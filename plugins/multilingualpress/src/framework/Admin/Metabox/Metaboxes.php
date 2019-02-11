<?php declare(strict_types=1); # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\MultilingualPress\Framework\Admin\Metabox;

use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Http\RequestGlobalsManipulator;
use Inpsyde\MultilingualPress\Framework\Nonce\WpNonce;

use function Inpsyde\MultilingualPress\printNonceField;
use function Inpsyde\MultilingualPress\siteExists;

class Metaboxes
{
    const REGISTER_METABOXES = 'multilingualpress.register_metaboxes';
    const ACTION_INSIDE_METABOX_AFTER = 'multilingualpress.inside_box_after';
    const ACTION_INSIDE_METABOX_BEFORE = 'multilingualpress.inside_box_before';
    const ACTION_SHOW_METABOXES = 'multilingualpress.show_metaboxes';
    const ACTION_SHOWED_METABOXES = 'multilingualpress.showed_metaboxes';
    const ACTION_SAVE_METABOXES = 'multilingualpress.save_metaboxes';
    const ACTION_SAVED_METABOXES = 'multilingualpress.saved_metaboxes';
    const ACTION_SAVE_METABOX = 'multilingualpress.save_metabox';
    const ACTION_SAVED_METABOX = 'multilingualpress.saved_metabox';
    const FILTER_SAVE_METABOX_ON_EMPTY_POST = 'multilingualpress.metabox_save_on_empty_post';
    const ACTION_UNAUTHORIZED_METABOX_SAVE = 'multilingualpress.unauthorized_box_save';
    const FILTER_METABOX_ENABLED = 'multilingualpress.metabox_enabled';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RequestGlobalsManipulator
     */
    private $globalsManipulator;

    /**
     * @var PersistentAdminNotices
     */
    private $notices;

    /**
     * @var Metabox[]
     */
    private $boxes = [];

    /**
     * @var bool
     */
    private $locked = true;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var string
     */
    private $registeringFor = '';

    /**
     * @var string
     */
    private $saving = '';

    /**
     * @param Request $request
     * @param RequestGlobalsManipulator $globalsManipulator
     * @param PersistentAdminNotices $notices
     */
    public function __construct(
        Request $request,
        RequestGlobalsManipulator $globalsManipulator,
        PersistentAdminNotices $notices
    ) {

        $this->request = $request;
        $this->globalsManipulator = $globalsManipulator;
        $this->notices = $notices;
    }

    /**
     * @return void
     */
    public function init()
    {
        if (!is_admin()) {
            return;
        }

        add_action('current_screen', function (\WP_Screen $screen) {
            if ($screen->taxonomy) {
                $this->initForTerm($screen->taxonomy);
                add_action("{$screen->taxonomy}_edit_form", [$this, 'printTermBoxes']);

                return;
            }
            $this->initForPost();
        }, PHP_INT_MAX);
    }

    /**
     * @param Metabox[] $boxes
     *
     * @return Metaboxes
     */
    public function addBox(Metabox ...$boxes): Metaboxes
    {
        if (!is_admin()) {
            return $this;
        }

        if ($this->locked) {
            throw new \BadMethodCallException('Cannot add boxes when controller is locked.');
        }

        if (!$this->entity->valid()
            || !in_array($this->registeringFor, [Metabox::SAVE, Metabox::SHOW], true)
        ) {
            return $this;
        }

        $isPost = $this->entity->is(\WP_Post::class);
        $isTerm = $this->entity->is(\WP_Term::class);

        foreach ($boxes as $box) {
            if (($isPost && $box instanceof PostMetabox)
                || ($isTerm && $box instanceof TermMetabox)
            ) {
                $this->boxes[$box->createInfo($this->registeringFor, $this->entity)->id()] = $box;
            }
        }

        return $this;
    }

    /**
     * WordPress does not print metaboxes for terms, let's fix this.
     *
     * @param \WP_Term $term
     */
    public function printTermBoxes(\WP_Term $term)
    {
        if (!is_admin() || current_filter() !== "{$term->taxonomy}_edit_form") {
            return;
        }

        global $wp_meta_boxes;
        if (empty($wp_meta_boxes["edit-{$term->taxonomy}"])) {
            return;
        }

        $script = '!function(J,D){J(function(){J(".meta-box-sortables").sortable();'
            . 'J(D).on("click",".termbox-container button.handlediv",function(){'
            . 'var D=J(this),t=D.siblings(".inside");t.toggle();'
            . 'var e=t.is(":visible")?"true":"false";'
            . 'D.attr("aria-expanded",e)})})}(jQuery,document);';
        wp_enqueue_script('jquery-ui-sortable');
        wp_add_inline_script('jquery-ui-sortable', $script);

        print '<div id="poststuff"><div class="termbox-container">';
        // WordPress does not print metaboxes for terms, let's fix this
        do_meta_boxes("edit-{$term->taxonomy}", 'side', $term);
        do_meta_boxes("edit-{$term->taxonomy}", 'normal', $term);
        do_meta_boxes("edit-{$term->taxonomy}", 'advanced', $term);
        print '</div></div>';
    }

