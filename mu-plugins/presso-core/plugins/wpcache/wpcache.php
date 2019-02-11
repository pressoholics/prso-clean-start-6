<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018-10-29
 * Time: 2:37 PM
 */

class Prso_Cache {

	/**
	 * set_post_cache
	 *
	 * Helper to set cache for 'post' data, this allows us to group all related
	 * cache data together for a object by it's ID
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $post_id
	 * @param int $expire
	 *
	 * @return mixed
	 * @access public static
	 * @author Ben Moody
	 */
	public static function set_post_cache( $key, $data, $post_id = null, $expire = 0 ) {

		//vars
		$group = self::create_cache_group( 'post', $post_id );

		return self::set_cache( $key, $data, $group, $expire );
	}

	/**
	 * create_cache_group
	 *
	 * Helper to create a cache group name string based on context and object
	 * ID
	 *
	 * For example we group cache data for posts by context = post and group =
	 * post_id OR for users by context = user and group = user_id
	 *
	 * @param string $context
	 * @param string $group
	 *
	 * @return string $output
	 * @access private static
	 * @author Ben Moody
	 */
	private static function create_cache_group( $context = null, $group = null ) {

		//vars
		$output = '';

		if ( ! empty( $context ) && ! empty( $group ) ) {

			$output = "prso_{$context}_{$group}";

			$output = sanitize_text_field( $output );

		}

		return $output;
	}

	/**
	 * set_cache
	 *
	 * Helper to set cache using WP wp_cache_set object cache
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param string $group
	 * @param int $expire
	 *
	 * @return mixed
	 * @access public static
	 * @author Ben Moody
	 */
	private static function set_cache( $key, $data, $group, $expire = 0 ) {

		if ( ! isset( $key, $data ) ) {
			return false;
		}

		return wp_cache_set( $key, $data, $group, $expire );
	}

	/**
	 * set_taxonomy_cache
	 *
	 * Helper to set cache for 'taxonomy' term data, this allows us to group
	 * all related cache data together for a object by it's ID
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $term_id
	 * @param int $expire
	 *
	 * @return mixed
	 * @access public static
	 * @author Ben Moody
	 */
	public static function set_taxonomy_cache( $key, $data, $term_id = null, $expire = 0 ) {

		//vars
		$group = self::create_cache_group( 'taxonomy', $term_id );

		return self::set_cache( $key, $data, $group, $expire );
	}

	/**
	 * set_user_cache
	 *
	 * Helper to set cache for 'user' data, this allows us to group all related
	 * cache data together for a object by it's ID
	 *
	 * @param string $key
	 * @param mixed $data
	 * @param int $user_id
	 * @param int $expire
	 *
	 * @return mixed
	 * @access public static
	 * @author Ben Moody
	 */
	public static function set_user_cache( $key, $data, $user_id = null, $expire = 0 ) {

		//vars
		$group = self::create_cache_group( 'user', $user_id );

		return self::set_cache( $key, $data, $group, $expire );
	}

	/**
	 * wp_query_cache
	 *
	 * Helper to get/set wp_query results to wp cache.
	 * First tryies to get results from cache, if not set it then performs the
	 * wp_query with passed args, caches results and returns results
	 *
	 * Returns false on failure
	 *
	 * @param array $args - wp_query args
	 * @param string $context - post, taxonomy, or user
	 * @param string $key
	 * @param string $group
	 * @param int $expire
	 *
	 * @return mixed
	 * @access public static
	 * @author Ben Moody
	 */
	public static function wp_query_cache( $args = array(), $context = 'post', $key = null, $group = null, $expire = 0 ) {

		//vars
		$get_result   = null;
		$query_result = null;
		$get_group    = self::create_cache_group( $context, $group );

		if ( ! isset( $key ) ) {
			return false;
		}

		//First try and get cache
		$get_result = wp_cache_get( $key, $get_group, false );

		//No cache yet, set it
		$query_result = new WP_Query( $args );

		$set_method = "set_{$context}_cache";

		if ( ! method_exists( __CLASS__, $set_method ) ) {
			return false;
		}

		$set_result = self::$set_method( $key, $query_result, $group, $expire );

		//If success return the original cache data, false on failure
		if ( false === $set_result ) {
			return false;
		}

		return $query_result;
	}

}