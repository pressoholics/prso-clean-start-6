<?php
/**
 * WP Query Model
 *
 * Contains methods to help with common mysql query tasks
 * 
 */
 
class PrsoCoreWpqueryModel {
	
	function __construct() {
		
		//Actions
		add_action( 'wp_update_nav_menu', array('PrsoCoreWpqueryModel', 'update_nav_menu_objects') ); //Nav cache
		
	}
	
	public static function optimize_query_args( $args = array() ) {
		
		//Init vars
		$defaults = array();
		
		//Set optimal arg defaults
		$defaults = array(
			'post_type'				=>	array('post','page'),
			'post_status'			=>	array('publish'),
			'posts_per_page'		=>	2,
			'fields'				=>	'ids',
			'orderby'				=>	'ID',
			'no_found_rows'			=>	TRUE,
			'suppress_filters'		=>	TRUE,
			'ignore_sticky_posts'	=>	TRUE
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		return $args;
	}
	
	/**
	 * Wrapper function around wp_nav_menu() that will cache the wp_nav_menu for all tag/category
	 * pages used in the nav menus
	 * @see http://lookup.hitchhackerguide.com/wp_nav_menu for $args
	 * @author tott
	 */
	public static function cached_nav_menu( $args = array(), $prime_cache = false ) {
	    global $wp_query;
	    $nav_menu_key = NULL;
	     
	    $queried_object_id = empty( $wp_query->queried_object_id ) ? 0 : (int) $wp_query->queried_object_id;
	    
	    if( isset($args['theme_location']) ) {
	    
		    $nav_menu_key = 'prso_nav_cache-' . $args['theme_location'];
		    
		    //Cache nav args
		    if( !get_transient( 'prso_nav_cache_args-' . $args['theme_location'] ) ) {
			    set_transient( 'prso_nav_cache_args-' . $args['theme_location'], $args, 3600 * 24 );
		    }
		    
	    }
	    
	    $my_args = wp_parse_args( $args );
	    $my_args = apply_filters( 'wp_nav_menu_args', $my_args );
	    $my_args = (object) $my_args;
	     
	    if ( ( isset( $my_args->echo ) && true === $my_args->echo ) || !isset( $my_args->echo ) ) {
	        $echo = true;
	    } else {
	        $echo = false;
	    }
	     
	    $skip_cache = false;
	    $use_cache = ( true === $prime_cache ) ? false : true;
	    
	    //Is nav key set, if not cancel cache and use wp_nav_menu
	    if( !isset($args['theme_location']) ) {
		    $skip_cache = true;
	    }
	   
	    if ( true === $skip_cache || true === $prime_cache || false === ( $nav_menu = get_transient( $nav_menu_key ) ) ) {
	        if ( false === $echo ) {
	            $nav_menu = wp_nav_menu( $args );
	            
	        } else {
	            ob_start();
	            wp_nav_menu( $args );
	            $nav_menu = ob_get_clean();
	            
	        }
	        if ( false === $skip_cache )
	            set_transient( $nav_menu_key, $nav_menu, 3600 * 24 );
	    }
	    if ( true === $echo )
	        echo $nav_menu;
	    else
	        return $nav_menu;
	}
	 
	/**
	 * Invalidate navigation menu when an update occurs
	 */
	public static function update_nav_menu_objects( $menu_id = null, $menu_data = null ) {
		
		//Init vars
		$locations 	= array();
		$nav_args	= array();
		
		$locations = get_nav_menu_locations();
		if( is_array( $locations ) && $locations ) {
			$locations = array_keys( $locations, $menu_id );
			if( $locations ) {
				foreach( $locations as $location ) {
					
					//Try and get cached nav args
					$nav_args = get_transient( 'prso_nav_cache_args-' . $location );
					
					//Set echo to false
					$nav_args['echo'] = false;
					
					delete_transient( 'prso_nav_cache-' . $location );
					
					//Update transient cache for nav
					if( isset($nav_args['theme_location']) ) {
						PrsoCoreWpqueryModel::cached_nav_menu( $nav_args, $prime_cache = true );
					}
					
				}
			}
		}
		
	}
	 
	/**
	 * Helper function that returns the object_ids we'd like to cache
	 */
	public static function get_nav_menu_cache_objects( $use_cache = true ) {
	    $object_ids = get_transient( 'prso_nav_menu_cache_object_ids' );
	    if ( true === $use_cache && !empty( $object_ids ) ) {
	        return $object_ids;
	    }
	 
	    $object_ids = $objects = array();
	     
	    $menus = wp_get_nav_menus();
	    foreach ( $menus as $menu_maybe ) {
	        if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id ) ) {
	            foreach( $menu_items as $menu_item ) {
	                if ( preg_match( "#.*/category/([^/]+)/?$#", $menu_item->url, $match ) )
	                    $objects['category'][] = $match[1];
	                if ( preg_match( "#.*/tag/([^/]+)/?$#", $menu_item->url, $match ) )
	                    $objects['post_tag'][] = $match[1];
	            }
	        }
	    }
	    if ( !empty( $objects ) ) {
	        foreach( $objects as $taxonomy => $term_names ) {
	            foreach( $term_names as $term_name ) {
	                $term = get_term_by( 'slug', $term_name, $taxonomy );
	                if ( $term )
	                    $object_ids[] = $term->term_id;
	            }
	        }
	    }
	     
	    $object_ids[] = 0; // that's for the homepage
	     
	    set_transient( 'prso_nav_menu_cache_object_ids', $object_ids );
	    return $object_ids;
	}
	
}