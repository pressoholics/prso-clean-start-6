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
 * Onboarding messages.
 */
class Messages
{

    /**
     * @var State
     */
    private $onboardingState;

    /**
     * @param State $onboardingState
     */
    public function __construct(State $onboardingState)
    {
        $this->onboardingState = $onboardingState;
    }

    /**
     * Creates onboarding message content.
     * @param \WP_Screen $screen
     * @param string $onboardingState
     * @param array $siteRelations
     * @return array
     */
    // phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
    public function onboardingMessageContent(
        \WP_Screen $screen,
        string $onboardingState,
        array $siteRelations
    ): array {
        // phpcs:enable
        $title = '';
        $message = '';

        if ($onboardingState === $this->onboardingState::STATE_SITES) {
            $title = esc_html__('Welcome to MultilingualPress', 'multilingualpress');
            $buttonText = esc_html__('Create a new Site', 'multilingualpress');
            $buttonLink = esc_url(network_admin_url('site-new.php'));
            if (count(get_sites()) > 1) {
                $buttonText = esc_html__('Connect Sites', 'multilingualpress');
                $buttonLink = add_query_arg(
                    'id',
                    get_network()->site_id,
                    network_admin_url('sites.php?page=multilingualpress-site-settings')
                );
            }

            $message = $this->createMessage(
                esc_html__(
                    'The first step is to set up site relationships, to do so please click the following button:',
                    'multilingualpress'
                ),
                $buttonText,
                $buttonLink
            );

            if ($screen->id === 'site-new-network' && count($siteRelations) === 0
                || $screen->id === 'sites_page_multilingualpress-site-settings-network'
                && count($siteRelations) === 0
            ) {
                $buttonText = '';
                $buttonLink = '';

                $message = $this->createMessage(
                    esc_html__(
                        'Here you can start setting up site relationships.',
                        'multilingualpress'
                    ),
                    $buttonText,
                    $buttonLink
                );
            }
        }

        if ($onboardingState === $this->onboardingState::STATE_SETTINGS) {
            $title = esc_html__('Configure MultilingualPress Settings', 'multilingualpress');
            $buttonText = esc_html__('Go to MultilingualPress Settings', 'multilingualpress');
            $buttonLink = esc_url(network_admin_url('admin.php?page=multilingualpress'));

            $message = $this->createMessage(
                esc_html__(
                    'Next step is to configure MultilingualPress settings. You can enable Modules to add more functionality and select the post types and taxonomies that you want to translate.',
                    'multilingualpress'
                ),
                $buttonText,
                $buttonLink
            );
        }

        if ($onboardingState === $this->onboardingState::STATE_POST) {
            $title = esc_html__('Connect WordPress Content', 'multilingualpress');
            $buttonText = '';
            $buttonLink = '';

            $textMessage = esc_html__(
                'You made it! Finally, you can translate and connect content in the edit panel. Now you can go to ',
                'multilingualpress'
            );
            $textMessage .= sprintf('<a href="%s">', esc_url(admin_url('edit.php')));
            $textMessage .= esc_html__('Posts', 'multilingualpress');
            $textMessage .= '</a>';
            $textMessage .= esc_html__(' or ', 'multilingualpress');
            $textMessage .= sprintf('<a href="%s">', esc_url(admin_url('edit.php?post_type=page')));
            $textMessage .= esc_html__('Pages', 'multilingualpress');
            $textMessage .= '</a>';
            $textMessage .= esc_html__(' to connect them.', 'multilingualpress');

            $message = $this->createMessage(
                $textMessage,
                $buttonText,
                $buttonLink
            );

            if ($screen->id === 'toplevel_page_multilingualpress-network') {
                $title = esc_html__('Configure MultilingualPress Settings', 'multilingualpress');
                $buttonText = '';
                $buttonLink = '';

                $textMessage = esc_html__(
                    'After you enabled Modules and select post types and taxonomies that you want to translate and clicked on "Save Changes", you can go to ',
                    'multilingualpress'
                );
                $textMessage .= sprintf('<a href="%s">', esc_url(admin_url('edit.php')));
                $textMessage .= esc_html__('Posts', 'multilingualpress');
                $textMessage .= '</a>';
                $textMessage .= esc_html__(' or ', 'multilingualpress');
                $textMessage .= sprintf('<a href="%s">', esc_url(admin_url('edit.php?post_type=page')));
                $textMessage .= esc_html__('Pages', 'multilingualpress');
                $textMessage .= '</a>';
                $textMessage .= esc_html__(' to connect them.', 'multilingualpress');

                $message = $this->createMessage(
                    $textMessage,
                    $buttonText,
                    $buttonLink
                );
            }
        }

        if ($onboardingState === $this->onboardingState::STATE_END) {
            $title = esc_html__('Connect WordPress Content', 'multilingualpress');
            $buttonText = '';
            $buttonLink = '';

            $textMessage = esc_html__(
                'You made it! Finally, you can translate and connect content in the edit panel. Now you can go to ',
                'multilingualpress'
            );
            $textMessage .= sprintf('<a href="%s">', esc_url(admin_url('edit.php')));
            $textMessage .= esc_html__('Posts', 'multilingualpress');
            $textMessage .= '</a>';
            $textMessage .= esc_html__(' or ', 'multilingualpress');
            $textMessage .= sprintf('<a href="%s">', esc_url(admin_url('edit.php?post_type=page')));
            $textMessage .= esc_html__('Pages', 'multilingualpress');
            $textMessage .= '</a>';
            $textMessage .= esc_html__(' to connect them.', 'multilingualpress');

            $message = $this->createMessage(
                $textMessage,
                $buttonText,
                $buttonLink
            );

            if ($screen->id === 'edit-post' || $screen->id === 'edit-page') {
                $title = esc_html__('Connect WordPress Content', 'multilingualpress');
                $buttonText = esc_html__('Finish Guide', 'multilingualpress');
                $buttonLink = add_query_arg(
                    'onboarding_dismissed',
                    true,
                    esc_url(admin_url('edit.php'))
                );

                $textMessage = esc_html__(
                    'You made it! Finally, you can translate and connect content in the edit panel. If you need further information ',
                    'multilingualpress'
                );
                // phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
                $textMessage .= '<a href="https://multilingualpress.org/docs-category/multilingualpress-3/" target="_blank">';
                // phpcs:enable
                $textMessage .= esc_html__('check out our documentation.', 'multilingualpress');
                $textMessage .= '</a>';

                $message = $this->createMessage(
                    $textMessage,
                    $buttonText,
                    $buttonLink
                );
            }
        }

        return [
            'title' => $title,
            'message' => $message,
        ];
    }

    /**
     * @param string $message
     * @param string $buttonText
     * @param string $buttonLink
     * @return string
     */
    private function createMessage(
        string $message,
        string $buttonText,
        string $buttonLink
    ): string {

        $output = '<div class="onboarding-message">';
        $output .= $message;
        $output .= '</div>';

        if ($buttonText === '' || $buttonLink === '') {
            return $output;
        }

        $output .= '<div class="buttons">';
        /* translators: 1: button link, 2: button text */
        $output .= sprintf(__(
            '<a class="button button-primary" href="%1$s">%2$s</a>',
            'multilingualpress'
        ), $buttonLink, $buttonText);
        $output .= '</div>';

        return $output;
    }
}
