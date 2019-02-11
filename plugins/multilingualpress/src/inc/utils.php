<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Utilities around WordPress functions and classes.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Framework\Database\TableList;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Framework\Service\Container;

/**
 * Resolves the value with the given name from the container.
 *
 * @param string|null $name
 * @return mixed
 *
 * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
 */
function resolve(string $name = null)
{
    // phpcs:enable

    static $container;
    $container or $container = new Container();

    return $name === null ? $container : $container[$name];
}

/**
 * Checks if MultilingualPress debug mode is on.
 *
 * @return bool
 */
function isDebugMode(): bool
{
    $env = getenv('MULTILINGUALPRESS_DEBUG');
    if (is_string($env)) {
        return (bool)filter_var($env, FILTER_VALIDATE_BOOLEAN);
    }

    return defined('MULTILINGUALPRESS_DEBUG') && MULTILINGUALPRESS_DEBUG;
}

/**
 * Checks if either MultilingualPress or WordPress script debug mode is on.
 *
 * @return bool
 */
function isScriptDebugMode(): bool
{
    return isDebugMode() || (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);
}

/**
 * Checks if either MultilingualPress or WordPress debug mode is on.
 *
 * @return bool
 */
function isWpDebugMode(): bool
{
    return isDebugMode() || (defined('WP_DEBUG') && WP_DEBUG);
}

/**
 * Returns the given content ID, if valid, and the ID of the queried object otherwise.
 *
 * @param int $contentId
 * @return int
 */
function defaultContentId(int $contentId): int
{
    return (int)($contentId ?: get_queried_object_id());
}

/**
 * Print the setting page header
 *
 * @param \WP_Site $site
 * @param string $id
 */
function settingsPageHead(\WP_Site $site, string $id)
{
    $siteId = $site->id;

    /* translators: %s: site name */
    $title = sprintf(__('Edit Site: %s', 'multilingualpress'), $site->blogname);
    ?>
    <h1 id="edit-site"><?= esc_html($title) ?></h1>
    <?php settings_errors() ?>
    <p class="edit-site-actions">
        <a href="<?= esc_url(get_home_url($siteId, '/')) ?>">
            <?php esc_html_e('Visit', 'multilingualpress') ?>
        </a>
        |
        <a href="<?= esc_url(get_admin_url($siteId)) ?>">
            <?php esc_html_e('Dashboard', 'multilingualpress') ?>
        </a>
    </p>
    <?php
    network_edit_site_nav(['blog_id' => $siteId, 'selected' => $id]);
}

/**
 * Add error messages to the settings_errors transient.
 *
 * @param array $errors
 * @param string $setting
 * @param string $type
 */
function settingsErrors(array $errors, string $setting, string $type)
{
    $messages = get_transient('settings_errors');
    if (!is_array($messages)) {
        $messages = [];
    }

    foreach ($errors as $code => $message) {
        $messages[$code] = [
            'setting' => $setting,
            'code' => $code,
            'message' => $message,
            'type' => $type,
        ];
    }

    set_transient('settings_errors', $messages);
}

/**
 * Redirects to the given URL (or the referer) after a settings update request.
 *
 * @param string $url
 * @param string $setting
 * @param string $code
 */
function redirectAfterSettingsUpdate(
    string $url = '',
    string $setting = 'mlp-setting',
    string $code = 'mlp-setting'
) {

    if ($setting) {
        settingsErrors([$code => __('Settings saved.', 'multilingualpress')], $setting, 'updated');
    }

    if (!$url) {
        /** @var ServerRequest $serverRequest */
        $serverRequest = resolve(ServerRequest::class);
        $url = (string)($serverRequest->bodyValue('_wp_http_referer') ?: admin_url());
    }

    wp_safe_redirect(add_query_arg('settings-updated', true, $url));

    callExit();
}

/**
 * Checks if the site with the given ID exists (within the current or given network)
 * and is not marked as deleted.
 *
 * @param int $siteId
 * @param int $networkId
 * @return bool
 */
function siteExists(int $siteId, int $networkId = 0): bool
{
    static $cache = [];

    // We don't test large sites.
    if (wp_is_large_network()) {
        return true;
    }

    $networkId = (int)($networkId ?: get_current_network_id());

    if (!isset($cache[$networkId])) {
        /** @var \wpdb $wpdb */
        $wpdb = resolve(\wpdb::class);
        $query = $wpdb->prepare(
            "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = %d AND deleted = 0",
            $networkId
        );

        $cache[$networkId] = array_map('intval', $wpdb->get_col($query));
    }

    return in_array($siteId, $cache[$networkId], true);
}

/**
 * Checks if a given table exists within the database.
 *
 * @param string $tableName
 * @return bool
 */
function tableExists(string $tableName): bool
{
    static $cache = [];

    // We don't test large sites.
    if (function_exists('wp_is_large_network') and wp_is_large_network()) {
        return true;
    }

    if (empty($cache)) {
        $cache = resolve(TableList::class)->allTables();
    }

    return in_array($tableName, $cache, true);
}

/**
 * Wrapper for the exit language construct.
 *
 * Introduced to allow for easy unit testing.
 *
 * @param int|string $message
 *
 * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
 */
function callExit($message = '')
{
    $exitCallback = function () use ($message) {
        is_string($message) and $message = esc_html($message);
        exit($message); // phpcs:ignore WordPress
    };
    // phpcs:enable

    add_action('multilingualpress.exit', $exitCallback, 999, 0);

    // The closure object hash of is passed to allow 3rd party prevent exit by removing the action.
    do_action('multilingualpress.exit', spl_object_hash($exitCallback));
}

/**
 * Renders the HTML string for the hidden nonce field according to the given nonce object.
 *
 * @param Nonce $nonce
 * @param bool $withReferer
 */
function printNonceField(Nonce $nonce, bool $withReferer = true)
{
    ?>
    <input
        type="hidden"
        name="<?= esc_attr($nonce->action()) ?>"
        value="<?= esc_attr((string)$nonce) ?>">
    <?php
    $withReferer and wp_referer_field();
}

/**
 * Combine Attributes
 *
 * @param array $pairs
 * @param array $atts
 * @return array
 */
function combineAtts(array $pairs, array $atts): array
{
    $atts = (array)$atts;
    $out = [];

    foreach ($pairs as $name => $default) {
        if (array_key_exists($name, $atts)) {
            $out[$name] = $atts[$name];
            continue;
        }

        $out[$name] = $default;
    }

    return $out;
}

/**
 * Array to attributes
 *
 * @param array $attributes
 * @param bool $xml
 * @return string
 */
// phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
function arrayToAttrs(array $attributes, $xml = false): string
{
    // phpcs:enable
    $str = '';

    foreach ($attributes as $key => $value) {
        if (true === $value) {
            $value = $xml ? "='{$key}'" : '';
        }

        $str .= " {$key}=" . esc_attr($value);
    }

    return $str;
}
