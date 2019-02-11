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
			));

		}

	}

	public function render_block( $block ) {

		// convert name ("acf/testimonial") into path friendly slug ("testimonial")
		$slug = str_replace('acf/', '', $block['name']);

		// include a template part from within the "template-parts/block" folder
		if( file_exists(STYLESHEETPATH . "/template_parts/block/{$slug}.php") ) {
			include( STYLESHEETPATH . "/template_parts/block/{$slug}.php" );
		}

	}


}
new Prso_Acf_Block_Example();
