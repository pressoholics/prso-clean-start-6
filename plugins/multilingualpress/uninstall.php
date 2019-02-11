<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

/**
 * Uninstall routines.
 *
 * This file is called automatically when the plugin is deleted per user interface.
 *
 * @see https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
 */

namespace Inpsyde\MultilingualPress;

use Inpsyde\MultilingualPress\Database\Table;
use Inpsyde\MultilingualPress\Installation\Uninstaller;

defined('ABSPATH') || die();

if (!defined('WP_UNINSTALL_PLUGIN')) {
    return;
}

if (!current_user_can('activate_plugins')) {
    return;
}

if (!is_multisite()) {
    return;
}

$mainPluginFile = __DIR__ . '/multilingualpress.php';

if (plugin_basename($mainPluginFile) !== WP_UNINSTALL_PLUGIN
    || !is_readable($mainPluginFile)
) {
    unset($mainPluginFile);

    return;
}

/** @noinspection PhpIncludeInspection */
require_once $mainPluginFile;

unset($mainPluginFile);

if (!bootstrap()) {
    return;
}

$uninstaller = resolve(Uninstaller::class);

$uninstaller->uninstallTables(
    [
        resolve(Table\ContentRelationsTable::class),
        resolve(Table\LanguagesTable::class),
        resolve(Table\RelationshipsTable::class),
        resolve(Table\SiteRelationsTable::class),
    ]
);

$uninstaller->deleteNetworkOptions(
    [
        Activation\Activator::OPTION,
        Core\Admin\SiteSettingsRepository::OPTION,
        Core\PostTypeRepository::OPTION,
        Core\TaxonomyRepository::OPTION,
        Framework\Module\ModuleManager::OPTION,
        MultilingualPress::OPTION_VERSION,
    ]
);

$uninstaller->deleteSiteOptions(
    [
        Module\Redirect\SettingsRepository::OPTION_SITE,
    ]
);

$uninstaller->deletePostMeta(
    [
        Module\Trasher\TrasherSettingRepository::META_KEY,
        NavMenu\ItemRepository::META_KEY_SITE_ID,
    ]
);

$uninstaller->deleteUserMeta(
    [
        Module\Redirect\SettingsRepository::META_KEY_USER,
    ]
);

$uninstaller->deleteOnboardingData(
    [
        'onboarding_dismissed',
        'onboarding_state',
    ],
    ['dismissed_mlp_pointers']
);


unset($uninstaller);
