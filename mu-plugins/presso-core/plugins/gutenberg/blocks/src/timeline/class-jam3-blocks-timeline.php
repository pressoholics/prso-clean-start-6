<?php
/**
 * Class to handle any PHP required for timeline Gutenberg block
 * User: ben
 * Date: 2018-11-25
 * Time: 1:34 PM
 */

class Jam3_Blocks_Timeline {

	private static $block_slug = 'timeline';

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
			'title' => esc_html( $block_data['attrs']['blockTitle'] ),
		);

		$items = $block_data['attrs']['items'];
		foreach ( $items as $item ) {

			if ( ! isset( $item['title'], $item['date'], $item['content'] ) ) {
				continue;
			}

			$json_output['data']['items'][] = array(
				'title'       => esc_html( $item['title'] ),
				'date'        => esc_html( $item['date'] ),
				'description' => wp_kses_post( $item['content'] ),
			);

		}

		return $json_output;
	}

}
