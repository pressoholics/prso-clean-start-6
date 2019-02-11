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

class SettingsRepository
{
    const META_KEY_USER = 'multilingualpress_redirect';
    const OPTION_SITE = 'multilingualpress_redirect';

    /**
     * Returns the redirect setting for the site with the given ID.
     *
     * @param int $siteId
     * @return bool
     */
    public function shouldRedirectBySite(int $siteId = 0): bool
    {
        return (bool)get_blog_option(
            $siteId ?: get_current_blog_id(),
            self::OPTION_SITE
        );
    }

    /**
     * Returns the redirect setting for the user with the given ID.
     *
     * @param int $userId
     * @return bool
     */
    public function shouldRedirectByUser(int $userId = 0): bool
    {
        return (bool)get_user_meta(
            $userId ?: get_current_user_id(),
            self::META_KEY_USER
        );
    }
}
