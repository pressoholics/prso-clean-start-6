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

namespace Inpsyde\MultilingualPress\Framework\Asset;

/**
 * Managing instance for all asset-specific tasks.
 */
class AssetManager
{
    private static $dataAddedFor = [];

    /**
     * @var Script[]
     */
    private $scripts = [];

    /**
     * @var Style[]
     */
    private $styles = [];

    /**
     * Register the given script.
     *
     * @param Script $script
     * @return static
     */
    public function registerScript(Script $script): AssetManager
    {
        $this->scripts[$script->handle()] = $script;

        return $this;
    }

    /**
     * Register the given style.
     *
     * @param Style $style
     * @return AssetManager
     */
    public function registerStyle(Style $style): AssetManager
    {
        $this->styles[$style->handle()] = $style;

        return $this;
    }

    /**
     * Enqueues the script with the given handle.
     *
     * @param string $handle
     * @param bool $inFooter
     * @return bool
     */
    public function enqueueScript(string $handle, bool $inFooter = true): bool
    {
        if (empty($this->scripts[$handle])) {
            return false;
        }

        $script = $this->scripts[$handle];

        if (did_action('init') && wp_script_is($handle)) {
            $this->handleScriptData($script);

            return true;
        }

        $url = MaybeMinifiedAssetUrl::fromLocation($script->location());
        $version = $script->version() ?: $url->version();

        $this->enqueue(
            function () use ($handle, $script, $inFooter, $url, $version) {

                wp_enqueue_script(
                    $handle,
                    (string)$url,
                    $script->dependencies(),
                    $version ?: null,
                    $inFooter
                );

                $this->handleScriptData($script);
            }
        );

        return true;
    }

    /**
     * Enqueues the script with the given handle.
     *
     * @param string $handle
     * @param string $objectName
     * @param array $data
     * @param bool $inFooter
     * @return bool
     */
    public function enqueueScriptWithData(
        string $handle,
        string $objectName,
        array $data,
        bool $inFooter = true
    ): bool {

        if (empty($this->scripts[$handle])) {
            return false;
        }

        if (!$this->addScriptData($handle, $objectName, $data)) {
            return false;
        }

        return $this->enqueueScript($handle, $inFooter);
    }

    /**
     * Enqueues the style with the given handle.
     *
     * @param string $handle
     * @return bool
     */
    public function enqueueStyle(string $handle): bool
    {
        if (empty($this->styles[$handle])) {
            return false;
        }

        if (did_action('init') && wp_style_is($handle)) {
            return true;
        }

        $style = $this->styles[$handle];
        $url = MaybeMinifiedAssetUrl::fromLocation($style->location());
        $version = $style->version() ?: $url->version();

        $this->enqueue(
            function () use ($handle, $url, $version) {

                $style = $this->styles[$handle];

                wp_enqueue_style(
                    $handle,
                    (string)$url,
                    $style->dependencies(),
                    $version ?: null,
                    $style->media()
                );
            }
        );

        return true;
    }

    /**
     * Adds the given data to the given script, and handles it in case the script
     * has been enqueued already.
     *
     * @param string $handle
     * @param string $objectName
     * @param array $data
     * @return Script|null
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    public function addScriptData(
        string $handle,
        string $objectName,
        array $data
    ) {

        // phpcs:enable
        if (!array_key_exists($handle, $this->scripts)) {
            return null;
        }

        $script = $this->scripts[$handle];
        $script->addData($objectName, $data);

        if (did_action('init') && wp_script_is($handle)) {
            $this->handleScriptData($script);
        }

        return $script;
    }

    /**
     * Handles potential data that has been added to the script after it was
     * enqueued, and then clears the data.
     *
     * @param Script $script
     */
    private function handleScriptData(Script $script)
    {
        $handle = $script->handle();
        if (in_array($handle, self::$dataAddedFor, true)) {
            return;
        }

        self::$dataAddedFor[] = $handle;
        $data = $script->data();

        array_walk(
            $data,
            function (array $data, string $objectName) use ($handle) {
                wp_localize_script($handle, $objectName, $data);
            }
        );
    }

    /**
     * Either executes the given callback or hooks it to the appropriate enqueue
     * action, depending on the context.
     *
     * @param callable $callback
     */
    private function enqueue(callable $callback)
    {
        $enqueueAction = $this->enqueueAction();

        if (did_action($enqueueAction)) {
            $callback();

            return;
        }

        add_action($enqueueAction, $callback);
    }

    /**
     * Returns the appropriate action for enqueueing assets.
     *
     * @return string
     */
    private function enqueueAction(): string
    {
        if (0 === strpos(ltrim(add_query_arg([]), '/'), 'wp-login.php')) {
            return empty($GLOBALS['interim_login'])
                ? 'login_enqueue_scripts'
                : '';
        }

        if (is_admin()) {
            return 'admin_enqueue_scripts';
        }

        if (is_customize_preview()) {
            return 'customize_controls_enqueue_scripts';
        }

        return 'wp_enqueue_scripts';
    }
}
