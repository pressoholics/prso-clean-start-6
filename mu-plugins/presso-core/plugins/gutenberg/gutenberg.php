<?php
class Prso_Gutenberg {

	public function __construct() {

		//$this->load_blocks();

		//$this->load_acf_blocks();

		//$this->load_block_templates();

		//Add custom block category
		//add_filter( 'block_categories', array( $this, 'blocks_catergories' ), 10, 1 );

		//Set project allowed block types
		add_filter( 'allowed_block_types', array( $this, 'blocks_allowed_block_types' ) );

	}

	public function load_blocks() {

		//Enqueue block editor scripts
		add_action( 'enqueue_block_editor_assets', array($this, 'enqueue_block_editor_scripts') );

	}

	public function enqueue_block_editor_scripts() {

		//Vars
		$dist_path = dirname( __FILE__ ) . '/blocks/dist';
		$dist_url = plugin_dir_url( __FILE__ ) . '/blocks/dist';

		// Scripts.
		wp_enqueue_script(
			'prso-guten-blocks-js', // Handle.
			$dist_url . '/blocks.build.js', // Block.build.js: We register the block here. Built with Webpack.
			array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
			), // Dependencies, defined above.
			filemtime( $dist_path . '/blocks.build.js' ), // Version: filemtime — Gets file modification time.
			true // Enqueue the script in the footer.
		);
		
		//Editor Styles
		wp_enqueue_style(
			'prso-guten-styles',
			$dist_url . '/blocks.editor.build.css',
			false,
			'1.0',
			'all'
		);

	}

	public function load_acf_blocks() {

		//Vars
		$cpt_path = dirname( __FILE__ ) . '/acf/blocks';

		//Include files
		prso_include_all_files( $cpt_path );

	}

	public function load_block_templates() {

		//Vars
		$cpt_path = dirname( __FILE__ ) . '/block-templates';

		//Include files
		prso_include_all_files( $cpt_path );

	}

	/**
	 * blocks_catergories
	 *
	 * @CALLED BY FILTER 'block_categories'
	 *
	 * Add custom block category
	 *
	 * @param array $categories
	 *
	 * @param array $categories
	 *
	 * @access public
	 * @author Ben Moody
	 */
	function blocks_catergories( $categories ) {

		$categories = array_merge(
			$categories,
			array(
				array(
					'slug'  => 'prso-blocks',
					'title' => 'Custom Blocks',
				),
			)
		);

		return $categories;
	}

	/**
	 * blocks_allowed_block_types
	 *
	 * @CALLED BY FILTER 'allowed_block_types'
	 *
	 * https://wpdevelopment.courses/a-list-of-all-default-gutenberg-blocks-in-wordpress-5-0/
	 *
	 * Filter the block types users are allowed to access
	 *
	 * @param array $allowed_blocks
	 * @return array $allowed_blocks
	 * @access public
	 * @author Ben Moody
	 */
	function blocks_allowed_block_types( $allowed_blocks ) {

		//https://wpdevelopment.courses/a-list-of-all-default-gutenberg-blocks-in-wordpress-5-0/
		$allowed_blocks = array(
			//Common blocks
			'core/paragraph',
			'core/image',
			'core/heading',
			'core/gallery',
			'core/list',
			'core/quote',
			'core/audio',
			'core/cover',
			'core/file',
			'core/video',
			//Formatting
			'core/preformatted',
			'core/code',
			'core/freeform', //Classic Editor
			'core/html',
			'core/pullquote',
			'core/table',
			'core/verse',
			//Layout
			'core/button',
			'core/columns',
			'core/media-text',
			'core/more',
			'core/nextpage',//page break
			'core/separator',
			'core/spacer',
			//Widgets
			'core/shortcode',
//			'core/archives',
//			'core/categories',
//			'core/latest-comments',
//			'core/latest-posts',
			//Embeds
			'core/embed',
			'core-embed/twitter',
			'core-embed/youtube',
			'core-embed/facebook',
			'core-embed/instagram',
			'core-embed/spotify',
			'core-embed/flickr',
			'core-embed/vimeo',

			//Project blocks
			'prso/carousel'
		);

		/**
		 * prso_blocks__allowed_blocks
		 *
		 * @since 1.0.0
		 *
		 * @param array $allowed_blocks
		 */
		$allowed_blocks = apply_filters( 'prso_blocks__allowed_blocks', $allowed_blocks );

		return $allowed_blocks;
	}
	
	/**
	 * is_gutenberg_request
	 *
	 * Helper to detect if current request is from the Gutenberg editor
	 *
	 * @access public static
	 * @author Ben Moody
	 */
	public static function is_gutenberg_request() {

		global $pagenow;

		if ( isset( $pagenow ) && ( 'post-new.php' === $pagenow ) ) {
			return true;
		}

		if ( isset( $_REQUEST['_locale'] ) ) {
			return true;
		}

		if ( isset( $_REQUEST['action'] ) && ( 'edit' === $_REQUEST['action'] ) ) {
			return true;
		}
		
		if ( isset( $_REQUEST['action'] ) && ( 'acf/ajax/render_block_preview' === $_REQUEST['action'] ) ) {
			return true;
		}

		if ( isset( $_REQUEST['context'] ) && ( 'edit' === $_REQUEST['context'] ) ) {
			return true;
		}

		return false;
	}

}
new Prso_Gutenberg();
