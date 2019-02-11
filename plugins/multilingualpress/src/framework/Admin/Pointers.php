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

namespace Inpsyde\MultilingualPress\Framework\Admin;

use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Http\Request;

/**
 * WordPress Internal Pointers manager.
 */
class Pointers
{
    const ACTION_AFTER_POINTERS_CREATED = 'multilingualpress.after_pointers_created';

    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var PointersRepository
     */
    private $repository;

    /**
     * @param Request $request
     * @param PointersRepository $repository
     * @param AssetManager $assetManager
     */
    public function __construct(
        Request $request,
        PointersRepository $repository,
        AssetManager $assetManager
    ) {

        $this->request = $request;
        $this->repository = $repository;
        $this->assetManager = $assetManager;
    }

    /**
     * @return void
     */
    public function createPointers()
    {
        if (!current_user_can('create_sites')) {
            return;
        }

        $screen = get_current_screen();

        list($pointers, $ajaxAction) = $this->repository->pointersForScreen($screen->id);

        if (!$pointers) {
            return;
        }

        $dismissedPointers = explode(
            ',',
            (string)get_user_meta(get_current_user_id(), 'dismissed_mlp_pointers', true)
        );
        if ($this->currentPointersDismissed(array_keys($pointers), $dismissedPointers)) {
            return;
        }

        $this->enqueuePointers($pointers, $ajaxAction);

        do_action(self::ACTION_AFTER_POINTERS_CREATED, $screen);
    }

    /**
     * @param array $pointers
     * @param string $ajaxAction
     * @return void
     */
    // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    public function enqueuePointers(array $pointers, string $ajaxAction)
    {
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');

        $this->assetManager->enqueueScriptWithData(
            'pointers',
            'MultilingualPressPointersData',
            [
                'pointers' => $pointers,
                'dismissButtonText' => esc_html__('Dismiss guide', 'multilingualpress'),
                'OkButtonText' => esc_html__('OK', 'multilingualpress'),
                'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
                'ajaxAction' => $ajaxAction,
            ]
        );
    }

    /**
     * @return void
     */
    public function dismiss()
    {
        $pointer = $this->request->bodyValue('pointer', INPUT_POST, FILTER_SANITIZE_STRING);

        if ($pointer) {
            $dismissedPointers = explode(
                ',',
                (string)get_user_meta(get_current_user_id(), 'dismissed_mlp_pointers', true)
            );
            $dismissedPointers[] = $pointer;
            $dismissed = implode(',', $dismissedPointers);

            update_user_meta(get_current_user_id(), 'dismissed_mlp_pointers', $dismissed);
        }
    }

    /**
     * @param array $pointers
     * @param array $dismissedPointers
     * @return bool
     */
    private function currentPointersDismissed(array $pointers, array $dismissedPointers): bool
    {
        foreach ($pointers as $pointer) {
            if (in_array($pointer, $dismissedPointers, true)) {
                return true;
            }
        }

        return false;
    }
}
