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

namespace Inpsyde\MultilingualPress\Framework;

/**
 * Provides access to the correct basedir and baseurl paths of the current site's
 * uploads folder.
 */
class BasePathAdapter
{
    /**
     * @var string[][]
     */
    private $uploadsDirs = [];

    /**
     * Returns the correct basedir path of the current site's uploads folder.
     *
     * @return string
     */
    public function basedir(): string
    {
        $uploads = $this->uploadsDir();

        return (string)$uploads['basedir'];
    }

    /**
     * Returns the correct baseurl path of the current site's uploads folder.
     *
     * @return string
     */
    public function baseurl(): string
    {
        $uploads = $this->uploadsDir();

        $baseUrl = (string)$uploads['baseurl'];

        if (!is_subdomain_install()) {
            return $baseUrl;
        }

        $siteUrl = get_option('siteurl');

        if (0 === strpos($baseUrl, $siteUrl)) {
            return $baseUrl;
        }

        return str_replace(
            wp_parse_url($baseUrl, PHP_URL_HOST),
            wp_parse_url($siteUrl, PHP_URL_HOST),
            $baseUrl
        );
    }

    /**
     * Returns the current site's uploads folder paths.
     *
     * @return string[]
     */
    private function uploadsDir(): array
    {
        $currentSiteId = get_current_blog_id();

        if (empty($this->uploadsDirs[$currentSiteId])) {
            $this->uploadsDirs[$currentSiteId] = wp_upload_dir();
        }

        return $this->uploadsDirs[$currentSiteId];
    }
}
