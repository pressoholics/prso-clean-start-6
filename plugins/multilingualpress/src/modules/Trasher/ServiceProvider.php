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

namespace Inpsyde\MultilingualPress\Module\Trasher;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Framework\Module\Module;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Service\Container;

final class ServiceProvider implements ModuleServiceProvider
{
    const NONCE_ACTION = 'save_trasher_setting';

    const MODULE_ID = 'trasher';

    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->share(
            TrasherSettingRepository::class,
            function (): TrasherSettingRepository {
                return new TrasherSettingRepository();
            }
        );

        $container->addService(
            Trasher::class,
            function (Container $container): Trasher {
                return new Trasher(
                    $container[TrasherSettingRepository::class],
                    $container[ContentRelations::class],
                    $container[ActivePostTypes::class]
                );
            }
        );

        $container->addService(
            TrasherSettingUpdater::class,
            function (Container $container): TrasherSettingUpdater {
                return new TrasherSettingUpdater(
                    $container[TrasherSettingRepository::class],
                    $container[ContentRelations::class],
                    $container[ServerRequest::class],
                    $container[NonceFactory::class]->create([self::NONCE_ACTION]),
                    $container[ActivePostTypes::class]
                );
            }
        );

        $container->addService(
            TrasherSettingView::class,
            function (Container $container): TrasherSettingView {
                return new TrasherSettingView(
                    $container[TrasherSettingRepository::class],
                    $container[NonceFactory::class]->create([self::NONCE_ACTION]),
                    $container[ActivePostTypes::class]
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
                        'Enable the Thrash checkbox on post/page edit page: this allows you to send all the translations to trash when the source post/page is trashed.',
                        'multilingualpress'
                    ),
                    'name' => __('Trasher', 'multilingualpress'),
                    'active' => false,
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function activateModule(Container $container)
    {
        add_action(
            'post_submitbox_misc_actions',
            [$container[TrasherSettingView::class], 'render']
        );

        add_action(
            'save_post',
            [$container[TrasherSettingUpdater::class], 'update'],
            10,
            2
        );

        add_action('wp_trash_post', [$container[Trasher::class], 'trashRelatedPosts']);
    }
}
