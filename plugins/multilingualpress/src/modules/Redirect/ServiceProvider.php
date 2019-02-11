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

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Framework\Api\Translations;
use Inpsyde\MultilingualPress\Asset\AssetFactory;
use Inpsyde\MultilingualPress\Framework\Admin\SitesListTableColumn;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSetting;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingUpdater;
use Inpsyde\MultilingualPress\Framework\Setting\User\UserSetting;
use Inpsyde\MultilingualPress\Framework\Setting\User\UserSettingUpdater;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Framework\Module\Module;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdater;
use Inpsyde\MultilingualPress\Core\Frontend\AlternateLanguages;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Translator\FrontPageTranslator;
use Inpsyde\MultilingualPress\Translator\PostTranslator;
use Inpsyde\MultilingualPress\Translator\TermTranslator;

final class ServiceProvider implements ModuleServiceProvider
{
    const MODULE_ID = 'redirect';

    const SETTING_NONCE_ACTION = 'multilingualpress_save_redirect_setting_nonce_';

    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->addService(
            AcceptLanguageParser::class,
            function (): AcceptLanguageParser {
                return new AcceptLanguageParser();
            }
        );

        $container->addService(
            LanguageNegotiator::class,
            function (Container $container): LanguageNegotiator {
                return new LanguageNegotiator(
                    $container[Translations::class],
                    $container[ServerRequest::class],
                    $container[AcceptLanguageParser::class]
                );
            }
        );

        $container->addService(
            NoredirectPermalinkFilter::class,
            function (): NoredirectPermalinkFilter {
                return new NoredirectPermalinkFilter();
            }
        );

        $container->addService(
            NoRedirectStorage::class,
            function (): NoRedirectStorage {
                return is_user_logged_in() && wp_using_ext_object_cache()
                    ? new NoRedirectObjectCacheStorage()
                    : new NoRedirectSessionStorage();
            }
        );

        $container->addService(
            RedirectRequestChecker::class,
            function (Container $container): RedirectRequestChecker {
                return new RedirectRequestChecker(
                    $container[SettingsRepository::class],
                    $container[NoRedirectStorage::class]
                );
            }
        );

        $this->registerSettings($container);

        $this->registerRedirector($container);
    }

    /**
     * @param Container $container
     */
    private function registerRedirector(Container $container)
    {
        $container->addService(
            Redirector::class,
            function (Container $container): Redirector {

                $negotiator = $container[LanguageNegotiator::class];

                /**
                 * Filters the redirector type.
                 *
                 * @param string $type
                 */
                $type = apply_filters(Redirector::FILTER_REDIRECTOR_TYPE, Redirector::TYPE_PHP);

                if ($type === Redirector::TYPE_JAVASCRIPT) {
                    return new JsRedirector($negotiator, $container[AssetManager::class]);
                }

                return new PhpRedirector(
                    $negotiator,
                    $container[NoRedirectStorage::class],
                    $container[ServerRequest::class]
                );
            }
        );
    }

    /**
     * @param Container $container
     */
    private function registerSettings(Container $container)
    {
        $container->share(
            SettingsRepository::class,
            function (): SettingsRepository {
                return new SettingsRepository();
            }
        );

        $container->addService(
            RedirectSiteSetting::class,
            function (Container $container): RedirectSiteSetting {
                return new RedirectSiteSetting(
                    SettingsRepository::OPTION_SITE,
                    $container[NonceFactory::class]->create([self::SETTING_NONCE_ACTION . 'site']),
                    $container[SettingsRepository::class]
                );
            }
        );

        $container->addService(
            RedirectUserSetting::class,
            function (Container $container): RedirectUserSetting {
                return new RedirectUserSetting(
                    SettingsRepository::META_KEY_USER,
                    $container[NonceFactory::class]->create([self::SETTING_NONCE_ACTION . 'user']),
                    $container[SettingsRepository::class]
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function registerModule(ModuleManager $moduleManager): bool
    {
        return $moduleManager->register(
            new Module(
                self::MODULE_ID,
                [
                    'description' => __(
                        'Enable the Redirect checkbox on each site: this allows you to enable/disable the automatic redirection feature according to the user browser language settings.',
                        'multilingualpress'
                    ),
                    'name' => __('Redirect', 'multilingualpress'),
                    'active' => true,
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function activateModule(Container $container)
    {
        $userSetting = new UserSetting(
            $container[RedirectUserSetting::class],
            new UserSettingUpdater(
                SettingsRepository::META_KEY_USER,
                $container[ServerRequest::class],
                $container[NonceFactory::class]->create([self::SETTING_NONCE_ACTION . 'user'])
            )
        );
        $userSetting->register();

        if (is_admin()) {
            $this->activateModuleForAdmin($container);

            return;
        }

        $this->activateModuleForFrontend($container);
    }

    /**
     * Performs various admin-specific tasks on module activation.
     *
     * @param Container $container
     */
    private function activateModuleForAdmin(Container $container)
    {
        $setting = new SiteSetting(
            $container[RedirectSiteSetting::class],
            new SiteSettingUpdater(
                SettingsRepository::OPTION_SITE,
                $container[ServerRequest::class],
                $container[NonceFactory::class]->create([self::SETTING_NONCE_ACTION . 'site'])
            )
        );

        $setting->register(
            SiteSettingsSectionView::ACTION_AFTER . '_' . SiteSettings::ID,
            SiteSettingsUpdater::ACTION_UPDATE_SETTINGS
        );

        if (is_network_admin()) {
            $this->activateModuleForNetworkAdmin($container, $setting);
        }
    }

    /**
     * Performs various admin-specific tasks on module activation.
     *
     * @param Container $container
     * @param SiteSetting $setting
     */
    private function activateModuleForNetworkAdmin(Container $container, SiteSetting $setting)
    {
        $setting->register(
            SiteSettingsSectionView::ACTION_AFTER . '_' . NewSiteSettings::SECTION_ID,
            SiteSettingsUpdater::ACTION_DEFINE_INITIAL_SETTINGS
        );

        if ('sites.php' !== ($GLOBALS['pagenow'] ?? '')) {
            return;
        }

        $settingsRepository = $container[SettingsRepository::class];
        $sitesListTableColumn = new SitesListTableColumn(
            'multilingualpress.redirect',
            __('Redirect', 'multilingualpress'),
            function (string $column, int $siteId) use ($settingsRepository): string {
                return $settingsRepository->shouldRedirectBySite($siteId)
                    ? '<span class="dashicons dashicons-yes"></span>'
                    : '';
            }
        );

        $sitesListTableColumn->register();
    }

    /**
     * Performs various admin-specific tasks on module activation.
     *
     * @param Container $container
     */
    private function activateModuleForFrontend(Container $container)
    {
        $container[AssetManager::class]
            ->registerScript(
                $container[AssetFactory::class]->createInternalScript(
                    JsRedirector::SCRIPT_HANDLE,
                    'redirect.js'
                )
            );

        $filter = $container[NoredirectPermalinkFilter::class];
        $filter->enable();

        add_filter(
            AlternateLanguages::FILTER_HREFLANG_URL,
            [$filter, 'removeNoRedirectQueryArgument']
        );

        if ($container[RedirectRequestChecker::class]->isRedirectRequest()) {
            add_action(
                'wp_loaded',
                [$container[Redirector::class], 'redirect'],
                0
            );
        }
    }
}
