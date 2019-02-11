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

namespace Inpsyde\MultilingualPress\TranslationUi\Post;

use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;

class SourcePostSaveContext
{
    const POST_TYPE = 'real_post_type';
    const POST_ID = 'real_post_id';
    const POST = 'post';
    const POST_STATUS = 'original_post_status';
    const FEATURED_IMG_PATH = 'featured_image_path';

    const CONNECTABLE_STATUSES = [
        'auto-draft',
        'draft',
        'future',
        'private',
        'publish',
    ];

    /**
     * @var \WP_Post
     */
    private $sourcePost;

    /**
     * @var ActivePostTypes
     */
    private $postTypes;
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $postType;

    /**
     * @var string
     */
    private $postStatus;

    /**
     * @var string
     */
    private $thumbPath;

    /**
     * @param \WP_Post $sourcePost
     * @param ActivePostTypes $postTypes
     * @param Request $request
     */
    public function __construct(\WP_Post $sourcePost, ActivePostTypes $postTypes, Request $request)
    {
        $this->sourcePost = $sourcePost;
        $this->postTypes = $postTypes;
        $this->request = $request;
        $this->postType();
        $this->postStatus();
        $this->featuredImagePath();
    }

    /**
     * @return string
     */
    public function postType(): string
    {
        if (is_string($this->postType)) {
            return $this->postType;
        }

        $type = (string)$this->request->bodyValue(
            'post_type',
            INPUT_POST,
            FILTER_SANITIZE_STRING
        );

        $type or $type = $this->sourcePost->post_type;

        if (!$this->postTypes->arePostTypesActive($type)) {
            $type = '';
        }

        $this->postType = $type;
        $this->sourcePost->post_type = $type;

        return $this->postType;
    }

    /**
     * @return string
     */
    public function postStatus(): string
    {
        if (is_string($this->postStatus)) {
            return $this->postStatus;
        }

        $status = (string)$this->request->bodyValue(
            'original_post_status',
            INPUT_POST,
            FILTER_SANITIZE_STRING
        );

        if (!$status) {
            $status = $this->sourcePost->post_status;
        }

        if (!in_array($status, self::CONNECTABLE_STATUSES, true)) {
            $status = '';
        }

        $this->postStatus = $status;
        $this->sourcePost->post_status = $status;

        return $this->postStatus;
    }

    /**
     * Fetch data of original featured image.
     *
     * @return string
     */
    public function featuredImagePath(): string
    {
        if (is_string($this->thumbPath)) {
            return $this->thumbPath;
        }

        $thumbId = get_post_thumbnail_id($this->sourcePost);
        if (!$thumbId) {
            $this->thumbPath = '';

            return '';
        }

        $thumbPost = get_post($thumbId);
        if (!$thumbPost || !wp_attachment_is_image($thumbPost)) {
            $this->thumbPath = '';

            return '';
        }

        $uploadDir = wp_upload_dir()['basedir'] ?? '';
        if (!$uploadDir) {
            $this->thumbPath = '';

            return '';
        }

        $thumbRelPath = get_post_meta($thumbId, '_wp_attached_file', true);
        if (!$thumbRelPath) {
            $meta = (array)(wp_get_attachment_metadata($thumbId) ?: []);
            $thumbRelPath = $meta['file'] ?? '';
        }
        if (!$thumbRelPath) {
            $this->thumbPath = '';

            return '';
        }

        $thumbAbsPath = "{$uploadDir}/{$thumbRelPath}";

        $this->thumbPath = is_readable($thumbAbsPath) ? $thumbAbsPath : '';

        return $this->thumbPath;
    }
}
