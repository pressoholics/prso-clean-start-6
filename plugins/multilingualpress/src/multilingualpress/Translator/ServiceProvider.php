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

namespace Inpsyde\MultilingualPress\Translator;

use Inpsyde\MultilingualPress\Core\Frontend;
use Inpsyde\MultilingualPress\Core\Admin;
use Inpsyde\MultilingualPress\Core\PostTypeRepository;
use Inpsyde\MultilingualPress\Core\TaxonomyRepository;
use Inpsyde\MultilingualPress\Framework\Api\Translations;
use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Framework\Factory\UrlFactory;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;

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
        $this->registerContentRelatedTranslations($container);
        $this->registerNotContentRelatedTranslations($container);
    }

    /**
     * @param Container $container
     */
    private function registerContentRelatedTranslations(Container $container)
    {
        $container->addService(
            PostTranslator::class,
            function (Container $container): PostTranslator {
                return new PostTranslator(
                    $container[PostTypeRepository::class],
                    $container[Admin\PostTypeSlugsSettingsRepository::class],
                    $container[UrlFactory::class]
                );
            }
        );

        $container->addService(
            TermTranslator::class,
            function (Container $container): TermTranslator {
                return new TermTranslator(
                    $container[TaxonomyRepository::class],
                    $container[UrlFactory::class]
                );
            }
        );

        $container->addService(
            Frontend\WooCommerceShopPageUrlFilter::class,
            function (): Frontend\WooCommerceShopPageUrlFilter {
                return new Frontend\WooCommerceShopPageUrlFilter();
            }
        );
    }

    /**
     * @param Container $container
     */
    private function registerNotContentRelatedTranslations(Container $container)
    {
        $container->addService(
            SearchTranslator::class,
            function (Container $container): SearchTranslator {
                return new SearchTranslator($container[UrlFactory::class]);
            }
        );

        $container->addService(
            DateTranslator::class,
            function (Container $container): DateTranslator {
                return new DateTranslator(
                    $container[UrlFactory::class]
                );
            }
        );

        $container->addService(
            PostTypeTranslator::class,
            function (Container $container): PostTypeTranslator {
                return new PostTypeTranslator(
                    $container[Admin\PostTypeSlugsSettingsRepository::class],
                    $container[UrlFactory::class],
                    $container[ActivePostTypes::class]
                );
            }
        );

        $container->addService(
            FrontPageTranslator::class,
            function (Container $container): FrontPageTranslator {
                return new FrontPageTranslator($container[UrlFactory::class]);
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $translations = $container[Translations::class];

        $translations->registerTranslator(
            $container[FrontPageTranslator::class],
            WordpressContext::TYPE_FRONT_PAGE
        );

        $this->bootstrapPostTranslator($container, $translations);
        $this->bootstrapTermTranslator($container, $translations);
        $this->bootstrapDateTranslation($container, $translations);

        $translations->registerTranslator(
            $container[PostTypeTranslator::class],
            WordpressContext::TYPE_POST_TYPE_ARCHIVE
        );

        $translations->registerTranslator(
            $container[SearchTranslator::class],
            WordpressContext::TYPE_SEARCH
        );

        if (!is_admin()) {
            $this->bootstrapFrontend($container);
        }
    }

    /**
     * @param Container $container
     * @param Translations $translations
     */
    private function bootstrapPostTranslator(Container $container, Translations $translations)
    {
        $postTranslator = $container[PostTranslator::class];
        $postTranslator->registerBaseStructureCallback(
            'product',
            function (): string {
                $permaStructure = get_option('permalink_structure');
                $option = (array)get_option('woocommerce_permalinks');
                return ($permaStructure && $option['product_base'] ? $option['product_base'] : '');
            }
        );
        add_action(
            'setup_theme',
            function () use ($postTranslator) {
                global $wp_rewrite;
                $postTranslator->ensureWpRewrite($wp_rewrite);
            }
        );

        $translations->registerTranslator(
            $postTranslator,
            WordpressContext::TYPE_SINGULAR
        );
    }

    /**
     * @param Container $container
     * @param Translations $translations
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    private function bootstrapTermTranslator(Container $container, Translations $translations)
    {
        // phpcs:enable

        $termTranslator = $container[TermTranslator::class];
        $termTranslator->registerBaseStructureCallback(
            'product_cat',
            function (): string {
                $permaStructure = get_option('permalink_structure', '');
                $option = (array)get_option('woocommerce_permalinks');
                return ($permaStructure && $option['category_base'] ? $option['category_base'] : '');
            }
        );
        $termTranslator->registerBaseStructureCallback(
            'product_tag',
            function (): string {
                $permaStructure = get_option('permalink_structure');
                $option = (array)get_option('woocommerce_permalinks');
                return ($permaStructure && $option['tag_base'] ? $option['tag_base'] : '');
            }
        );

        if (function_exists('wc_get_attribute_taxonomies')) {
            $attributeTaxonomies = wc_get_attribute_taxonomies();
            $attributeTaxonomies and array_walk(
                $attributeTaxonomies,
                function (\stdClass $taxonomy) use ($termTranslator) {
                    $termTranslator->registerBaseStructureCallback(
                        'pa_' . sanitize_key($taxonomy->attribute_name),
                        function () use ($taxonomy) : string {
                            $permaStructure = get_option('permalink_structure');
                            $option = (array)get_option('woocommerce_permalinks');

                            return ($permaStructure && $option['attribute_base']
                                ? "{$option['attribute_base']}/{$taxonomy->attribute_name}"
                                : $taxonomy->attribute_name);
                        }
                    );
                }
            );
        }

        add_action(
            'setup_theme',
            function () use ($termTranslator) {
                global $wp_rewrite;
                $termTranslator->ensureWpRewrite($wp_rewrite);
            }
        );

        $translations->registerTranslator(
            $termTranslator,
            WordpressContext::TYPE_TERM_ARCHIVE
        );
    }

    /**
     * @param Container $container
     * @param Translations $translations
     */
    private function bootstrapDateTranslation(Container $container, Translations $translations)
    {
        $dateTranslation = $container[DateTranslator::class];
        $translations->registerTranslator(
            $dateTranslation,
            WordpressContext::TYPE_DATE_ARCHIVE
        );

        add_action(
            'setup_theme',
            function () use ($dateTranslation) {
                global $wp, $wp_rewrite;
                $dateTranslation->ensureWp($wp);
                $dateTranslation->ensureWpRewrite($wp_rewrite);
            }
        );
    }

    /**
     * @param Container $container
     */
    private function bootstrapFrontend(Container $container)
    {
        add_filter(
            PostTypeTranslator::FILTER_POST_TYPE_PERMALINK,
            function (string $url): string {
                if (!function_exists('wc')) {
                    return $url;
                }
                if (!is_shop()) {
                    return $url;
                }

                $shopPageId = wc_get_page_id('shop');
                $shopPageId and $url = get_permalink($shopPageId);

                if ('publish' !== get_post_status($shopPageId)
                    || !current_user_can('edit_post', $shopPageId)
                ) {
                    return '';
                }

                return (string)$url;
            }
        );
    }
}
