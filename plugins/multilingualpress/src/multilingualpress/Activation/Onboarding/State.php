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

/**
 * Onboarding state manager.
 */
class State
{
    const STATE_SITES = 'sites';
    const STATE_SETTINGS = 'settings';
    const STATE_POST = 'post';
    const STATE_END = 'end';

    /**
     * Update onboarding state based on site relations and screen.
     * @param \WP_Screen $screen
     * @param string $onboardingState
     * @param array $siteRelations
     * @return string
     */
    public function updateState(
        \WP_Screen $screen,
        string $onboardingState,
        array $siteRelations
    ): string {

        if (count($siteRelations) > 0 && $onboardingState === self::STATE_SITES) {
            $onboardingState = self::STATE_SETTINGS;
            update_site_option('onboarding_state', $onboardingState);
        }

        if ($screen->id === 'site-new-network' && count($siteRelations) > 0
            || $screen->id === 'sites_page_multilingualpress-site-settings-network'
            && count($siteRelations) > 0
        ) {
            if ($onboardingState === self::STATE_SITES) {
                $onboardingState = self::STATE_SETTINGS;
                update_site_option('onboarding_state', $onboardingState);
            }
        }
        if ($screen->id === 'toplevel_page_multilingualpress-network') {
            if ($onboardingState === self::STATE_SETTINGS) {
                $onboardingState = self::STATE_POST;
                update_site_option('onboarding_state', $onboardingState);
            }
        }
        if ($screen->id === 'edit-post' || $screen->id === 'edit-page') {
            if ($onboardingState === self::STATE_POST) {
                $onboardingState = self::STATE_END;
                update_site_option('onboarding_state', $onboardingState);
            }
        }

        return $onboardingState;
    }
}
