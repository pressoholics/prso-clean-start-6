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
use Inpsyde\MultilingualPress\Framework\NetworkState;
use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;

class Trasher
{

    /**
     * @var ActivePostTypes
     */
    private $activePostTypes;

    /**
     * @var ContentRelations
     */
    private $contentRelations;

    /**
     * @var TrasherSettingRepository
     */
    private $settingRepository;

    /**
     * @param TrasherSettingRepository $settingRepository
     * @param ContentRelations $contentRelations
     * @param ActivePostTypes $activePostTypes
     */
    public function __construct(
        TrasherSettingRepository $settingRepository,
        ContentRelations $contentRelations,
        ActivePostTypes $activePostTypes
    ) {

        $this->settingRepository = $settingRepository;
        $this->contentRelations = $contentRelations;
        $this->activePostTypes = $activePostTypes;
    }

    /**
     * Trashes all related posts.
     *
     * @param int $postId
     * @return int
     *
     * @wp-hook wp_trash_post
     */
    public function trashRelatedPosts($postId): int
    {
        if (!$this->activePostTypes->arePostTypesActive((string)get_post_type($postId))) {
            return 0;
        }

        static $trashingRelatedPosts;
        if ($trashingRelatedPosts || !$this->settingRepository->settingForPost((int)$postId)) {
            return 0;
        }

        $trashingRelatedPosts = true;
        $currentSiteId = get_current_blog_id();
        $relatedPosts = $this->contentRelations->relations($currentSiteId, (int)$postId, 'post');

        unset($relatedPosts[$currentSiteId]);

        if (!$relatedPosts) {
            return 0;
        }

        $trashedPosts = 0;
        $networkState = NetworkState::create();
        foreach ($relatedPosts as $siteId => $relPostId) {
            switch_to_blog($siteId);
            is_array(wp_trash_post($relPostId)) and $trashedPosts++;
        }
        $networkState->restore();

        $trashingRelatedPosts = false;

        return $trashedPosts;
    }
}
