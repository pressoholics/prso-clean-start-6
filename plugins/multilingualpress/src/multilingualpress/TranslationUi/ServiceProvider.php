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

namespace Inpsyde\MultilingualPress\TranslationUi;

use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Core\Entity\ActiveTaxonomies;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Entity;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Metaboxes;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Http\RequestGlobalsManipulator;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\TranslationUi\Post;
use Inpsyde\MultilingualPress\TranslationUi\Term\RelationshipContext;
use Inpsyde\MultilingualPress\TranslationUi\Term\TermRelationSaveHelper;

/**
 * Service provider for all translation objects.
 */
final class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->share(
            Metaboxes::class,
            function (Container $container): Metaboxes {
                return new Metaboxes(
                    $container[ServerRequest::class],
                    $container[RequestGlobalsManipulator::class],
                    $container[PersistentAdminNotices::class]
                );
            }
        );

        $this->registerForPost($container);
        $this->registerForTerm($container);
    }

    /**
     * @param Container $container
     * @throws \Inpsyde\MultilingualPress\Framework\Service\Exception\NameOverwriteNotAllowed
     * @throws \Inpsyde\MultilingualPress\Framework\Service\Exception\WriteAccessOnLockedContainer
     */
    private function registerForTerm(Container $container)
    {
        $container->share(
            Term\RelationshipPermission::class,
            function (Container $container): Term\RelationshipPermission {
                return new Term\RelationshipPermission($container[ContentRelations::class]);
            }
        );

        $container->addService(
            Term\Ajax\ContextBuilder::class,
            function (Container $container): Term\Ajax\ContextBuilder {
                return new Term\Ajax\ContextBuilder($container[ServerRequest::class]);
            }
        );

        $container->share(
            Term\Ajax\Search::class,
            function (Container $container): Term\Ajax\Search {
                return new Term\Ajax\Search(
                    $container[ServerRequest::class],
                    $container[Term\Ajax\ContextBuilder::class]
                );
            }
        );

        $container->share(
            Term\Ajax\RelationshipUpdater::class,
            function (Container $container): Term\Ajax\RelationshipUpdater {
                return new Term\Ajax\RelationshipUpdater(
                    $container[ServerRequest::class],
                    $container[Term\Ajax\ContextBuilder::class],
                    $container[ContentRelations::class],
                    $container[ActiveTaxonomies::class],
                    $container[Term\RelationshipPermission::class]
                );
            }
        );
    }

    /**
     * @param Container $container
     * @throws \Inpsyde\MultilingualPress\Framework\Service\Exception\NameOverwriteNotAllowed
     * @throws \Inpsyde\MultilingualPress\Framework\Service\Exception\WriteAccessOnLockedContainer
     */
    private function registerForPost(Container $container)
    {
        $container->share(
            Post\RelationshipPermission::class,
            function (Container $container): Post\RelationshipPermission {
                return new Post\RelationshipPermission($container[ContentRelations::class]);
            }
        );

        $container->addService(
            Post\Ajax\ContextBuilder::class,
            function (Container $container): Post\Ajax\ContextBuilder {
                return new Post\Ajax\ContextBuilder($container[ServerRequest::class]);
            }
        );

        $container->share(
            Post\Ajax\Search::class,
            function (Container $container): Post\Ajax\Search {
                return new Post\Ajax\Search(
                    $container[ServerRequest::class],
                    $container[Post\Ajax\ContextBuilder::class]
                );
            }
        );

        $container->share(
            Post\Ajax\RelationshipUpdater::class,
            function (Container $container): Post\Ajax\RelationshipUpdater {
                return new Post\Ajax\RelationshipUpdater(
                    $container[ServerRequest::class],
                    $container[Post\Ajax\ContextBuilder::class],
                    $container[ContentRelations::class],
                    $container[ActivePostTypes::class],
                    $container[Post\RelationshipPermission::class]
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    public function bootstrap(Container $container)
    {
        if (!is_admin()) {
            return;
        }

        add_action(
            'admin_menu',
            function () use ($container) {
                $container[Metaboxes::class]->init();
                $container[AssetManager::class]->enqueueStyle('multilingualpress-admin');
            }
        );

        add_action(
            Metaboxes::REGISTER_METABOXES,
            function (Metaboxes $metaboxes, Entity $entity) use ($container) {
                if (!$entity->is(\WP_Post::class) && !$entity->is(\WP_Term::class)) {
                    return;
                }

                $siteRelations = $container[SiteRelations::class];
                $currentSite = get_current_blog_id();
                $relatedSites = $siteRelations->relatedSiteIds($currentSite);
                if (!$relatedSites) {
                    return;
                }

                foreach ($relatedSites as $relatedSite) {
                    $metaboxes->addBox(
                        ...$this->createBoxes(
                            $currentSite,
                            $relatedSite,
                            $container
                        )
                    );
                }
            },
            10,
            2
        );

        add_action(
            TermRelationSaveHelper::ACTION_BEFORE_SAVE_RELATIONS,
            function (RelationshipContext $context) {
                global $wpdb;

                if (!function_exists('wc')) {
                    return;
                }

                $taxonomy = $context->sourceTerm()->taxonomy;
                if (substr($taxonomy, 0, 3) !== 'pa_' || !taxonomy_exists($taxonomy)) {
                    return;
                }

                $attributeName = substr($taxonomy, 3);
                $attributes = wc_get_attribute_taxonomies();
                $attributeExists = (bool)wp_list_filter(
                    $attributes,
                    ['attribute_name' => $attributeName]
                );

                $sourceDbPrefix = $wpdb->get_blog_prefix($context->sourceSiteId());
                $sourceAttribute = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$sourceDbPrefix}woocommerce_attribute_taxonomies WHERE attribute_name='%s'",
                        $attributeName
                    )
                );

                $results = false;
                ! $attributeExists and $results = $wpdb->insert(
                    $wpdb->prefix . 'woocommerce_attribute_taxonomies',
                    [
                        'attribute_label' => $sourceAttribute->attribute_label,
                        'attribute_name' => $sourceAttribute->attribute_name,
                        'attribute_type' => $sourceAttribute->attribute_type,
                        'attribute_orderby' => $sourceAttribute->attribute_orderby,
                        'attribute_public' => $sourceAttribute->attribute_public,
                    ],
                    ['%s', '%s', '%s', '%s', '%d']
                );

                if (!$results) {
                    return;
                }

                wp_schedule_single_event(time(), 'woocommerce_flush_rewrite_rules');
                delete_transient('wc_attribute_taxonomies');
            },
            10
        );

        $this->bootstrapAjax($container);
    }

    /**
     * @param int $currentSite
     * @param int $relatedSite
     * @param Container $container
     * @return array
     */
    private function createBoxes(
        int $currentSite,
        int $relatedSite,
        Container $container
    ): array {

        return [
            new Post\Metabox(
                $currentSite,
                $relatedSite,
                $container[ActivePostTypes::class],
                $container[ContentRelations::class],
                $container[Post\RelationshipPermission::class]
            ),
            new Term\Metabox(
                $currentSite,
                $relatedSite,
                $container[ActiveTaxonomies::class],
                $container[ContentRelations::class],
                $container[Term\RelationshipPermission::class]
            ),
        ];
    }

    /**
     * @param Container $container
     */
    private function bootstrapAjax(Container $container)
    {
        add_action(
            'wp_ajax_' . Post\Ajax\Search::ACTION,
            [$container[Post\Ajax\Search::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Post\Ajax\RelationshipUpdater::ACTION,
            [$container[Post\Ajax\RelationshipUpdater::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Term\Ajax\Search::ACTION,
            [$container[Term\Ajax\Search::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Term\Ajax\RelationshipUpdater::ACTION,
            [$container[Term\Ajax\RelationshipUpdater::class], 'handle']
        );
    }
}