    /**
     * @return bool
     */
    private function initForPost(): bool
    {
        // Show Boxes
        add_action(
            'add_meta_boxes',
            function ($postType, $post) {
                if ($post instanceof \WP_Post) {
                    $entity = new Entity($post);
                    $this->prepareTarget(new Entity($post), Metabox::SHOW);
                    do_action(self::ACTION_SHOW_METABOXES, $entity);
                    array_walk($this->boxes, [$this, 'addMetabox']);
                    $this->releaseTarget();
                    do_action(self::ACTION_SHOWED_METABOXES, $entity);
                }
            },
            100,
            2
        );

        // Save Boxes even if WordPress says content is empty.
        add_filter(
            'wp_insert_post_empty_content',
            function ($empty, array $data) {
                global $post;
                if (!$empty || !$post instanceof \WP_Post || !$post->ID) {
                    return $empty;
                }

                if (apply_filters(self::FILTER_SAVE_METABOX_ON_EMPTY_POST, true, $post, $data)) {
                    $this->onPostSave($post);
                }

                return $empty;
            },
            PHP_INT_MAX,
            2
        );

        // Save Boxes
        add_action(
            'wp_insert_post',
            function ($postId, \WP_Post $post) {
                $this->onPostSave($post);
            },
            100,
            2
        );

        return true;
    }

    /**
     * @param string $taxonomy
     *
     * @return bool
     */
    private function initForTerm(string $taxonomy): bool
    {
        // Show Boxes
        add_action(
            "{$taxonomy}_pre_edit_form",
            function (\WP_Term $term) {
                $entity = new Entity($term);
                $this->prepareTarget($entity, Metabox::SHOW);
                do_action(self::ACTION_SHOW_METABOXES, $entity);
                array_walk($this->boxes, [$this, 'addMetabox']);
                $this->releaseTarget();
                do_action(self::ACTION_SHOWED_METABOXES, $entity);
            },
            1
        );

        // Save Boxes
        add_action(
            'edit_term',
            function ($termId, $termTaxonomyId, $termTaxonomy) use ($taxonomy) {
                // This check allows to edit term object inside BoxAction::save() without recursion.
                if ($this->saving === 'term') {
                    return;
                }

                $term = get_term_by('term_taxonomy_id', $termTaxonomyId);

                if (!$term instanceof \WP_Term
                    || (int)$term->term_id !== (int)$termId
                    || $term->taxonomy !== $termTaxonomy
                    || $term->taxonomy !== $taxonomy
                ) {
                    return;
                }

                $this->saving = 'term';
                $entity = new Entity($term);
                $this->prepareTarget($entity, Metabox::SAVE);
                do_action(self::ACTION_SAVE_METABOXES, $entity);
                $this->saveMetaBoxes();
                do_action(self::ACTION_SAVED_METABOXES, $entity);
                $this->releaseTarget();
                $this->saving = '';
            },
            100,
            3
        );

        return true;
    }

    /**
     * @param Entity $entity
     * @param string $showOrSave
     */
    private function prepareTarget(Entity $entity, string $showOrSave)
    {
        $this->entity = $entity;
        $this->registeringFor = $showOrSave;
        $this->boxes = [];
        $this->locked = false;
        if ($this->entity->valid()) {
            do_action(self::REGISTER_METABOXES, $this, $this->entity, $showOrSave);
        }
        $this->locked = true;
    }

    /**
     * @param Metabox|PostMetabox|TermMetabox $box
     * @param string $type
     * @return bool
     */
    private function isBoxEnabled(Metabox $box, string $type): bool
    {
        if (!$this->entity->valid()) {
            return false;
        }

        $accept = false;
        /** @var \WP_Post|\WP_Term $object */
        $object = $this->entity->expose();
        switch (true) {
            case $this->entity->is(\WP_Post::class) && $box instanceof PostMetabox:
                $accept = $box->acceptPost($object, $type);
                break;
            case $this->entity->is(\WP_Term::class) && $box instanceof TermMetabox:
                $accept = $box->acceptTerm($object, $type);
                break;
        }

        return (bool)apply_filters(self::FILTER_METABOX_ENABLED, $accept, $box, $object);
    }

