<?php
/**
 * Class to handle any PHP required for code-block Gutenberg block
 * User: ben
 * Date: 2018-11-25
 * Time: 1:34 PM
 */

class Jam3_Blocks_Code_Block {

	public static $block_slug = 'code-block';

	function __construct() {

		//Enqueue Gutenberg editor assets
		add_action(
			'enqueue_block_editor_assets',
			array(
				$this,
				'block_editor_assets',
			),
			20
		);

	}

	/**
	 * render_json
	 *
	 * This is the REST API callback to return the json data array for the rest
	 * response, unique to this block and it's data
	 *
	 * @param array $block_data
	 *
	 * @return array $json_output
	 * @access public static
	 * @author Ben Moody
	 */
	public static function render_json( $block_data = array() ) {

		//vars
		$json_output = array();

		if ( method_exists( 'Jam3_Rest_Api', 'hypen_to_camel_case' ) ) {
			$json_output = array(
				'blockName' => Jam3_Rest_Api::hypen_to_camel_case( self::$block_slug, true ),
				//Set the correct type for this block
			);
		}

		if ( ! isset( $block_data['attrs'] ) ) {

			//Missing data
			return new WP_Error(
				'render_json()',
				'Missing block attrs',
				$block_data
			);

		}

		$json_output['data'] = array(
			'codeBlock'    => $block_data['attrs']['content'],
			'codeLanguage' => esc_attr( $block_data['attrs']['codeLanguage'] ),
			'caption'      => esc_html( $block_data['attrs']['caption'] ),
		);

		return $json_output;
	}

	/**
	 * block_editor_assets
	 *
	 * @CALLED BY /ACTION 'enqueue_block_editor_assets'
	 *
	 * Enqueue assets for codemirror code editor JS IDE
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function block_editor_assets() {

		//vars
		$current_lib_ver   = '5.33.0';
		$mode_requirements = array(
			'xml',
			'javascript',
			'css',
			'htmlmixed',
			'clike',
			'python',
		);

		$codemirror_vendor_url = JAM3_BLOCKS_PLUGIN_BASE_URL . '/src/code-block/vendors/codemirror';

		wp_enqueue_script(
			'codemirror', // Handle.
			$codemirror_vendor_url . '/lib/codemirror.js', // Block.build.js: We register the block here. Built with Webpack.
			array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
			), // Dependencies, defined above.
			$current_lib_ver,
			true // Enqueue the script in the footer.
		);

		//Enqueue codemirror mode requirements
		foreach ( $mode_requirements as $requirement ) {

			//Enqueue code language mode
			wp_enqueue_script(
				"codemirror-{$requirement}",
				esc_url( $codemirror_vendor_url . "/mode/{$requirement}/{$requirement}.js" ),
				array( 'codemirror' ),
				$current_lib_ver,
				true
			);
		}

		//Enqueue codemirror main stylesheet
		wp_enqueue_style(
			'codemirror',
			$codemirror_vendor_url . '/lib/codemirror.css',
			null,
			$current_lib_ver,
			'screen'
		);

	}

}

new Jam3_Blocks_Code_Block();
