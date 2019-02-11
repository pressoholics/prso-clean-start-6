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

use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;

use function Inpsyde\MultilingualPress\siteLanguageName;

final class MetaboxAction implements Metabox\Action
{
    /**
     * @var array
     */
    private static $calledCount = [];

    /**
     * @var MetaboxFields
     */
    private $fields;

    /**
     * @var MetaboxFieldsHelper
     */
    private $fieldsHelper;

    /**
     * @var RelationshipContext
     */
    private $relationshipContext;

    /**
     * @var ActivePostTypes
     */
    private $postTypes;

    /**
     * @var ContentRelations
     */
    private $contentRelations;

    /**
     * @var SourcePostSaveContext
     */
    private $sourcePostContext;

    /**
     * @param MetaboxFields $fields
     * @param MetaboxFieldsHelper $fieldsHelper
     * @param RelationshipContext $relationshipContext
     * @param ActivePostTypes $postTypes
     * @param ContentRelations $contentRelations
     */
    public function __construct(
        MetaboxFields $fields,
        MetaboxFieldsHelper $fieldsHelper,
        RelationshipContext $relationshipContext,
        ActivePostTypes $postTypes,
        ContentRelations $contentRelations
    ) {

        $this->fields = $fields;
        $this->fieldsHelper = $fieldsHelper;
        $this->relationshipContext = $relationshipContext;
        $this->postTypes = $postTypes;
        $this->contentRelations = $contentRelations;
    }

