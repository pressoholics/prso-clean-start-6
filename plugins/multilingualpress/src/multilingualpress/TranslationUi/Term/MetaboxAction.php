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

namespace Inpsyde\MultilingualPress\TranslationUi\Term;

use Inpsyde\MultilingualPress\Core\Entity\ActiveTaxonomies;
use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;

final class MetaboxAction implements Metabox\Action
{
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
     * @var ActiveTaxonomies
     */
    private $taxonomies;

    /**
     * @var ContentRelations
     */
    private $contentRelations;

    /**
     * @param MetaboxFields $fields
     * @param MetaboxFieldsHelper $fieldsHelper
     * @param RelationshipContext $relationshipContext
     * @param ActiveTaxonomies $taxonomies
     * @param ContentRelations $contentRelations
     */
    public function __construct(
        MetaboxFields $fields,
        MetaboxFieldsHelper $fieldsHelper,
        RelationshipContext $relationshipContext,
        ActiveTaxonomies $taxonomies,
        ContentRelations $contentRelations
    ) {

        $this->fields = $fields;
        $this->fieldsHelper = $fieldsHelper;
        $this->relationshipContext = $relationshipContext;
        $this->taxonomies = $taxonomies;
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

        $relationshipHelper = new TermRelationSaveHelper($this->contentRelations);

        return $this->doSaveOperation($request, $relationshipHelper, $notices);
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

        $hasRemoteTerm = $this->relationshipContext->hasRemoteTerm();

        if (($relation === MetaboxFields::FIELD_RELATION_NEW && $hasRemoteTerm)
            || ($relation === MetaboxFields::FIELD_RELATION_LEAVE && !$hasRemoteTerm)
        ) {
            return '';
        }

        return $relation;
    }

    /**
     * @param array $values
     * @param TermRelationSaveHelper $relationshipHelper
     * @return array
     */
    private function generateTermData(
        array $values,
        TermRelationSaveHelper $relationshipHelper
    ): array {

        $name = $values[MetaboxFields::FIELD_NAME] ?? '';
        $slug = $values[MetaboxFields::FIELD_SLUG] ?? '';

        if (!$name && !$slug) {
            return [];
        }

        $term = [];
        $name and $term['name'] = $name;
        $slug and $term['slug'] = $slug;

        $description = $values[MetaboxFields::FIELD_DESCRIPTION] ?? '';
        $description and $term['description'] = $description;

        $parent = $values[MetaboxFields::FIELD_PARENT] ?? -1;
        $parent > 0 and $term['parent'] = (int) $parent;
        if ($parent === 0) {
            $term['parent'] = $relationshipHelper->relatedTermParent($this->relationshipContext);
        }

        return $term;
    }

    /**
     * @param Request $request
     * @param TermRelationSaveHelper $relationshipHelper
     * @param PersistentAdminNotices $notices
     * @return bool
     */
    private function doSaveOperation(
        Request $request,
        TermRelationSaveHelper $relationshipHelper,
        PersistentAdminNotices $notices
    ): bool {

        $values = $this->allFieldsValues($request);
        $termData = $this->generateTermData($values, $relationshipHelper);
        if (!$termData) {
            return false;
        }

        $termTaxonomyId = $this->saveTerm($termData, $relationshipHelper);

        if (!$termTaxonomyId) {
            // translators: %s is the language name
            $message = __(
                'Error updating term translation for %s: error updating term in database.',
                'multilingualpress'
            );
            $notices->add(AdminNotice::error($message));

            return false;
        }

        $relationshipHelper->syncMetadata($this->relationshipContext);

        return false;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function allFieldsValues(Request $request): array
    {
        $fields = [];
        $allTabs = $this->fields->allFieldsTabs();
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
     * @param array $termData
     * @param TermRelationSaveHelper $helper
     * @return int
     */
    private function saveTerm(array $termData, TermRelationSaveHelper $helper): int
    {
        $termTaxonomyId = $this->relationshipContext->hasRemoteTerm()
            ? $this->updateTerm(wp_slash($termData))
            : $this->insertTerm(wp_slash($termData));

        if (!$termTaxonomyId) {
            return 0;
        }

        $remoteTerm = get_term_by('term_taxonomy_id', $termTaxonomyId);
        if (!$remoteTerm instanceof \WP_Term) {
            return 0;
        }

        $this->relationshipContext = RelationshipContext::fromExistingAndData(
            $this->relationshipContext,
            [RelationshipContext::REMOTE_TERM_ID => (int)$remoteTerm->term_taxonomy_id]
        );

        if (!$helper->relateTerms($this->relationshipContext)) {
            return 0;
        }

        return (int)$remoteTerm->term_taxonomy_id;
    }

    /**
     * @param array $termData
     * @return int
     */
    private function updateTerm(array $termData): int
    {
        $term = $this->relationshipContext->remoteTerm();
        $update = wp_update_term($term->term_id, $term->taxonomy, $termData);
        if (!is_array($update)
            || empty($update['term_id'])
            || empty($update['term_taxonomy_id'])
        ) {
            return 0;
        }

        return (int)$update['term_taxonomy_id'];
    }

    /**
     * @param array $termData
     * @return int
     */
    private function insertTerm(array $termData): int
    {
        $taxonomy = $this->relationshipContext->sourceTerm()->taxonomy;
        $name = $termData['name'] ?? '';
        $slug = $termData['slug'] ?? '';
        $name or $name = $slug;
        $slug or $slug = sanitize_title($name);
        unset($termData['name']);

        $termExists = term_exists($name, $taxonomy);

        if (is_array($termExists)
            && !empty($termExists['term_id'])
            && !empty($termExists['term_taxonomy_id'])
        ) {
            $this->relationshipContext = RelationshipContext::fromExistingAndData(
                $this->relationshipContext,
                [RelationshipContext::REMOTE_TERM_ID => (int)$termExists['term_taxonomy_id']]
            );

            return $this->updateTerm($termData);
        }

        $insert = wp_insert_term($name, $taxonomy, $termData);
        if (!is_array($insert)
            || empty($insert['term_id'])
            || empty($insert['term_taxonomy_id'])
        ) {
            return 0;
        }

        return (int)$insert['term_taxonomy_id'];
    }
}
