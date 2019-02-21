<?php
/**
 * Class to handle any PHP required for related-content Gutenberg block
 * User: ben
 * Date: 2018-11-25
 * Time: 1:34 PM
 */

class Jam3_Blocks_Related_Content {

	private static $block_slug = 'related-content';

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

		if ( method_exists( 'Jam3_Rest_Api', 'hypen_to_camel_case' ) ) {
			$json_output = array(
				'blockName' => Jam3_Rest_Api::hypen_to_camel_case( self::$block_slug, true ),
				//Set the correct type for this block
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

		$json_output['data'] = array(
			'title' => esc_html( $block_data['attrs']['blockTitle'] ),
		);

		if( method_exists('Jam3_Project_Fieldmanager_Helpers', 'format_link_output') ) {

			$items = $block_data['attrs']['items'];
			foreach( $items as $item ) {

				if( !isset($item['title'], $item['link']) ) {
					continue;
				}

				$item = self::get_item_link( $item );

				$json_output['data']['items'][] = Jam3_Project_Fieldmanager_Helpers::format_link_output( $item['title'], $item['link'], $item['external'] );

			}

		}


		return $json_output;
	}

	/**
	* get_item_link
	*
	* Helper to detect any intenal links (posts) and get the correct permalinks as well as set the external truthy flag
	*
	* @param array $item
	* @return array $item
	* @access private static
	* @author Ben Moody
	*/
	private static function get_item_link( $item ) {

		$item['external'] = true;

		//Handle internal links
		if( 'internal' === $item['linkType'] ) {

			if( isset($item['post']['id']) ) {
				$item['link'] = get_permalink( $item['post']['id'] );
			}

			$item['external'] = false;

		}

		return $item;
	}

}
