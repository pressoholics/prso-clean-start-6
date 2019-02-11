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

namespace Inpsyde\MultilingualPress\Core;

class TaxonomyRepository
{
    const DEFAULT_SUPPORTED_TAXONOMIES = [
        'category',
        'post_tag',
    ];

    const FIELD_ACTIVE = 'active';

    const FIELD_SKIN = 'ui';

    const OPTION = 'multilingualpress_taxonomies';

    /**
     * @var \WP_Taxonomy[]
     */
    private $allTaxonomies;

    /**
     * Returns all taxonomies that MultilingualPress is able to support.
     *
     * @return \WP_Taxonomy[]
     */
    public function allAvailableTaxonomies(): array
    {
        if (is_array($this->allTaxonomies)) {
            return $this->allTaxonomies;
        }

        $allTaxonomies = get_taxonomies(['show_ui' => true], 'objects');
        if (!get_option('link_manager_enabled')) {
            $allTaxonomies = array_filter(
                $allTaxonomies,
                function (\WP_Taxonomy $taxonomy): bool {
                    return $taxonomy->name !== 'link_category';
                }
            );
        }

        $this->allTaxonomies = $allTaxonomies;
        if ($this->allTaxonomies) {
            uasort(
                $this->allTaxonomies,
                function (\WP_Taxonomy $left, \WP_Taxonomy $right): int {
                    return strcasecmp($left->labels->name, $right->labels->name);
                }
            );
        }

        return $this->allTaxonomies;
    }

    /**
     * Returns the UI ID of the taxonomy with the given slug.
     *
     * @param string $slug
     * @return string
     */
    public function taxonomySkinId(string $slug): string
    {
        list(, $value) = $this->settingFor(
            $slug,
            TaxonomyRepository::FIELD_SKIN,
            false
        );

        return (string)$value;
    }

    /**
     * Returns all taxonomies supported by MultilingualPress.
     *
     * @return string[]
     */
    public function supportedTaxonomies(): array
    {
        list($found, $settings) = $this->allSettings();

        if (!$found) {
            return TaxonomyRepository::DEFAULT_SUPPORTED_TAXONOMIES;
        }

        $supported = array_filter(
            $settings,
            function (array $data): bool {
                return $data[TaxonomyRepository::FIELD_ACTIVE] ?? false;
            }
        );

        return array_keys($supported);
    }

    /**
     * Checks if the taxonomy with the given slug is active.
     *
     * @param string $slug
     * @return bool
     */
    public function isTaxonomyActive(string $slug): bool
    {
        list($found, $value) = $this->settingFor(
            $slug,
            TaxonomyRepository::FIELD_ACTIVE,
            false
        );

        if (!$found) {
            return in_array(
                $slug,
                TaxonomyRepository::DEFAULT_SUPPORTED_TAXONOMIES,
                true
            );
        }

        return (bool)$value;
    }

    /**
     * Sets taxonomy support according to the given settings.
     *
     * @param array $taxonomies
     * @return bool
     */
    public function supportTaxonomies(array $taxonomies): bool
    {
        return (bool)update_network_option(
            0,
            TaxonomyRepository::OPTION,
            $taxonomies
        );
    }

    /**
     * Removes the support for all taxonomies.
     *
     * @return bool
     */
    public function removeSupportForAllTaxonomies(): bool
    {
        return $this->supportTaxonomies([]);
    }

    /**
     * Returns a two-items array, where the first is a boolean indicating if
     * settings are found in database, the second is actual settings array.
     * Help disguising on-purpose empty array in db from a no-result.
     *
     * @return array
     */
    private function allSettings(): array
    {
        $options = get_network_option(0, TaxonomyRepository::OPTION);
        if (!is_array($options)) {
            return [false, []];
        }

        return [true, $options];
    }

    /**
     * @param string $slug
     * @param string $field
     * @param mixed $default
     * @return array|null
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    private function settingFor(
        string $slug,
        string $field,
        $default = null
    ) {

        // phpcs:enable

        list($found, $settings) = $this->allSettings();

        if (!$found) {
            return [false, $default];
        }

        return [true, $settings[$slug][$field] ?? $default];
    }
}
