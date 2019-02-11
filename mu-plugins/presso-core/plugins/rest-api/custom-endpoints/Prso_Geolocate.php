<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018-10-17
 * Time: 11:57 AM
 */

class PrsoGeolocate extends PrsoCustomRestApi {

	public function __construct() {

		add_filter( 'rest_api_init', array( $this, 'register_routes' ) );

	}

	/**
	 * register_routes
	 *
	 * @CALLED BY ACTION 'rest_api_init'
	 *
	 * Register rest api routes
	 *
	 * @access public
	 */
	public function register_routes() {

		register_rest_route(
			self::$base_api_url, '/geolocation',
			array(
				'callback' => array( $this, 'prepare_result' ),
				'methods'  => 'GET',
			)
		);

	}

	/**
	 * prepare_result
	 *
	 * @CALLED BY register_rest_route()
	 *
	 * Prepare the rest api response for the user geolocation lookup, Hook into
	 *     the 'prso_geolocate__response' filter to alter the response object
	 *
	 * @param object $data
	 *
	 * @return object $response
	 * @access public
	 * @author Ben Moody
	 */
	public function prepare_result( $data ) {

		//vars
		$params   = array();
		$response = null;
		$user_ip = null;

		if ( is_object( $data ) ) {
			$params = $data->get_params();
		}

		//Has a user ip been provided
		if( isset($params['ip']) ) {
			$user_ip = sanitize_text_field( $params['ip'] );
		}

		//Get user location
		$user_location = $this->get_user_location( $user_ip );

		/**
		 * prso_geolocate__response
		 *
		 * Filter the rest api response for the geolocation lookup
		 *
		 * @since 1.0.0
		 *
		 * @see
		 *
		 * @param object $response
		 * @param object $user_location
		 */
		$response = apply_filters( 'prso_geolocate__response', $response, $user_location );

		return rest_ensure_response( $response );
	}

	/**
	 * get_user_location
	 *
	 * @CALLED BY $this->prepare_result()
	 *
	 * Uses the set geolocation rest api service to get and cache the user
	 *     locaiton data. NOTE you can change the geolocaiton service rest api
	 *     endpoint via the 'prso_geolocate__service_endpoint' filter
	 *
	 * @return mixed $result
	 * @access private
	 * @author Ben Moody
	 */
	private function get_user_location( $user_ip = null ) {

		//vars
		$user_ip_address                = $user_ip;
		$geo_location_rest_api_endpoint = null;

		//Get user IP address
		if( empty($user_ip) ) {
			$user_ip_address = self::get_user_ip();
		}

		/**
		 * prso_geolocate__service_endpoint
		 *
		 * Filter the rest api endpoint used to get geolocation data from ip address
		 *
		 * @since 1.0.0
		 *
		 * @see
		 *
		 * @param rest api endpoint URL
		 * @param current user ip address
		 */
		$geo_location_rest_api_endpoint = apply_filters( 'prso_geolocate__service_endpoint', 'http://ip-api.com/json/' . $user_ip_address, $user_ip_address );

		//Cache params
		$cache = array(
			'key'   => $geo_location_rest_api_endpoint,
			'group' => 'prso_geolocate',
		);

		//Try and get cache
		$result = wp_cache_get( $cache['key'], $cache['group'], false );

		//Found cached result?
		if ( false !== $result ) {
			return $result;
		}

		//Make rest api get request and return data
		$response = wp_remote_get( $geo_location_rest_api_endpoint );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( ! is_array( $response ) ) {
			$result = false;
		} else {
			$result = json_decode( $response['body'] );
		}

		//Set new cache value
		wp_cache_set( $cache['key'], $result, $cache['group'] );

		return $result;
	}

	/**
	 * get_user_ip
	 *
	 * Helper to get ip address of current user, detects if in dev mode and if
	 * so uses external service to get correct ip address as otehrwise local
	 * dev ip would be used
	 *
	 * @return string $user_ip_address
	 * @access public static
	 * @author Ben Moody
	 */
	public static function get_user_ip() {

		$user_ip_address = $_SERVER['REMOTE_ADDR'];

		$ip_response = wp_remote_get( 'https://api.ipify.org/?format=json' );

		if ( is_wp_error( $ip_response ) ) {
			return $user_ip_address;
		}

		if ( is_array( $ip_response ) ) {

			$ip_response_body = json_decode( $ip_response['body'] );

			if ( isset( $ip_response_body->ip ) ) {

				$user_ip_address = sanitize_text_field( $ip_response_body->ip );

			}

		}

		return $user_ip_address;
	}

}

new PrsoGeolocate();
