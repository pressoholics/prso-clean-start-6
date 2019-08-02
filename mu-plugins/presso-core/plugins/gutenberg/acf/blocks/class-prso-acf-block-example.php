<?php
/**
 * ACF Gutenberg Block Render Example
 */

class Prso_Acf_Block_Example {

	protected static $block_name = 'example';

	public function __construct() {

		add_action(
			'acf/init',
			array(
				$this,
				'register_block'
			)
		);

	}

	public function register_block() {

		//vars
		$block_title = 'Example';
		$block_description = 'Example block';
		$block_category = 'formatting';
		$block_icon = 'admin-home';
		$block_keywords = array(
			'example'
		);

		// check function exists
		if( function_exists('acf_register_block') ) {

			// register block
			acf_register_block(array(
				'name'				=> self::$block_name,
				'title'				=> $block_title,
				'description'		=> $block_description,
				'render_callback'	=> array( $this, 'render_block' ),
				'category'			=> $block_category,
				'icon'				=> $block_icon,
				'keywords'			=> $block_keywords,
				'mode'              => 'auto', //auto, preview, edit
				'align'             => '', //left, center, right, wide, full
				'supports'          => array(
					// disable alignment toolbar - 'left', 'right', 'full'
					'align'     => false,
					// disable preview/edit toggle
					'mode'      => false,
					// This property allows the block to be added multiple times. Defaults to true.
					'multiple'  => true,
				),
			));

		}

	}

	public function render_block( $block ) {

		add_filter( 'jetpack_lazy_images_skip_image_with_atttributes', '__return_true' );

		// convert name ("acf/testimonial") into path friendly slug ("testimonial")
		$slug = str_replace('acf/', '', $block['name']);

		// include a template part from within the "template-parts/block" folder
		if( file_exists(STYLESHEETPATH . "/template_parts/block/{$slug}.php") ) {
			include( STYLESHEETPATH . "/template_parts/block/{$slug}.php" );
		}
		
		add_filter( 'jetpack_lazy_images_skip_image_with_atttributes', '__return_false' );

	}


}
new Prso_Acf_Block_Example();
