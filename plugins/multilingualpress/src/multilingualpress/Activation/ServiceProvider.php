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

namespace Inpsyde\MultilingualPress\Activation;

use Inpsyde\MultilingualPress\Activation\Onboarding\Messages;
use Inpsyde\MultilingualPress\Activation\Onboarding\Onboarding;
use Inpsyde\MultilingualPress\Activation\Onboarding\State;
use Inpsyde\MultilingualPress\Asset\AssetFactory;
use Inpsyde\MultilingualPress\Framework\Admin\Pointers;
use Inpsyde\MultilingualPress\Framework\Admin\PointersRepository;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Framework\Service\IntegrationServiceProvider;

use const Inpsyde\MultilingualPress\ACTION_ACTIVATION;

/**
 * Service provider for all activation objects.
 */
final class ServiceProvider implements IntegrationServiceProvider, BootstrappableServiceProvider
{

    /**
     * @inheritdoc
     */
    // phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
    public function register(Container $container)
    {
        // phpcs:enable
        $container->addService(
            Activator::class,
            function (): Activator {
                return new Activator();
            }
        );

        $container->addService(
            AssetManager::class,
            function (): AssetManager {
                return new AssetManager();
            }
        );

        $container->addService(
            State::class,
            function (): State {
                return new State();
            }
        );

        $container->addService(
            Messages::class,
            function (Container $container): Messages {
                return new Messages($container[State::class]);
            }
        );

        $container->addService(
            Onboarding::class,
            function (Container $container): Onboarding {
                return new Onboarding(
                    $container[AssetManager::class],
                    $container[SiteRelations::class],
                    $container[ServerRequest::class],
                    $container[State::class],
                    $container[Messages::class]
                );
            }
        );

        $container->addService(
            PointersRepository::class,
            function (): PointersRepository {
                return new PointersRepository();
            }
        );

        $container->addService(
            Pointers::class,
            function (Container $container): Pointers {
                return new Pointers(
                    $container[ServerRequest::class],
                    $container[PointersRepository::class],
                    $container[AssetManager::class]
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function integrate(Container $container)
    {
        $this->setupActivator($container);

        $onboarding = $container[Onboarding::class];
        $onboarding->init();

        $this->registerPointersForScreen($container);
        $this->registerPointersActionForScreen($container);
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $this->registerAssets($container);

        add_action('admin_enqueue_scripts', [$container[Pointers::class], 'createPointers']);
        add_action('wp_ajax_edit_site_dismiss', [$container[Pointers::class], 'dismiss']);
        add_action('wp_ajax_new_site_dismiss', [$container[Pointers::class], 'dismiss']);
        add_action('wp_ajax_settings_dynamic_permalinks', [$container[Pointers::class], 'dismiss']);

        $this->dismissPointersOnPageVisit();
        $this->dismissPointersForNewUsers();

        $this->handleDismissOnboardingMessage($container);
    }

    /**
     * @param Container $container
     * @return void
     */
    private function setupActivator(Container $container)
    {
        $activator = $container[Activator::class];

        if (did_action(ACTION_ACTIVATION)) {
            $activator->handleActivation();
        }

        $activator->registerCallback(
            function () use ($container) {

                $contentRelations = $container[ContentRelations::class];

                try {
                    $contentRelations->deleteAllRelationsForInvalidSites();
                    $contentRelations->deleteAllRelationsForInvalidContent(ContentRelations::CONTENT_TYPE_POST);
                    $contentRelations->deleteAllRelationsForInvalidContent(ContentRelations::CONTENT_TYPE_TERM);
                } catch (NonexistentTable $exc) {
                    return;
                }
            }
        );

        $activator->handlePendingActivation();
    }

    /**
     * @param Container $container
     * @return void
     */
    // phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
    private function registerPointersForScreen(Container $container)
    {
        // phpcs:enable
        $pointersRepository = $container[PointersRepository::class];

        $pointersRepository->registerForScreen(
            'sites_page_multilingualpress-site-settings-network',
            'multilingualpress_edit_site_language',
            '#mlp-site-language',
            'multilingualpress_edit_relationships_languages',
            [],
            [
                'content' => '<h3>' . esc_html__('Language', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__('Select the language of the site.', 'multilingualpress') . '</p>',
                'position' => [
                    'edge' => 'top',
                    'align' => 'left',
                ],
            ]
        )->registerForScreen(
            'sites_page_multilingualpress-site-settings-network',
            'multilingualpress_edit_relationships_languages',
            '.mlp-relationships-languages',
            'multilingualpress_edit_site',
            [],
            [
                'content' => '<h3>' . esc_html__('Site Relationships', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'Set up site relationships to existing sites in the network.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'bottom',
                    'align' => 'left',
                ],
            ]
        )->registerForScreen(
            'sites_page_multilingualpress-site-settings-network',
            'multilingualpress_edit_site',
            '#submit',
            '',
            [],
            [
                'content' => '<h3>' . esc_html__('Save Changes', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'Finally, save to apply the changes.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'bottom',
                    'align' => 'left',
                ],
            ]
        );

        $pointersRepository->registerForScreen(
            'site-new-network',
            'multilingualpress_new_site_language',
            '#mlp-site-language',
            'multilingualpress_new_relationships_languages',
            [],
            [
                'content' => '<h3>' . esc_html__('Language', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'After filling the above WordPress site fields, select the language of the site.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'top',
                    'align' => 'left',
                ],
            ]
        )->registerForScreen(
            'site-new-network',
            'multilingualpress_new_relationships_languages',
            '.mlp-relationships-languages',
            'multilingualpress_based_on_site',
            [],
            [
                'content' => '<h3>' . esc_html__('Site Relationships', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'Set up site relationships to existing sites in the network.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'bottom',
                    'align' => 'left',
                ],
            ]
        )->registerForScreen(
            'site-new-network',
            'multilingualpress_based_on_site',
            '#mlp-base-site-id',
            'multilingualpress_add_site',
            [],
            [
                'content' => '<h3>' . esc_html__('Based on site', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'Select a site to copy all its contents to the new site.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'bottom',
                    'align' => 'left',
                ],
            ]
        )->registerForScreen(
            'site-new-network',
            'multilingualpress_add_site',
            '#add-site',
            '',
            [],
            [
                'content' => '<h3>' . esc_html__('Save Changes', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'Finally, save to apply the changes.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'bottom',
                    'align' => 'left',
                ],
            ]
        );

        $pointersRepository->registerForScreen(
            'toplevel_page_multilingualpress-network',
            'multilingualpress_settings_dynamic_permalinks',
            '#mlp-post-type-page-permalinks',
            '',
            [],
            [
                'content' => '<h3>' . esc_html__('Use Dynamic Permalinks', 'multilingualpress') . '</h3>' .
                    '<p>' . esc_html__(
                        'If the post type can not translate the URL, you can activate dynamic permalinks (which is a plain URL) as a workaround to solve the problem.',
                        'multilingualpress'
                    ) . '</p>',
                'position' => [
                    'edge' => 'top',
                    'align' => 'left',
                ],
            ]
        );
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerPointersActionForScreen(Container $container)
    {
        $pointersRepository = $container[PointersRepository::class];

        $pointersRepository->registerActionForScreen(
            'sites_page_multilingualpress-site-settings-network',
            'edit_site_dismiss'
        );

        $pointersRepository->registerActionForScreen('site-new-network', 'new_site_dismiss');

        $pointersRepository->registerActionForScreen(
            'toplevel_page_multilingualpress-network',
            'settings_dynamic_permalinks'
        );
    }

    /**
     * @param Container $container
     * @return void
     */
    private function registerAssets(Container $container)
    {
        $assetFactory = $container[AssetFactory::class];
        $container[AssetManager::class]
            ->registerScript(
                $assetFactory->createInternalScript(
                    'onboarding',
                    'onboarding.js'
                )
            )->registerStyle(
                $assetFactory->createInternalStyle(
                    'onboarding',
                    'onboarding.css'
                )
            )->registerScript(
                $assetFactory->createInternalScript(
                    'pointers',
                    'pointers.js'
                )
            );
    }

    /**
     *
     * @param Container $container
     * @return void
     */
    private function handleDismissOnboardingMessage(Container $container)
    {
        add_action(
            'wp_ajax_onboarding_plugin',
            [Onboarding::class, 'handleAjaxDismissOnboardingMessage']
        );

        add_action('admin_init', [$container[Onboarding::class], 'handleDismissOnboardingMessage']);
    }

    /**
     *
     * @return void
     */
    private function dismissPointersForNewUsers()
    {
        add_action('user_register', function ($userId) {

            $dismissedPointers = explode(
                ',',
                (string)get_user_meta(get_current_user_id(), 'dismissed_mlp_pointers', true)
            );
            foreach ($dismissedPointers as $pointer) {
                add_user_meta($userId, 'dismissed_mlp_pointers', $pointer);
            }
        });
    }

    /**
     *
     * @return void
     */
    private function dismissPointersOnPageVisit()
    {
        add_action(Pointers::ACTION_AFTER_POINTERS_CREATED, function ($screen) {
            if ($screen->id === 'site-new-network') {
                update_user_meta(
                    get_current_user_id(),
                    'dismissed_mlp_pointers',
                    'multilingualpress_new_site_language'
                );
            }
            if ($screen->id === 'sites_page_multilingualpress-site-settings-network') {
                update_user_meta(
                    get_current_user_id(),
                    'dismissed_mlp_pointers',
                    'multilingualpress_edit_site_language'
                );
            }
        });
    }
}