    /**
     * @param Metabox|PostMetabox|TermMetabox $box
     * @param string $boxId
     */
    private function addMetabox(Metabox $box, string $boxId)
    {
        if (!$this->isBoxEnabled($box, Metabox::SHOW)) {
            return;
        }

        $isPost = $this->entity->is(\WP_Post::class);
        /** @var \WP_Post|\WP_Term $object */
        $object = $this->entity->expose();
        $info = $box->createInfo(Metabox::SHOW, $this->entity);

        $boxSuffix = $isPost ? '-postbox' : '-termbox';
        $context = $info->context();
        $screen = $isPost ? null : "edit-{$object->taxonomy}";
        ($context === Info::CONTEXT_SIDE && $isPost) and $screen = $object->post_type;

        add_meta_box(
            $boxId . $boxSuffix,
            $info->title(),
            static function ($object) use ($boxId, $box, $info, $isPost) { // phpcs:ignore
                $siteId = $box->siteId();
                if (!siteExists($siteId)) {
                    // translators: %s is the site ID.
                    $message = __('Site %s is not accessible.', 'multilingualpress');
                    print esc_html(sprintf($message, $siteId));
                    return;
                }
                /** @var \WP_Post|\WP_Term $object */
                $objectId = $object instanceof \WP_Post ? $object->ID : $object->term_id;
                printNonceField((new WpNonce($boxId . "-{$objectId}"))->withSite($siteId));
                switch_to_blog($siteId);
                do_action(self::ACTION_INSIDE_METABOX_BEFORE, $box, $object, $info);
                $view = $isPost ? $box->viewForPost($object) : $box->viewForTerm($object);
                $view->render($info);
                do_action(self::ACTION_INSIDE_METABOX_AFTER, $box, $object, $info);
                restore_current_blog();
            },
            $screen,
            $context,
            $info->priority()
        );
    }

    /**
     * @param \WP_Post $post
     */
    private function onPostSave(\WP_Post $post)
    {
        // This check allows to edit post object inside BoxAction::save() without recursion.
        if ($this->saving === 'post') {
            return;
        }

        if (wp_is_post_autosave($post) || wp_is_post_revision($post)) {
            return;
        }

        $entity = new Entity($post);
        $this->saving = 'post';
        $this->prepareTarget($entity, Metabox::SAVE);
        do_action(self::ACTION_SAVE_METABOXES, $entity);
        $this->saveMetaBoxes();
        do_action(self::ACTION_SAVED_METABOXES, $entity);
        $this->releaseTarget();
        $this->saving = '';
    }

    /**
     * @return void
     */
    private function saveMetaBoxes()
    {
        $globalsCleared = $this->globalsManipulator->clear();

        foreach ($this->boxes as $boxId => $box) {
            if (!$this->isBoxEnabled($box, Metabox::SAVE)) {
                continue;
            }

            $siteId = $box->siteId();
            if (!siteExists($siteId)) {
                $title = $box->createInfo('save', $this->entity)->title();
                // translators: 1 is the site ID, 2 the metabox title
                $message = __(
                    'Site %1$d was not accessible when attempting to save metabox "%2$s".',
                    'multilingualpress'
                );
                $notice = AdminNotice::error(sprintf($message, $siteId, $title))
                    ->withTitle(__('Metabox Not Saved', 'multilingualpress'));

                $this->notices->add($notice);

                continue;
            }

            $this->saveMetaBox($box, $boxId);
        }

        $globalsCleared and $this->globalsManipulator->restore();
    }

    /**
     * @param Metabox|PostMetabox|TermMetabox $box
     * @param string $boxId
     */
    private function saveMetaBox(Metabox $box, string $boxId)
    {
        $siteId = $box->siteId();
        $isPost = $this->entity->is(\WP_Post::class);
        $object = $this->entity->expose();

        /** @var \WP_Post|\WP_Term $object */
        if (!$object) {
            return;
        }

        $objectId = $isPost ? $object->ID : $object->term_id;
        $authClass = $isPost ? PostAuth::class : TermAuth::class;

        /** @var PostAuth|TermAuth $auth */
        $nonce = new WpNonce("{$boxId}-{$objectId}");
        $auth = new $authClass($object, $nonce->withSite($siteId));

        if (!$auth->authorized($this->request)) {
            do_action(self::ACTION_UNAUTHORIZED_METABOX_SAVE, $box, $object, $nonce);

            return;
        }

        $object = $isPost
            ? get_post($object->ID)
            : get_term_by('term_taxonomy_id', $object->term_taxonomy_id);

        switch_to_blog($siteId);
        do_action(self::ACTION_SAVED_METABOX, $box, $this->entity, $this->request, $this->notices);
        $action = $isPost ? $box->actionForPost($object) : $box->actionForTerm($object);
        $action->save($this->request, $this->notices);
        do_action(self::ACTION_SAVED_METABOX, $box, $this->entity, $this->request, $this->notices);
        restore_current_blog();
    }

    /**
     * Clean up state.
     */
    private function releaseTarget()
    {
        $this->entity = null;
        $this->registeringFor = '';
        $this->boxes = [];
        $this->locked = false;
    }
}
