<?php

/**
 * PrsoMultilingualPress
 *
 * Class contains any customisation related to multilingual press
 *∂
 *
 * @access    public
 * @author    Ben Moody
 */
class PrsoMultilingualPress {

	private $list_table_screen_ids;

	function __construct() {

		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'admin_footer', array( $this, 'render_admin_css' ) );

	}

	/**
	 * admin_init
	 *
	 * @CALLED BY /ACTION 'admin_init'
	 *
	 * Actions to be performed during admin_init
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function admin_init() {

		if ( ! class_exists( 'Mlp_Language_Api' ) ) {
			return;
		}

		//Setup screen id's where we need to show the multilingual press list table column
		$this->setup_list_table_screen_ids();

		//Setup list table column for post translation links
		$this->setup_list_table_post_translations_columns();

	}

	/**
	* render_admin_css
	*
	* @CALLED BY /ACTION 'admin_footer'
	*
	* Render little snippet of css to better style custom columns and interface
	*
	* @access public
	* @author Ben Moody
	*/
	function render_admin_css() {

		?>
		<style type="text/css">
			.fixed th.column-prso_multilingualpress {
				width: 10em;
			}
		</style>
		<?php

	}

	/**
	 * setup_list_table_screen_ids
	 *
	 * @CALLED BY $this->admin_init()
	 *
	 * Set default screen ids for list table translation columns, then apply
	 *     filter to the array
	 *
	 * @access public
	 * @author Ben Moody
	 */
	private function setup_list_table_screen_ids() {

		//vars
		$defaults = array(
			'page',
			'post',
		);

		/**
		 * prso_multilingual_press_list_table_posts
		 *
		 * Filter array of post types (screen id's) used to render multilingual press list table column displaying link to post translation
		 *
		 * https://make.wordpress.org/docs/plugin-developer-handbook/10-plugin-components/custom-list-table-columns/
		 *
		 * @param array $defaults
		 */
		$this->list_table_screen_ids = apply_filters(
			'prso_multilingual_press_list_table_posts',
			$defaults
		);

	}

	/**
	 * setup_list_table_post_translations_columns
	 *
	 * @CALLED BY $this->admin_init()
	 *
	 * Loop $this->list_table_screen_ids array and register filters to add
	 *     custom list table columnd and render it's content for each screen id
	 *     in the array
	 *
	 * @access public
	 * @author Ben Moody
	 */
	private function setup_list_table_post_translations_columns() {

		//Loop array of screen ids and call function to add column for each one
		foreach ( $this->list_table_screen_ids as $screen_id ) {

			add_filter( "manage_edit-{$screen_id}_columns", array(
				$this,
				'add_list_table_column',
			) );

			add_action( "manage_{$screen_id}_posts_custom_column", array(
				$this,
				'add_list_table_content',
			), 10, 2 );

		}

	}

	/**
	 * add_list_table_column
	 *
	 * @CALLED BY FILTER/ "manage_edit-{$screen_id}_columns"
	 *
	 * Add custom list table column for post translations
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function add_list_table_column( $columns ) {

		$columns['prso_multilingualpress'] = 'Translations';

		return $columns;
	}

	/**
	 * add_list_table_content
	 *
	 * @CALLED BY /ACTION "manage_{$screen_id}_posts_custom_column"
	 *
	 * Detect any translations for the current post in list table and render
	 *     links to edit those translations
	 *
	 * https://multilingualpress.org/docs/get-translations-programmatically/
	 *
	 * @access public
	 * @author Ben Moody
	 */
	public function add_list_table_content( $colname, $post_id ) {

		//vars
		$mlp_language_api  = self::get_language_api();
		$post_translations = null;
		$mlp_args          = array(
			'site_id'    => get_current_blog_id(),
			'content_id' => intval( $post_id ),
			'type'       => 'post',
		);
		$output            = null;

		if ( is_wp_error( $mlp_language_api ) ) {
			return;
		}

		if ( 'prso_multilingualpress' !== $colname ) {
			return;
		}

		/**
		 * prso_multilingual_press_get_translations_args
		 *
		 * Filter array of args passed to multilingual press get_tranlsation method
		 *
		 * https://multilingualpress.org/docs/get-translations-programmatically/
		 *
		 * @param array $defaults
		 */
		$mlp_args = apply_filters(
			'prso_multilingual_press_get_translations_args',
			$mlp_args
		);

		$post_translations = $mlp_language_api->get_translations( $mlp_args );

		if ( empty( $post_translations ) ) {
			return;
		}

		//Build list of links to edit translations
		foreach ( $post_translations as $translation ) {

			$language_class = $translation->get_language();

			$language_name = $language_class->get_name();

			//Get url link to edit this post translation
			$target_site_id = $translation->get_target_site_id();
			$target_post_id = $translation->get_target_content_id();

			switch_to_blog( $target_site_id );
			$post_edit_link = get_edit_post_link( $target_post_id );
			restore_current_blog();

			ob_start();
			?>
			<a href="<?php echo esc_url( $post_edit_link ); ?>" target="_blank">
				<img src="<?php echo esc_url( $translation->get_icon_url() ); ?>"
					 alt="<?php echo esc_html( $language_name ); ?>">
			</a>
			<?php
			$output .= ob_get_contents();
			ob_end_clean();

		}

		echo $output;

		return;
	}

	/**
	 * get_language_api
	 *
	 * Helper to get instance of multilingual press plugin lanuage api class
	 *
	 * @return object WP_Error / mlp_language_api
	 * @access public static
	 * @author Ben Moody
	 */
	public static function get_language_api() {

		$mlp_language_api = null;

		$mlp_language_api = apply_filters( 'mlp_language_api', null );

		if ( ! is_a( $mlp_language_api, 'Mlp_Language_Api_Interface' ) ) {

			return new WP_Error(
				'mlp_language_api_error',
				'PrsoMultilingualPress::get_language_api'
			);

		}

		return $mlp_language_api;
	}

}

new PrsoMultilingualPress();
