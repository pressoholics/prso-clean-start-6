<?php // phpcs:ignore
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @wordpress-plugin
 * Plugin Name: MultilingualPress
 * Plugin URI: https://multilingualpress.org/
 * Description: The multisite-based plugin for your multilingual WordPress websites.
 * Author: Inpsyde GmbH
 * Author URI: https://inpsyde.com
 * Version: 3.0.0-dev
 * Text Domain: multilingualpress
 * Domain Path: /languages/
 * License: MIT
 * Network: true
 * Requires at least: 4.8
 * Requires PHP: 7.0
 */

namespace Inpsyde\MultilingualPress;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable NeutronStandard.StrictTypes.RequireStrictTypes
// phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration

defined('ABSPATH') or die();

if (version_compare(PHP_VERSION, '7', '<')) {
    wp_die(
        esc_html__(
            'MultilingualPress 3 requires at least PHP version 7.',
            'multilingualpress'
        )
        . '<br>' .
        esc_html__(
            'Please ask your server administrator to update your environment to PHP version 7.',
            'multilingualpress'
        ),
        esc_html__('MultilingualPress Activation', 'multilingualpress'),
        [
            'back_link' => true,
        ]
    );
}

// phpcs:disable Inpsyde.CodeQuality.NoTopLevelDefine.Found
define('MULTILINGUALPRESS_NEEDS_LICENSE', true);
define('MULTILINGUALPRESS_LICENSE_API_URL', 'https://multilingualpress.org');
// phpcs:enable

const ACTION_ACTIVATION = 'multilingualpress.activation';
const ACTION_ADD_SERVICE_PROVIDERS = 'multilingualpress.add_service_providers';

// phpcs:ignore
function autoload()
{
    static $done;
    if (is_bool($done)) {
        return $done;
    }
    if (class_exists(MultilingualPress::class)) {
        $done = true;

        return true;
    }
    if (is_readable(__DIR__ . '/autoload.php')) {
        require_once __DIR__ . '/autoload.php';
        $done = true;

        return true;
    }
    if (is_readable(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $done = true;

        return true;
    }
    $done = false;

    return false;
}

if (!autoload()) {
    return;
}

/**
 * Bootstraps MultilingualPress.
 *
 * @return bool
 *
 * @wp-hook plugins_loaded
 */
function bootstrap()
{
    /** @var Framework\Service\Container $container */
    $container = resolve(null);
    $container = $container->shareValue(
        Framework\PluginProperties::class,
        new Framework\PluginProperties(__FILE__)
    );

    $providers = new Framework\Service\ServiceProvidersCollection();
    $providers
        ->add(new Activation\ServiceProvider())
        ->add(new Api\ServiceProvider())
        ->add(new Asset\ServiceProvider())
        ->add(new Cache\ServiceProvider())
        ->add(new Core\ServiceProvider())
        ->add(new Database\ServiceProvider())
        ->add(new Factory\ServiceProvider())
        ->add(new Installation\ServiceProvider())
        ->add(new Integration\ServiceProvider())
        ->add(new NavMenu\ServiceProvider())
        ->add(new SiteDuplication\ServiceProvider())
        ->add(new TranslationUi\ServiceProvider())
        ->add(new Translator\ServiceProvider())
        ->add(new Module\AltLanguageTitleInAdminBar\ServiceProvider())
        ->add(new Module\Redirect\ServiceProvider())
        ->add(new Module\Trasher\ServiceProvider());

    $multilingualpress = new MultilingualPress($container, $providers);

    /**
     * Fires right before MultilingualPress gets bootstrapped.
     *
     * Hook here to add custom service providers via
     * `ServiceProviderCollection::add_service_provider()`.
     *
     * @param Framework\Service\ServiceProvidersCollection $providers
     */
    do_action(ACTION_ADD_SERVICE_PROVIDERS, $providers);

    $bootstrapped = $multilingualpress->bootstrap();

    unset($providers);

    return $bootstrapped;
}

/**
 * Triggers a plugin-specific activation action third parties can listen to.
 *
 * @wp-hook activate_{$plugin}
 */
function activate()
{
    /**
     * Fires when MultilingualPress is about to be activated.
     */
    do_action(ACTION_ACTIVATION);

    add_action(
        'activated_plugin',
        function (string $plugin) {
            if (plugin_basename(__FILE__) === $plugin) {
                // Bootstrap MultilingualPress to take care of installation or upgrade routines.
                bootstrap();
            }
        },
        0
    );
}

add_action('plugins_loaded', __NAMESPACE__ . '\\bootstrap', 0);

register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate');
