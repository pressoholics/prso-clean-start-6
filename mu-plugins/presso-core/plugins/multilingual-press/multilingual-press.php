<?php

/**
 * PrsoMultilingualPress 3.0
 *
 * Class contains any customisation related to multilingual press
 *
 * FOR MultilingualPress Version 3.0+
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
	 * get_post_language_switcher_data
	 *
	 * Helper to return array of all langauge switcher data for current post
	 *
	 * @param type name
	 *
	 * @var type name
	 * @return type name
	 * @access public static
	 * @author Ben Moody
	 */
	public static function get_post_language_switcher_data( $post_id = 0 ) {

		//vars
		$output                   = array();
		$post_translations        = null;
		$post_translation_options = array();

		//Manually provided post id?
		if ( 0 !== $post_id ) {
			$post_translation_options['content_id'] = intval( $post_id );
		}

		//Get post translations for current post
		$post_translations = self::get_post_translations( $post_translation_options );

		if ( ! is_array( $post_translations ) ) {
			return $output;
		}

		foreach ( $post_translations as $translation ) {

			if ( ! method_exists( $translation, 'language' ) ) {
				continue;
			}

			$language_class = $translation->language();

			$language_name = $language_class->name();

			//Get url link to edit this post translation
			$target_site_id    = $translation->remoteSiteId();
			$target_post_id    = $translation->remoteContentId();
			$target_post_url   = null;
			$target_post_title = null;
			$is_archive        = false;

			//Handle archive pages first
			if ( is_post_type_archive() ) {

				$post_type = get_queried_object();

				if ( ! isset( $post_type->name ) ) {
					continue;
				}

				//Switch to source blog and get post type archive link
				switch_to_blog( $target_site_id );

				$target_post_url = get_post_type_archive_link( $post_type->name );

				$target_post_title = $post_type->label;

				$is_archive = true;

			} elseif ( 0 === $target_post_id ) { //No translation for this post? skip it!
				continue;
			} else {

				//Switch to source blog and get post type archive link
				switch_to_blog( $target_site_id );

				$target_post_title = get_the_title( $target_post_id );

				$target_post_url = get_post_permalink( $target_post_id );

			}

			restore_current_blog();

			$output[] = array(
				'language'       => preg_replace( '/\([^)]+\)/', '', $language_name ),
				'post_title'     => $target_post_title,
				'post_permalink' => add_query_arg( 'noredirect', 'true', $target_post_url ),
				'is_archive'     => $is_archive,
			);

		}

		return $output;
	}

	/**
	* is_mlp_installed
	*
	* Helper to detect if Multilingual press 3.0+ is installed
	*
	* @return mixed wp_error / true
	* @access public static
	* @author Ben Moody
	*/
	public static function is_mlp_installed() {

		if( !class_exists('\Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs') ) {
			//No valid post object
			return new WP_Error(
				'is_mlp_installed',
				'MultilingualPress not installed'
			);
		}

		return true;
	}

	/**
	 * get_post_translations
	 *
	 * Helper to get instance of multilingual press plugin lanuage api class
	 *
	 * @return object WP_Error / mlp_language_api
	 * @access public static
	 * @author Ben Moody
	 */
	public static function get_post_translations( $mlp_args = array() ) {

		//vars
		global $post;

		$defaults = array(
			'site_id'       => get_current_blog_id(),
			'type'          => 'post',
			'filter_source' => true,
		);

		//Is MLP plugin installed?
		if( is_wp_error( $mlp_installed = self::is_mlp_installed() ) ) {
			return $mlp_installed;
		}

		//Maybe fallback to current post if no post is specified
		if( !isset($mlp_args['content_id']) ) {

			if( is_object($post) && isset($post->ID) ) {

				$defaults['content_id'] = $post->ID;

			} else {
				//No valid post object
				return new WP_Error(
					'get_post_translations',
					'Global post is not valid post object',
					$post
				);
			}

		}

		$mlp_args = wp_parse_args( $mlp_args, $defaults );

		//Should we include the source language data?
		if ( true === $mlp_args['filter_source'] ) {

			$mlp_query_args = \Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs::forContext( new \Inpsyde\MultilingualPress\Framework\WordpressContext() )
			                                                                                ->forSiteId( $mlp_args['site_id'] )
			                                                                                ->forContentId( $mlp_args['content_id'] )
			                                                                                ->forType( $mlp_args['type'] )
			                                                                                ->dontIncludeBase();

		} else {

			$mlp_query_args = \Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs::forContext( new \Inpsyde\MultilingualPress\Framework\WordpressContext() )
			                                                                                ->forSiteId( $mlp_args['site_id'] )
			                                                                                ->forContentId( $mlp_args['content_id'] )
			                                                                                ->forType( $mlp_args['type'] )
			                                                                                ->includeBase();

		}


		$post_translations = \Inpsyde\MultilingualPress\resolve(
			\Inpsyde\MultilingualPress\Framework\Api\Translations::class
		)->searchTranslations( $mlp_query_args );

		return $post_translations;
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

		//Is MLP plugin installed?
		if( is_wp_error( $mlp_installed = self::is_mlp_installed() ) ) {
			return $mlp_installed;
		}

		//Setup screen id's where we need to show the multilingual press list table column
		$this->setup_list_table_screen_ids();

		//Setup list table column for post translation links
		$this->setup_list_table_post_translations_columns();

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
		$post_translations = null;
		$mlp_args          = array(
			'site_id'    => get_current_blog_id(),
			'content_id' => intval( $post_id ),
			'type'       => 'post',
		);
		$output            = null;

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

		$post_translations = self::get_post_translations( $mlp_args );

		if ( ! is_array( $post_translations ) ) {
			return;
		}

		//Build list of links to edit translations
		foreach ( $post_translations as $translation ) {

			if ( ! method_exists( $translation, 'language' ) ) {
				continue;
			}

			$language_class = $translation->language();

			$language_name = $language_class->name();

			//Get url link to edit this post translation
			$target_site_id = $translation->remoteSiteId();
			$target_post_id = $translation->remoteContentId();

			//No translation for this post? skip it!
			if ( 0 === $target_post_id ) {
				continue;
			}

			switch_to_blog( $target_site_id );
			$post_edit_link = get_edit_post_link( $target_post_id );
			restore_current_blog();

			ob_start();
			?>
			<a href="<?php echo esc_url( $post_edit_link ); ?>" target="_blank">
				<?php echo esc_html( $language_name ); ?>
			</a><br/>
			<?php
			$output .= ob_get_contents();
			ob_end_clean();

		}

		echo $output;

		return;
	}

}

new PrsoMultilingualPress();
