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

class WordpressContext
{
    const TYPE_ADMIN = 'admin';
    const TYPE_FRONT_PAGE = 'front-page';
    const TYPE_POST_TYPE_ARCHIVE = 'post-type-archive';
    const TYPE_SEARCH = 'search';
    const TYPE_SINGULAR = 'post';
    const TYPE_TERM_ARCHIVE = 'term';
    const TYPE_DATE_ARCHIVE = 'date-archive';

    /**
     * @var callable[]
     */
    private $callbacks;

    /**
     * @var string[]
     */
    private $types;

    /**
     * @param \WP_Query|null $wpQuery
     */
    public function __construct(\WP_Query $wpQuery = null)
    {
        if (!$wpQuery) {
            $wpQuery = $GLOBALS['wp_query'] ?? new \WP_Query();
        }

        // Checks if the current request is for a single post or the page for posts.
        $isSingular = function () use ($wpQuery): bool {
            return $wpQuery->is_singular() || ($wpQuery->is_home() && !$wpQuery->is_front_page());
        };

        $this->callbacks = [
            [self::TYPE_ADMIN, 'is_admin'],
            [self::TYPE_FRONT_PAGE, [$wpQuery, 'is_front_page']],
            [self::TYPE_POST_TYPE_ARCHIVE, [$wpQuery, 'is_post_type_archive']],
            [self::TYPE_SEARCH, [$wpQuery, 'is_search']],
            [self::TYPE_TERM_ARCHIVE, [$wpQuery, 'is_tax']],
            [self::TYPE_TERM_ARCHIVE, [$wpQuery, 'is_tag']],
            [self::TYPE_TERM_ARCHIVE, [$wpQuery, 'is_category']],
            [self::TYPE_DATE_ARCHIVE, [$wpQuery, 'is_date']],
            [self::TYPE_SINGULAR, $isSingular],
        ];
    }

    /**
     * Returns the (first) post type of the current request.
     *
     * @return string
     */
    public function postType(): string
    {
        $postType = (array)get_query_var('post_type');

        return (string)reset($postType);
    }

    /**
     * Returns the ID of the queried object.
     *
     * For term archives, this is the term taxonomy ID (not the term ID).
     *
     * @return int
     */
    public function queriedObjectId(): int
    {
        if (is_category() || is_tag() || is_tax()) {
            $queriedObject = get_queried_object();

            return (int)($queriedObject->term_taxonomy_id ?? 0);
        }

        return (int)get_queried_object_id();
    }

    /**
     * Returns all types of the current request or empty string on failure.
     *
     * @return string[]
     */
    public function types(): array
    {
        if (is_array($this->types)) {
            return $this->types;
        }

        $this->types = [];
        foreach ($this->callbacks as list($type, $callback)) {
            if ($callback()) {
                $this->types[] = $type;
            }
        }

        return $this->types;
    }

    /**
     * Returns the type of the current request or empty string on failure.
     *
     * @return string
     */
    public function type(): string
    {
        $types = $this->types();

        return $types ? reset($types) : '';
    }

    /**
     * Returns if the current request match given type.
     *
     * @param string $type
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $type && in_array($type, $this->types(), true);
    }
}
