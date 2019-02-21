<?php
/**
 * Class to handle any PHP required for facebook-video Gutenberg block
 * User: ben
 * Date: 2018-11-25
 * Time: 1:34 PM
 */

class Jam3_Blocks_Facebook_Video {

	private static $block_slug = 'facebook-video';

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
			'videoId' => esc_html( $block_data['attrs']['videoID'] ),
			'appId'   => esc_html( $block_data['attrs']['appID'] ),
		);

		return $json_output;
	}

}
