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

/**
 * Model for a custom column in the Sites list table in the Network Admin.
 */
class SitesListTableColumn
{
    /**
     * @var string
     */
    private $columnName;

    /**
     * @var string
     */
    private $columnLabel;

    /**
     * @var callable
     */
    private $renderCallback;

    /**
     * @param string $columnName
     * @param string $columnLabel
     * @param callable $renderCallback
     */
    public function __construct(
        string $columnName,
        string $columnLabel,
        callable $renderCallback
    ) {

        $this->columnName = $columnName;
        $this->columnLabel = $columnLabel;
        $this->renderCallback = $renderCallback;
    }

    /**
     * Registers the column methods by using the appropriate WordPress hooks.
     */
    public function register()
    {
        add_filter('wpmu_blogs_columns', function (array $columns): array {
            return array_merge($columns, [$this->columnName => $this->columnLabel]);
        });

        add_action(
            'manage_sites_custom_column',
            function ($column, $siteId) {
                $this->renderContent((string)$column, (int)$siteId);
            },
            10,
            2
        );
    }

    /**
     * Renders the column content.
     *
     * @param string $column
     * @param int $siteId
     *
     * @wp-hook manage_sites_custom_column
     */
    public function renderContent(string $column, int $siteId)
    {
        if ($column === $this->columnName) {
            echo wp_kses_post(($this->renderCallback)($column, $siteId));
        }
    }
}
