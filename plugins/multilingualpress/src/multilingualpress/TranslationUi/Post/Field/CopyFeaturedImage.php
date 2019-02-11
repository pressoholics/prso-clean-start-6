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

namespace Inpsyde\MultilingualPress\TranslationUi\Post\Field;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post\RelationshipContext;
use Inpsyde\MultilingualPress\TranslationUi\Post\MetaboxFields;

class CopyFeaturedImage
{

    /**
     * @param $value
     * @return string
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public static function sanitize($value): string
    {
        // phpcs:enable

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '';
    }

    /**
     * @param MetaboxFieldsHelper $helper
     * @param RelationshipContext $context
     */
    public function __invoke(MetaboxFieldsHelper $helper, RelationshipContext $context)
    {
        $id = $helper->fieldId(MetaboxFields::FIELD_COPY_FEATURED);
        $name = $helper->fieldName(MetaboxFields::FIELD_COPY_FEATURED);

        ?>
        <tr>
            <th scope="row">
                <label>
                    <?php esc_html_e('Copy featured image', 'multilingualpress') ?>
                </label>
            </th>
            <td>
                <label for="<?= esc_attr($id) ?>">
                    <input
                        type="checkbox"
                        name="<?= esc_attr($name) ?>"
                        value="1"
                        id="<?= esc_attr($id) ?>">
                    <?php
                    esc_html_e(
                        'Overwrites featured image on translated post with the featured image of source post.',
                        'multilingualpress'
                    );
                    ?>
                </label>
            </td>
        </tr>
        <?php
    }
}
