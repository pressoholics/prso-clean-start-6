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

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;

/**
 * Deactivates specific plugin.
 */
class PluginDeactivator
{
    /**
     * @var \WP_Error
     */
    private $wpError;

    /**
     * @var string
     */
    private $pluginBaseName;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @param string $pluginBaseName
     * @param string $pluginName
     * @param \WP_Error $error
     */
    public function __construct(
        string $pluginBaseName,
        string $pluginName,
        \WP_Error $error
    ) {

        $this->pluginBaseName = $pluginBaseName;
        $this->pluginName = $pluginName;
        $this->wpError = $error;
    }

    /**
     * Deactivates the plugin, and renders an according admin notice.
     */
    public function deactivatePlugin()
    {
        deactivate_plugins($this->pluginBaseName);

        // Suppress the "Plugin activated" notice.
        unset($_GET['activate']); // phpcs:ignore

        $this->renderAdminNotice();
    }

    /**
     * Deactivate the plugin and stop the script execution
     *
     * @uses wp_die
     */
    public function deactivatePluginAndDie()
    {
        deactivate_plugins($this->pluginBaseName);

        $this->wpError->add_data(['title' => $this->messageTitle()]);

        //phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
        wp_die($this->wpError, '', ['back_link' => true]);
    }

    /**
     * Renders an admin notice informing about the plugin deactivation,
     * including potential error messages.
     */
    private function renderAdminNotice()
    {
        $title = $this->messageTitle();

        if (!empty($this->wpError->errors)) {
            AdminNotice::error(...$this->wpError->get_error_messages())
                ->withTitle($title)
                ->renderNow();

            return;
        }

        AdminNotice::info($title)->renderNow();
    }

    /**
     * @return string
     */
    private function messageTitle(): string
    {
        // translators: %s: plugin name.
        $message = esc_html__(
            'The plugin %s has been deactivated.',
            'multilingualpress'
        );

        return sprintf($message, $this->pluginName);
    }
}
