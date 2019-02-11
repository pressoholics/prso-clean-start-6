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

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTab;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTabData;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageView;

class SettingsPageTabsBuilder
{
    /**
     * @var array
     */
    private $tabs = [];

    /**
     * @param SettingsPageTabData $tabData
     * @param SettingsPageView $pageView
     * @return SettingsPageTabsBuilder
     */
    public function addTabDataAndView(
        SettingsPageTabData $tabData,
        SettingsPageView $pageView
    ): self {

        $this->tabs[] = [$tabData, $pageView];

        return $this;
    }

    /**
     * @return SettingsPageTab[]
     */
    public function build(): array
    {
        $tabs = [];

        /**
         * @var SettingsPageTabData $data
         * @var SettingsPageView $view
         */
        foreach ($this->tabs as list($data, $view)) {
            $tabs[$data->id()] = new SettingsPageTab($data, $view);
        }

        return $tabs;
    }
}