    /**
     * @inheritdoc
     */
    public function save(Request $request, PersistentAdminNotices $notices): bool
    {
        $relation = $this->saveOperation($request);
        if (!$relation) {
            return false;
        }

        if (!$this->isValidSaveRequest($this->sourceContext($request))) {
            return false;
        }

        $relationshipHelper = new PostRelationSaveHelper($this->contentRelations);

        return $this->doSaveOperation($relation, $request, $relationshipHelper, $notices);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function saveOperation(Request $request): string
    {
        $relation = $this->fieldsHelper->fieldRequestValue($request, MetaboxFields::FIELD_RELATION);

        if ($relation !== MetaboxFields::FIELD_RELATION_NEW
            && $relation !== MetaboxFields::FIELD_RELATION_LEAVE
        ) {
            return '';
        }

        $hasRemotePost = $this->relationshipContext->hasRemotePost();

        if (($relation === MetaboxFields::FIELD_RELATION_NEW && $hasRemotePost)
            || ($relation === MetaboxFields::FIELD_RELATION_LEAVE && !$hasRemotePost)
        ) {
            return '';
        }

        return $relation;
    }

    /**
     * @param Request $request
     * @return SourcePostSaveContext
     */
    private function sourceContext(Request $request): SourcePostSaveContext
    {
        if ($this->sourcePostContext) {
            return $this->sourcePostContext;
        }

        switch_to_blog($this->relationshipContext->sourceSiteId());
        $this->sourcePostContext = new SourcePostSaveContext(
            $this->relationshipContext->sourcePost(),
            $this->postTypes,
            $request
        );
        restore_current_blog();

        return $this->sourcePostContext;
    }

    /**
     * @param array $values
     * @param PostRelationSaveHelper $relationshipHelper
     * @return array
     */
    private function generatePostData(
        array $values,
        PostRelationSaveHelper $relationshipHelper
    ): array {

        $language = siteLanguageName($this->relationshipContext->remoteSiteId());
        $source = $this->relationshipContext->sourcePost();
        $hasRemote = $this->relationshipContext->hasRemotePost();

        $title = $values[MetaboxFields::FIELD_TITLE] ?? '';
        if (!$title && !$hasRemote) {
            $title = $source->post_title . " ({$language})";
        }
        $slug = $values[MetaboxFields::FIELD_SLUG] ?? '';
        if (!$slug && !$hasRemote) {
            $slug = sanitize_title($title);
        }
        $status = $values[MetaboxFields::FIELD_STATUS] ?? '';
        if (!$status && !$hasRemote) {
            $status = 'draft';
        }
        $excerpt = $values[MetaboxFields::FIELD_EXCERPT] ?? '';

        $post = [];
        $hasRemote and $post['ID'] = $this->relationshipContext->remotePostId();
        $title and $post['post_title'] = $title;
        $slug and $post['post_name'] = $slug;
        $status and $post['post_status'] = $status;
        $excerpt and $post['post_excerpt'] = $excerpt;

        if ($values[MetaboxFields::FIELD_COPY_CONTENT] ?? false) {
            $post['post_content'] = $source->post_content;
        }

        if (!$hasRemote) {
            $base = $source;
            $post['post_parent'] =$relationshipHelper->relatedPostParent($this->relationshipContext);
            $post['post_type'] = $base->post_type;
            $post['post_author'] = $base->post_author;
            $post['comment_status'] = $base->comment_status;
            $post['ping_status'] = $base->ping_status;
            $post['post_password'] = $base->post_password;
            $post['menu_order'] = $base->menu_order;
        }

        return $post;
    }

    /**
     * @param string $operation
     * @param Request $request
     * @param PostRelationSaveHelper $relationshipHelper
     * @param PersistentAdminNotices $notices
     * @return bool
     */
    private function doSaveOperation(
        string $operation,
        Request $request,
        PostRelationSaveHelper $relationshipHelper,
        PersistentAdminNotices $notices
    ): bool {

        $values = $this->allFieldsValues($request);
        $post = $this->generatePostData($values, $relationshipHelper);

        if (!$post) {
            return false;
        }

        $postId = $this->savePost($operation, $post, $relationshipHelper);

        if (!$postId) {
            // translators: %s is the language name
            $message = __(
                'Error updating translation for %s: error updating post in database.',
                'multilingualpress'
            );
            $notices->add(AdminNotice::error($message));

            return false;
        }

        $relationshipHelper->syncMetadata($this->relationshipContext);

        if ($values[MetaboxFields::FIELD_COPY_FEATURED] ?? false) {
            $relationshipHelper->syncThumb(
                $this->relationshipContext,
                $this->sourceContext($request)->featuredImagePath()
            );
        }

        $syncTaxonomies = $values[MetaboxFields::FIELD_COPY_TAXONOMIES] ?? false;
        $terms = $syncTaxonomies ? [] : ($values[MetaboxFields::FIELD_TAXONOMIES] ?? false);
        $slugs = $values[MetaboxFields::FIELD_TAXONOMY_SLUGS] ?? [];

        if ($syncTaxonomies) {
            $relationshipHelper->syncTaxonomyTerms($this->relationshipContext);
        }

        if (!$syncTaxonomies && ($terms || $slugs) && $this->relationshipContext->hasRemotePost()) {
            $this->saveTaxonomyTerms($terms, $slugs ? array_fill_keys($slugs, 1) : []);
        }

        return false;
    }

    /**
     * Check if the current request should be processed by save().
     *
     * @param SourcePostSaveContext $context
     * @return bool
     */
    private function isValidSaveRequest(SourcePostSaveContext $context): bool
    {
        $site = $this->relationshipContext->remoteSiteId();
        array_key_exists($site, self::$calledCount) or self::$calledCount[$site] = 0;

        // For auto-drafts, 'save_post' is called twice, resulting in doubled drafts for translations.
        self::$calledCount[$site]++;

        return
            $context->postType()
            && $context->postStatus()
            && ($context->postStatus() !== 'auto-draft' || self::$calledCount[$site] === 1);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function allFieldsValues(Request $request): array
    {
        $fields = [];
        $allTabs = $this->fields->allFieldsTabs($this->relationshipContext);
        /** @var MetaboxTab $tab */
        foreach ($allTabs as $tab) {
            $fields += $this->tabFieldsValues($tab, $request);
        }

        return $fields;
    }

    /**
     * @param MetaboxTab $tab
     * @param Request $request
     * @return array
     */
    private function tabFieldsValues(MetaboxTab $tab, Request $request): array
    {
        $fields = [];
        if (!$tab->enabled($this->relationshipContext)) {
            return $fields;
        }

        $tabFields = $tab->fields();
        foreach ($tabFields as $field) {
            if ($field->enabled($this->relationshipContext)) {
                $fields[$field->key()] = $field->requestValue($request, $this->fieldsHelper);
            }
        }

        return $fields;
    }

    /**
     * @param string $operation
     * @param array $post
     * @param PostRelationSaveHelper $helper
     * @return int
     */
    private function savePost(string $operation, array $post, PostRelationSaveHelper $helper): int
    {
        $postId = $operation === MetaboxFields::FIELD_RELATION_NEW
            ? wp_insert_post(wp_slash($post), true)
            : wp_update_post(wp_slash($post), true);

        if (!is_numeric($postId) || !$postId) {
            return 0;
        }

        $remotePost = get_post($postId);
        if (!$remotePost instanceof \WP_Post) {
            return 0;
        }

        $this->relationshipContext = RelationshipContext::fromExistingAndData(
            $this->relationshipContext,
            [RelationshipContext::REMOTE_POST_ID => $postId]
        );

        if (!$helper->relatePosts($this->relationshipContext)) {
            return 0;
        }

        return (int)$remotePost->ID;
    }

    /**
     * @param array $taxonomyTerms
     * @param array $taxonomies
     */
    private function saveTaxonomyTerms(array $taxonomyTerms, array $taxonomies)
    {
        $post = $this->relationshipContext->remotePost();

        foreach ($taxonomyTerms as $taxonomy => $termIds) {
            sort($termIds);
            $currentTerms = get_the_terms($post, $taxonomy);
            if (is_wp_error($currentTerms)) {
                continue;
            }
            $currentTermIds = [];
            if (!is_array($currentTerms)) {
                $currentTerms = [];
            }
            if ($currentTerms) {
                $currentTermIds = wp_parse_id_list(array_column($currentTerms, 'term_id'));
                $currentTermIds and sort($currentTermIds);
            }

            unset($taxonomies[$taxonomy]);
            if ($currentTermIds === $termIds) {
                continue;
            }

            wp_set_object_terms($post->ID, $termIds, $taxonomy, false);
        }

        foreach ($taxonomies as $taxonomy => $remove) {
            if (!$remove) {
                continue;
            }

            $taxonomyObject = get_taxonomy($taxonomy);

            if (!$taxonomyObject
                || !current_user_can($taxonomyObject->cap->delete_terms, $taxonomy)
            ) {
                continue;
            }

            wp_set_object_terms($post->ID, [], $taxonomy, false);
        }
    }
}
