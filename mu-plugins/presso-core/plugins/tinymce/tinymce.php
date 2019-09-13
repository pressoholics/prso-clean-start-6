<?php

/**
* PrsoCustomTinymce
*
* Class contains any customisation to tinymce visual editor
*∂
* @access 	public
* @author	Ben Moody
*/
class PrsoCustomTinymce {

    function __construct() {

        //$this->init_custom();

    }

    private function init_custom() {

	    //Add stylesheet button to tinymce
	    add_filter( 'mce_buttons_2', array( $this, 'enable_stylesheet_button' ), 10, 1 );

	    //Register our custom styles
	    add_filter( 'tiny_mce_before_init', array($this, 'register_custom_styles'), 10, 1 );

	    add_filter( 'wp_mce_translation', array($this, 'filter_tinymce_strings'), 999, 2 );

	    //Remove headings from blog heading tinymce dropdown
	    add_filter('tiny_mce_before_init', array($this, 'tiny_mce_remove_unused_formats') );

    }

    public function tiny_mce_remove_unused_formats( $init ) {

        // Add block format elements you want to show in dropdown
        $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;';

        return $init;

    }

    /**
     * fbm_filter_tinymce_strings
     *
     * @CALLED BY FILTER 'wp_mce_translation'
     *
     * Alter the tinymce translation strings, we can use this to customize any strings in the editor
     *
     * @access 	public
     * @author	Ben Moody
     */
    public function filter_tinymce_strings( $mce_translation, $mce_locale ) {

        if( isset($mce_translation['Heading 2']) ) {

            $mce_translation['Heading 2'] = esc_html_x( 'Main Heading', 'text', FB_MESSENGER__DOMAIN );

        }

        if( isset($mce_translation['Heading 5']) ) {

            $mce_translation['Heading 5'] = esc_html_x( 'Heading 5 (Title Block)', 'text', FB_MESSENGER__DOMAIN );

        }

        return $mce_translation;
    }

    public function enable_stylesheet_button( $buttons ) {

        array_unshift( $buttons, 'styleselect' );

        return $buttons;
    }

    /**
    * FUNCTION_NAME
    *
    * @CALLED BY FILTER/ACTION ''
    *
    * https://codex.wordpress.org/TinyMCE_Custom_Styles
     * https://www.tinymce.com/docs/configure/content-formatting/#formats
    *
    * @param	type	name
    * @var		type	name
    * @return	type	name
    * @access 	public
    * @author	Ben Moody
    */
    public function register_custom_styles( $init_array ) {

        // Define the style_formats array
        $style_formats = array(
            // Each array child is a format with it's own settings
            array(
                'title'     => 'Heading Dash',
                'selector'  => 'h2,h3,h4,h5,h6',
                'classes'   => 'dash',
            ),
            array(
                'title'     => 'Overview',
                'selector'  => 'p',
                'classes'   => 'cms-overview',
            ),
            array(
                'title'         => 'Title Block',
                'block'         => 'div',
                'classes'       => 'cms-title-block',
                'wrapper'       => true,
            ),
            array(
                'title'     => 'Code Block',
                'block'     => 'code',
                'wrapper'   => true,
            ),
        );

        // Insert the array, JSON ENCODED, into 'style_formats'
        $init_array['style_formats'] = wp_json_encode( $style_formats );

        return $init_array;
    }

}
new PrsoCustomTinymce();