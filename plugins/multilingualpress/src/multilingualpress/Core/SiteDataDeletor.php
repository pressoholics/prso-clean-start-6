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

namespace Inpsyde\MultilingualPress\Core;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;

/**
 * Deletes all plugin-specific data when a site is deleted.
 */
class SiteDataDeletor
{
    /**
     * @var ContentRelations
     */
    private $contentRelations;

    /**
     * @var SiteRelations
     */
    private $siteRelations;

    /**
     * @var SiteSettingsRepository
     */
    private $siteSettingsRepository;

    /**
     * @param ContentRelations $contentRelations
     * @param SiteRelations $siteRelations
     * @param SiteSettingsRepository $siteSettingsRepository
     */
    public function __construct(
        ContentRelations $contentRelations,
        SiteRelations $siteRelations,
        SiteSettingsRepository $siteSettingsRepository
    ) {

        $this->contentRelations = $contentRelations;
        $this->siteRelations = $siteRelations;
        $this->siteSettingsRepository = $siteSettingsRepository;
    }

    /**
     * Deletes all plugin-specific data of the site with the given ID.
     *
     * @param int $siteId
     *
     * @wp-hook delete_blog
     */
    public function deleteSiteData($siteId)
    {
        $siteId = (int)$siteId;

        $this->contentRelations->deleteAllRelationsForSite($siteId);
        $this->siteRelations->deleteRelation($siteId);
        $settings = $this->siteSettingsRepository->allSettings();
        if (isset($settings[$siteId])) {
            unset($settings[$siteId]);
            $this->siteSettingsRepository->updateSettings($settings);
        }
    }
}
