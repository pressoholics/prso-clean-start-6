<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Database\TableDuplicator;
use Inpsyde\MultilingualPress\Framework\Database\TableList;
use Inpsyde\MultilingualPress\Framework\Database\TableReplacer;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\NetworkState;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;

/**
 * Handles duplication of a site.
 */
class SiteDuplicator
{
    const NAME_ACTIVATE_PLUGINS = 'mlp_activate_plugins';
    const NAME_BASED_ON_SITE = 'mlp_based_on_site';
    const NAME_SEARCH_ENGINE_VISIBILITY = 'mlp_search_engine_visibility';
    const NAME_OPTION_SITE_LANGUAGE = 'WPLANG';
    const DUPLICATE_ACTION_KEY = 'multilingualpress.duplicated_site';
    const FILTER_SITE_TABLES = 'multilingualpress.duplicate_site_tables';

    /**
     * @var ActivePlugins
     */
    private $activePlugins;

    /**
     * @var AttachmentCopier
     */
    private $attachmentCopier;

    /**
     * @var ContentRelations
     */
    private $contentRelations;

    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var TableDuplicator
     */
    private $tableDuplicator;

    /**
     * @var TableList
     */
    private $tableList;

    /**
     * @var TableReplacer
     */
    private $tableReplacer;

    /**
     * @param \wpdb $wpdb
     * @param TableList $tableList
     * @param TableDuplicator $tableDuplicator
     * @param TableReplacer $tableReplacer
     * @param ActivePlugins $activePlugins
     * @param ContentRelations $contentRelations
     * @param AttachmentCopier $attachmentCopier
     * @param Request $request
     * @param Nonce $nonce
     */
    public function __construct(
        \wpdb $wpdb,
        TableList $tableList,
        TableDuplicator $tableDuplicator,
        TableReplacer $tableReplacer,
        ActivePlugins $activePlugins,
        ContentRelations $contentRelations,
        AttachmentCopier $attachmentCopier,
        Request $request,
        Nonce $nonce
    ) {

        $this->wpdb = $wpdb;
        $this->tableList = $tableList;
        $this->tableDuplicator = $tableDuplicator;
        $this->tableReplacer = $tableReplacer;
        $this->activePlugins = $activePlugins;
        $this->contentRelations = $contentRelations;
        $this->attachmentCopier = $attachmentCopier;
        $this->request = $request;
        $this->nonce = $nonce;
    }

    /**
     * Duplicates a complete site to the new site just created.
     *
     * @param int $newSiteId
     * @return bool
     *
     * @wp-hook wpmu_new_blog
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function duplicateSite($newSiteId): bool
    {
        // phpcs:enable

        if (!$this->nonce->isValid()) {
            return false;
        }

        $sourceSiteId = (int)$this->request->bodyValue(
            static::NAME_BASED_ON_SITE,
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        if ($sourceSiteId < 1) {
            return false;
        }

        $newSiteId = (int)$newSiteId;

        $networkState = NetworkState::create();

        switch_to_blog($sourceSiteId);
        $tablePrefix = $this->wpdb->prefix;
        $mappedDomain = $this->mappedDomain();

        switch_to_blog($newSiteId);
        $adminEmail = (string)get_option('admin_email', '');
        $siteurl = (string)get_option('siteurl', '');

        // Important: FIRST, duplicate the tables, and THEN overwrite things.
        $this->duplicateTables($sourceSiteId, $tablePrefix);
        $this->updateUrls($siteurl, $mappedDomain);
        $this->updateAdminEmail($adminEmail);

        $languageData = (string)$this->request->bodyValue(
            static::NAME_OPTION_SITE_LANGUAGE,
            INPUT_POST,
            FILTER_SANITIZE_STRING
        );
        $this->updateSiteLanguage($languageData);

        $blogData = (array)$this->request->bodyValue(
            'blog',
            INPUT_POST,
            FILTER_DEFAULT,
            FILTER_FORCE_ARRAY
        );

        update_option('blogname', stripslashes($blogData['title'] ?? ''));

        $this->renameUserRolesOption($tablePrefix);
        $this->handleSearchEngineVisibility();
        $this->handlePlugins();
        $this->handleTheme();

        $relations = (array)$this->request->bodyValue(
            'mlp_site_relations',
            INPUT_POST
        );
        $relations = array_map('intval', array_filter($relations));

        if ($relations && in_array($sourceSiteId, $relations, true)) {
            $this->handleContentRelations($sourceSiteId, $newSiteId);
        }

        $this->attachmentCopier->copyAttachments($sourceSiteId);

        $networkState->restore();

        /**
         * Fires after successful site duplication.
         *
         * @param int $sourceSiteId
         * @param int $newSiteId
         */
        do_action(self::DUPLICATE_ACTION_KEY, $sourceSiteId, $newSiteId);

