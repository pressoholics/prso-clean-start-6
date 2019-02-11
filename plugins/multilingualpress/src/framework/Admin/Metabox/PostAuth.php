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

namespace Inpsyde\MultilingualPress\Framework\Admin\Metabox;

use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;

/**
 * @package MultilingualPress
 * @license http://opensource.org/licenses/MIT MIT
 */
final class PostAuth implements Auth
{
    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @param \WP_Post $post
     * @param Nonce $nonce
     */
    public function __construct(\WP_Post $post, Nonce $nonce)
    {
        $this->post = $post;
        $this->nonce = $nonce;
    }

    /**
     * @inheritdoc
     */
    public function authorized(Request $request): bool
    {
        $type = get_post_type_object($this->post->post_type);

        if (!current_user_can($type->cap->edit_post, $this->post->ID)) {
            return false;
        }

        return $this->nonce->isValid();
    }
}
