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

namespace Inpsyde\MultilingualPress\Core\Frontend;

use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;

/**
 * Alternate language HTML link tag renderer implementation.
 */
final class AltLanguageHtmlLinkTagRenderer implements AltLanguageRenderer
{

    const FILTER_HREFLANG = 'multilingualpress.hreflang_html_link_tag';
    const FILTER_HREFLANG_X_DEFAULT = 'multilingualpress.hreflang_x_default_url';
    const FILTER_RENDER_HREFLANG = 'multilingualpress.render_hreflang';

    const KSES_TAGS = [
        'link' => [
            'href' => true,
            'hreflang' => true,
            'rel' => true,
        ],
    ];

    /**
     * @var AlternateLanguages
     */
    private $alternateLanguages;

    /**
     * @var SiteRelations
     */
    private $siteRelations;

    /**
     * @param AlternateLanguages $alternateLanguages
     * @param SiteRelations $siteRelations
     */
    public function __construct(
        AlternateLanguages $alternateLanguages,
        SiteRelations $siteRelations
    ) {

        $this->alternateLanguages = $alternateLanguages;
        $this->siteRelations = $siteRelations;
    }

    /**
     * Renders all alternate languages as HTML link tags into the HTML head.
     *
     * @param array ...$args
     *
     * @wp-hook wp_head
     */
    public function render(...$args)
    {
        $translations = iterator_to_array($this->alternateLanguages);
        $xDefault = $this->xDefaultUrl();

        /**
         * Filters if the hreflang links should be rendered.
         *
         * @param bool $render
         * @param string[] $translations
         * @param string $xDefault
         * @param int $type
         */
        if (!apply_filters(
            self::FILTER_RENDER_HREFLANG,
            count($translations) > 1 || $xDefault,
            $translations,
            $xDefault,
            $this->type()
        )) {
            return;
        }

        foreach ($translations as $language => $url) {
            $htmlLinkTag = sprintf(
                '<link rel="alternate" hreflang="%1$s" href="%2$s">',
                esc_attr($language),
                esc_url($url)
            );

            /**
             * Filters the output of the hreflang links in the HTML head.
             *
             * @param string $htmlLinkTag
             * @param string $language
             * @param string $url
             */
            $htmlLinkTag = (string)apply_filters(
                self::FILTER_HREFLANG,
                $htmlLinkTag,
                $language,
                $url
            );

            echo wp_kses($htmlLinkTag, self::KSES_TAGS);
        }

        if ($xDefault) {
            printf('<link rel="alternate" href="%s" hreflang="x-default">', esc_url($xDefault));
        }
    }

    /**
     * Returns the output type.
     *
     * @return int
     */
    public function type(): int
    {
        return self::TYPE_HTML_LINK_TAG;
    }

    /**
     * @return string
     *
     * TODO: Maybe we could later implement an UI to set a "default" site for each "group" of sites
     */
    private function xDefaultUrl(): string
    {
        $xDefault = apply_filters(
            self::FILTER_HREFLANG_X_DEFAULT,
            null,
            $this->siteRelations->relatedSiteIds(get_current_blog_id(), true),
            $this->alternateLanguages
        );

        if (!$xDefault || !is_string($xDefault) || !filter_var($xDefault, FILTER_SANITIZE_URL)) {
            return '';
        }

        if (wp_validate_redirect($xDefault, '') === $xDefault) {
            return $xDefault;
        }

        return '';
    }
}
