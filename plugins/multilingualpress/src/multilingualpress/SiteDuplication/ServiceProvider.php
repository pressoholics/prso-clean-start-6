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
use Inpsyde\MultilingualPress\Framework\BasePathAdapter;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Database\TableDuplicator;
use Inpsyde\MultilingualPress\Framework\Database\TableList;
use Inpsyde\MultilingualPress\Framework\Database\TableReplacer;
use Inpsyde\MultilingualPress\Framework\Database\TableStringReplacer;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Framework\Nonce\SiteAwareNonce;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingMultiView;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingsSectionView;
use Inpsyde\MultilingualPress\Core\Admin\NewSiteSettings;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;

/**
 * Service provider for all site duplication objects.
 */
final class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function register(Container $container)
    {
        // phpcs:enable

        $container->addService(
            ActivePlugins::class,
            function (): ActivePlugins {
                return new ActivePlugins();
            }
        );

        $container->addService(
            AttachmentCopier::class,
            function (Container $container): AttachmentCopier {
                return new AttachmentCopier(
                    $container[\wpdb::class],
                    $container[BasePathAdapter::class],
                    $container[TableStringReplacer::class]
                );
            }
        );

        $container->addService(
            ActivatePluginsSetting::class,
            function (): ActivatePluginsSetting {
                return new ActivatePluginsSetting();
            }
        );

        $container->addService(
            BasedOnSiteSetting::class,
            function (Container $container): BasedOnSiteSetting {
                return new BasedOnSiteSetting(
                    $container[\wpdb::class],
                    $this->duplicateNonce($container)
                );
            }
        );

        $container->addService(
            SearchEngineVisibilitySetting::class,
            function (): SearchEngineVisibilitySetting {
                return new SearchEngineVisibilitySetting();
            }
        );

        $container->addService(
            SiteDuplicator::class,
            function (Container $container): SiteDuplicator {
                return new SiteDuplicator(
                    $container[\wpdb::class],
                    $container[TableList::class],
                    $container[TableDuplicator::class],
                    $container[TableReplacer::class],
                    $container[ActivePlugins::class],
                    $container[ContentRelations::class],
                    $container[AttachmentCopier::class],
                    $container[ServerRequest::class],
                    $this->duplicateNonce($container)
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        add_action(
            'wpmu_new_blog',
            [$container[SiteDuplicator::class], 'duplicateSite'],
            0
        );

        $settingView = SiteSettingMultiView::fromViewModels(
            [
                $container[BasedOnSiteSetting::class],
                $container[ActivatePluginsSetting::class],
                $container[SearchEngineVisibilitySetting::class],
            ]
        );

        add_action(
            SiteSettingsSectionView::ACTION_AFTER . '_' . NewSiteSettings::SECTION_ID,
            [$settingView, 'render']
        );
    }

    /**
     * @param Container $container
     * @return Nonce
     */
    private function duplicateNonce(Container $container): Nonce
    {
        $nonce = $container[NonceFactory::class]->create(['duplicate_site']);
        // When creating a new site, its ID is not yet known, so create a nonce for a fixed site ID 0.
        if ($nonce instanceof SiteAwareNonce) {
            $nonce = $nonce->withSite(0);
        }

        return $nonce;
    }
}
