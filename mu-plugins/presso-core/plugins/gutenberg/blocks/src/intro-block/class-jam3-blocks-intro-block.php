<?php
/**
 * Class to handle any PHP required for intro-block Gutenberg block
 * User: ben
 * Date: 2018-11-25
 * Time: 1:34 PM
 */

class Jam3_Blocks_Intro_Block {

	private static $block_slug = 'intro-block';

	/**
	* render_json
	*
	* This is the REST API callback to return the json data array for the rest response, unique to this block and it's data
	*
	* @param array $block_data
	* @return array $json_output
	* @access public static
	* @author Ben Moody
	*/
	public static function render_json( $block_data = array() ) {

		//vars
		$json_output = array();

		if( method_exists('Jam3_Rest_Api', 'hypen_to_camel_case') ) {
			$json_output = array(
				'blockName' => Jam3_Rest_Api::hypen_to_camel_case( self::$block_slug ), //Set the correct type for this block
			);
		}

		if( !isset($block_data['attrs']) ) {

			//Missing data
			return new WP_Error(
				'render_json()',
				'Missing block attrs',
				$block_data
			);

		}

		$json_output['data'] = wp_kses_post( $block_data['innerHTML'] );

		return $json_output;
	}

}