        return true;
    }

    /**
     * Returns the primary domain if domain mapping is active.
     *
     * @return string
     */
    private function mappedDomain(): string
    {
        if (empty($this->wpdb->dmtable)) {
            return '';
        }

        $query = $this->wpdb->prepare(
            "SELECT domain FROM {$this->wpdb->dmtable} WHERE active = 1 AND blog_id = %s LIMIT 1",
            get_current_blog_id()
        );

        $domain = $this->wpdb->get_var($query);
        if (!$domain) {
            return '';
        }

        return set_url_scheme("http://{$domain}");
    }

    /**
     * Duplicates the tables of the given source site to the current site.
     *
     * @param int $sourceSiteId
     * @param string $tablePrefix
     */
    private function duplicateTables(int $sourceSiteId, string $tablePrefix)
    {
        /**
         * Filters the tables to duplicate from the source site for the current site.
         *
         * @param string[] $tables
         * @param int $sourceSiteId
         */
        $tables = (array)apply_filters(
            self::FILTER_SITE_TABLES,
            $this->tableList->siteTables($sourceSiteId),
            $sourceSiteId
        );

        foreach ($tables as $table) {
            if (is_string($table) && $table) {
                $this->duplicateTable($table, $tablePrefix);
            }
        }
    }

    /**
     * @param string $table
     * @param string $tablePrefix
     */
    private function duplicateTable(string $table, string $tablePrefix)
    {
        $newTableName = is_string($table)
            ? preg_replace("~^{$tablePrefix}~", $this->wpdb->prefix, $table)
            : '';

        if ($newTableName && $this->tableDuplicator->duplicate($table, $newTableName)) {
            $this->tableReplacer->replace($newTableName, $table);
        }
    }

    /**
     * Sets the admin email address option to the given value.
     *
     * @param string $url
     * @param string $domain
     */
    private function updateUrls(string $url, string $domain)
    {
        update_option('home', $url);

        /**
         * Updating the siteurl option will start the URL renaming plugin.
         * So, yes, the following code is correct, even though siteurl will get updated two times!
         */
        if ($domain) {
            update_option('siteurl', $domain);
        }

        update_option('siteurl', $url);
    }

    /**
     * Sets the admin email address option to the given value.
     *
     * Using update_option() would trigger a confirmation email to the new address, so we directly
     * manipulate the db.
     *
     * @param string $newAdminEmail
     */
    private function updateAdminEmail(string $newAdminEmail)
    {
        $this->wpdb->update(
            $this->wpdb->options,
            ['option_value' => $newAdminEmail],
            ['option_name' => 'admin_email']
        );
    }

    /**
     * @param string $language The language we want to store into the db.
     *
     * @return void
     */
    private function updateSiteLanguage(string $language)
    {
        $language = $language ?: 'en-US';

        $this->wpdb->update(
            $this->wpdb->options,
            ['option_value' => $language],
            ['option_name' => 'WPLANG']
        );
    }

    /**
     * Renames the user roles option according to the given table prefix.
     *
     * @param string $newTablePrefix
     */
    private function renameUserRolesOption(string $newTablePrefix)
    {
        $this->wpdb->update(
            $this->wpdb->options,
            ['option_name' => "{$this->wpdb->prefix}user_roles"],
            ['option_name' => "{$newTablePrefix}user_roles"]
        );
    }

    /**
     * Adapts the search engine visibility according to the setting included in the request.
     */
    private function handleSearchEngineVisibility()
    {
        $isSiteVisible = $this->request->bodyValue(
            static::NAME_SEARCH_ENGINE_VISIBILITY,
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        update_option('blog_public', (bool)$isSiteVisible);
    }

    /**
     * Adapts all active plugins according to the setting included in the request.
     */
    private function handlePlugins()
    {
        $activatePlugins = $this->request->bodyValue(
            static::NAME_ACTIVATE_PLUGINS,
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        $activatePlugins ? $this->activePlugins->activate() : $this->activePlugins->deactivate();
    }

    /**
     * Triggers potential setup routines of the used theme.
     */
    private function handleTheme()
    {
        $theme = wp_get_theme();
        /** This action is documented in wp-includes/theme.php. */
        do_action('switch_theme', $theme->get('Name'), $theme, $theme);
    }

    /**
     * Sets up content relations between the source site and the new site.
     *
     * @param int $sourceSiteId
     * @param int $destinationSiteId
     */
    private function handleContentRelations(int $sourceSiteId, int $destinationSiteId)
    {
        if ($this->contentRelations->hasSiteRelations($sourceSiteId)) {
            $this->contentRelations->duplicateRelations($sourceSiteId, $destinationSiteId);

            return;
        }

        $this->contentRelations->relateAllPosts($sourceSiteId, $destinationSiteId);
        $this->contentRelations->relateAllTerms($sourceSiteId, $destinationSiteId);
    }
}
