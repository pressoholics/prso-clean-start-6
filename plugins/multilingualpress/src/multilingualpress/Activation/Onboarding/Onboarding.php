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

namespace Inpsyde\MultilingualPress\Activation\Onboarding;

use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Http\Request;

/**
 * Onboarding messages manager.
 */
class Onboarding
{
    const ONBOARDING_DISMISSED = 'onboarding_dismissed';

    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * @var Messages
     */
    private $onboardingMessages;

    /**
     * @var State
     */
    private $onboardingState;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var SiteRelations
     */
    private $siteRelations;

    /**
     * @param AssetManager $assetManager
     * @param SiteRelations $siteRelations
     * @param Request $request
     * @param State $onboardingState
     * @param Messages $onboardingMessages
     */
    public function __construct(
        AssetManager $assetManager,
        SiteRelations $siteRelations,
        Request $request,
        State $onboardingState,
        Messages $onboardingMessages
    ) {

        $this->assetManager = $assetManager;
        $this->siteRelations = $siteRelations;
        $this->request = $request;
        $this->onboardingState = $onboardingState;
        $this->onboardingMessages = $onboardingMessages;
    }

    /**
     * Setup onboarding state and messages.
     * @return void
     */
    public function init()
    {
        add_action('current_screen', function () {

            $screen = get_current_screen();
            if (!$this->canDisplayMessage($screen)) {
                return;
            }

            $this->assetManager->enqueueScript('onboarding');
            $this->assetManager->enqueueStyle('onboarding');

            $onboardingState = get_site_option('onboarding_state', 'sites');
            $siteRelations = $this->siteRelations->allRelations();

            $onboardingState = $this->onboardingState->updateState($screen, $onboardingState, $siteRelations);

            $messageContent = $this->onboardingMessages->onboardingMessageContent(
                $screen,
                $onboardingState,
                $siteRelations
            );

            AdminNotice::multilingualpress(wp_kses_post($messageContent['message']))
                ->withTitle($messageContent['title'])
                ->makeDismissible()
                ->inAllScreens()
                ->render();
        });
    }

    /**
     * @return void
     */
    public function handleDismissOnboardingMessage()
    {
        $onboardingDismissed = $this->request->bodyValue(
            self::ONBOARDING_DISMISSED,
            INPUT_GET,
            FILTER_SANITIZE_STRING
        );

        if ($onboardingDismissed === '1' && current_user_can('create_sites')) {
            update_site_option(self::ONBOARDING_DISMISSED, true);
        }
    }

    /**
     * @return void
     */
    public static function handleAjaxDismissOnboardingMessage()
    {
        if (!wp_doing_ajax()) {
            return;
        }

        if (!doing_action('wp_ajax_onboarding_plugin')) {
            wp_send_json_error('Invalid action.');
        }

        if (update_site_option(self::ONBOARDING_DISMISSED, true)) {
            wp_send_json_success();
        }

        wp_send_json_error('Not updated.');
    }

    /**
     * @param $screen
     * @return bool
     */
    private function canDisplayMessage(\WP_Screen $screen): bool
    {
        if (!$screen) {
            return false;
        }

        if (!current_user_can('create_sites')) {
            return false;
        }

        if ((bool)get_site_option(self::ONBOARDING_DISMISSED) === true) {
            return false;
        }

        return true;
    }
}
