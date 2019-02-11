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

/**
 * A Walker_Category_Checklist to use radio instead of checkboxes when necessary, and to replace
 * the input name attribute and the category id attribute.
 *
 * @package Inpsyde\MultilingualPress\TranslationUi\Post\Field
 */
class TaxonomyWalker extends \Walker_Category_Checklist
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @param $output
     * @param $category
     * @param int $depth
     * @param array $args
     * @param int $id
     *
     * phpcs:disable
     */
    public function start_el(&$output, $category, $depth = 0, $args = [], $id = 0)
    {
        // phpcs:enable

        $taxonomy = $args['taxonomy'];
        $nameOrig = $taxonomy === 'category' ? 'post_category' : "tax_input[{$taxonomy}]";
        $replacements = [
            " name=\"{$nameOrig}[]\" " => " name=\"{$this->name}[]\" ",
            "in-category-" => "mlp-in-category-",
        ];
        $this->type === 'radio' and $replacements[' type="checkbox" '] = ' type="radio" ';

        $temp = '';
        parent::start_el($temp, $category, $depth, $args, $id);

        $output .= strtr($temp, $replacements);
    }
}
