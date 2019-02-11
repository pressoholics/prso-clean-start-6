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

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;

class SiteRelationsChecker
{
    /**
     * @var SiteRelations
     */
    private $siteRelations;

    /**
     * @param SiteRelations $siteRelations
     */
    public function __construct(SiteRelations $siteRelations)
    {
        $this->siteRelations = $siteRelations;
    }

    /**
     * Checks if there are at least two sites related to each other, and renders an admin notice if not.
     *
     * @return bool
     */
    public function checkRelations(): bool
    {
        if (wp_doing_ajax()
            || is_network_admin()
            || !is_super_admin()
            || $this->siteRelations->allRelations()
        ) {
            return true;
        }

        $this->renderAdminNotice();

        return false;
    }

    /**
     * Renders the admin notice.
     */
    private function renderAdminNotice()
    {
        $message = __(
            "You didn't set up any site relationships. You have to set up these first to use MultilingualPress.",
            'multilingualpress'
        );
        $message .= __(
            'Please go to Network Admin > Sites > and choose a site to edit. Then go to the tab "MultilingualPress" and set up the relationships.',
            'multilingualpress'
        );

        AdminNotice::error($message)->inAllScreens()->render();
    }
}
