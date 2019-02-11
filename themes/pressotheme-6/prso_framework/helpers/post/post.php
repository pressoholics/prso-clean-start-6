<?php
/**
 * Wordpress Post/Page data Helper class file.
 *
 * Simplifies the process of quering wordpress post and page data.
 *
 * CONTENTS:
 *
 *	1. get_related_posts			-	do_action('prso_get_related_posts', $args); 
 *	2. get_ID_by_slug				-	apply_filters('prso_get_page_id_by_slug', NULL, $page_slug, $post_type);
 *  3. get_page_content				-	apply_filters('prso_get_page_content', NULL, $page_id_slug);
 *  4. prev_next_pagination			-	do_action('prso_query_posts_by_category', $cat_slug, $args);
 *  5. get_most_recent_post_of_user	-	do_action('prso_prev_next_permalink', $args);
 *	6. deep_mobile_nav				-	do_action('prso_deep_mobile_nav', $args);
 * 
 */
class PostHelper {
	
	function __construct() {
		
		//Add custom action hooks for post helpers
 		$this->custom_action_hooks();
		
	}
	
	/**
	* custom_action_hooks
	* 
	* Create any custom WP Action Hooks here for post helpers
	* 
	* @access 	private
	* @author	Ben Moody
	*/
 	private function custom_action_hooks() {
 		
 		/**
 		* 1. prso_get_related_posts
 		* 	 Returns WP_Query object for any related posts found. Based on taxonomies
 		*	 passed via $args['tax_args']. Defaults to 'post_tag' and 'category'
 		*/
 		$this->add_filter( 'prso_get_related_posts', 'get_related_posts', 10, 1 );
 		
 		/**
 		* 2. prso_get_page_id_by_slug
 		* 	 Shortcut for returning a page ID using it's slug.
 		*/
 		$this->add_filter( 'prso_get_page_id_by_slug', 'get_ID_by_slug', 1, 3 );
 		
 		/**
 		* 3. prso_get_page_content
 		* 	 Shortcut for returning a pages content by ID or Slug
 		*/
 		$this->add_filter( 'prso_get_page_content', 'get_page_content', 1, 2 );
 		
 		/**
 		* 4. prso_prev_next_permalink
 		* 	 Echo permalink to next/prev post in a loop of pages
 		*/
 		$this->add_action( 'prso_prev_next_permalink', 'prev_next_pagination', 10, 1 );
 		
 		/**
 		* 5. prso_user_recent_post
 		* 	 Returns the most recent post of the user based on supplied user_id
 		*	 can also return custom post types vis post_type in $args array
 		*/
 		$this->add_filter( 'prso_user_recent_post', 'get_most_recent_post_of_user', 10, 2 );
 		
 		/**
 		* 6. prso_deep_mobile_nav
 		* 	 Detects if current post/page is a child page itself and then detects if it
 		*	 has any children of it's own. If so it then builds a tertiary nav dropdown
 		*/
 		$this->add_action( 'prso_deep_mobile_nav', 'deep_mobile_nav', 10, 1 );
 		
 	}
	
	/**
	* get_related_posts
	*
	* Filter:: 'prso_get_related_posts'
	*
	* Returns the WP_Query object for any related posts found.
	* Uses taxonomies passed via $args['tax_args'] array to form the posts relationship
	* You can pass custom taxonomies to form the relationship if you want
	*
	* Also pass WP_Query args via $args
	*
	* @param	array	$args	-	WP_Query args and 'tax_args'
	* @access	public
	* @author	Ben Moody
	*/ 
	public function get_related_posts( $args = array() ) {
			
		//Init vars
		global $post;
		$output 	= NULL;
		$taxes		= array();
		$tax_query	= array();
		$defaults	= array(
			'showposts' 			=> 3,
			'ignore_sticky_posts' 	=> 1,
			'orderby'				=> 'post_date',
			'order'					=> 'DESC',
			'post_type'				=> NULL,
			'post_status'			=> 'publish',
			'tax_args'				=> array( 'post_tag', 'category' )
		);
		
		//Parse args
		$args = wp_parse_args( $args, $defaults );
		
		if( isset($post->ID) ) {
			
			//Ignore current post
			$args['post__not_in'] = array( $post->ID );		
			
			//Get post taxonomies
			$taxes = wp_get_post_terms(
	    		$post->ID,
	    		$args['tax_args'],
	    		array(
	    			'orderby'		=> 'count',
	    			'hide_empty'	=> true
	    		)
	    	);
			
			//Loop post taxonomies and cache tax query for wp_query
			foreach( $taxes as $tax ) {
				
				if( isset($tax->taxonomy, $tax->term_taxonomy_id) ) {
					$args['term_taxonomy_ids_in'][] = $tax->term_taxonomy_id;
				}
				
			}
			
			//Filter the mysql query used by wp_query
			add_filter( 'posts_join', 		array($this, 'tax_posts_join'), 10, 2 );
			add_filter( 'posts_where', 		array($this, 'tax_posts_where'), 10, 2 );
			add_filter( 'posts_groupby', 	array($this, 'tax_posts_group_by'), 10, 2 );
			add_filter( 'posts_request', 	array($this, 'tax_posts_request') );
			
			$output = new WP_Query( $args );
			
			//REMOVE Filter the mysql query used by wp_query
			remove_filter( 'posts_join', 	array($this, 'tax_posts_join'), 10, 2 );
			remove_filter( 'posts_where', 	array($this, 'tax_posts_where'), 10, 2 );
			remove_filter( 'posts_groupby', array($this, 'tax_posts_group_by'), 10, 2 );
			remove_filter( 'posts_request', array($this, 'tax_posts_request') );
			
		}
		
		return $output;
		
	}
	
	/**
	* tax_posts_join
	*
	* Called By:: $this->get_related_posts()
	*
	* Filter is added by get_related_posts and filters the JOIN section
	* of WP_Query's mysql query.
	*
	* This is nessessary to ensure a more effcient mysql query when
	* there are many taxonomies
	*
	* @access	public
	* @author	Ben Moody
	*/ 
	public function tax_posts_join( $sql, $wp_query ) {
		
		//Init vars
		global $wpdb;
		$tax_ids = array();
		
		//Confirm that our taxonomy id's are in the query object
		if( $tax_ids = $wp_query->get('term_taxonomy_ids_in') ) {
			
			//Add the INNER JOIN here
			$sql.= " INNER JOIN {$wpdb->term_relationships} ON ( {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id )";
			
		}
		
		return $sql;
	}
	
	/**
	* tax_posts_where
	*
	* Called By:: $this->get_related_posts()
	*
	* Filter is added by get_related_posts and filters the WHERE section
	* of WP_Query's mysql query.
	*
	* This is nessessary to ensure a more effcient mysql query when
	* there are many taxonomies
	*
	* @access	public
	* @author	Ben Moody
	*/
	function tax_posts_where( $sql, $wp_query ) {
		
		//Init vars
		global $wpdb;
		$tax_ids = array();
		
		//Confirm that our taxonomy id's are in the query object
		if( $tax_ids = $wp_query->get('term_taxonomy_ids_in') ) {
			
			//Implode the array of taxonomy ids for use in the mysql query
			$tax_ids = implode(', ', $tax_ids);
			
			//Add the AND to the mysql query
			$sql.= " AND ( {$wpdb->term_relationships}.term_taxonomy_id IN ({$tax_ids}) ) ";
			
		}
		
		return $sql;
	}
	
	/**
	* tax_posts_group_by
	*
	* Called By:: $this->get_related_posts()
	*
	* Filter is added by get_related_posts and filters the GROUP BY section
	* of WP_Query's mysql query.
	*
	* This is nessessary to ensure a more effcient mysql query when
	* there are many taxonomies
	*
	* @access	public
	* @author	Ben Moody
	*/
	function tax_posts_group_by( $sql, $wp_query ) {
		
		//Init vars
		global $wpdb;
		$tax_ids = array();
		
		//Confirm that our taxonomy id's are in the query object
		if( $tax_ids = $wp_query->get('term_taxonomy_ids_in') ) {
			
			//Add the AND to the mysql query
			$sql.= " {$wpdb->posts}.ID ";
			
		}
		
		return $sql;
	}
	
	/**
	* tax_posts_request
	*
	* Called By:: $this->get_related_posts()
	*
	* Filter is added by get_related_posts and allows debugging the
	* mysql query.
	*
	* @access	public
	* @author	Ben Moody
	*/
	function tax_posts_request( $sql ) {
		
		//prso_debug($sql);
		//exit();
		
		return $sql;
	}
	
	/**
	* get_ID_by_slug
	*
	* apply_filters('prso_get_page_id_by_slug', NULL, $page_slug, $post_type);
	*
	* Shortcut for returning a page ID using it's slug
	*
	* @param	string	$page_slug - page slug, can be take from url :)
	* @param	string	$post_type - 'page', 'post', 'custom post type'
	*/ 
	public function get_ID_by_slug( $output, $page_slug, $post_type = 'page' ) {
	    
	    $page = get_page_by_path( $page_slug, 'OBJECT', $post_type );
	    
	    if ( isset($page->ID) ) {
	        $output = $page->ID;
	    } else {
	        $output = null;
	    }
	    
	    return $output;
	}
	
	/**
	* get_page_content
	* 
	* apply_filters('prso_get_page_content', NULL, $page_id_slug);
	* 
	* Returns the content of any page by either it's ID or slug
	*
	* @param	mixed	ID or slug of page content you wish to return
	* @access 	public
	* @author	Ben Moody
	*/
	public function get_page_content( $content, $page_id_slug = NULL ) {
		
		//Init vars
		$page_data 	= NULL;
		
		$page_id_slug = esc_attr( $page_id_slug );
		
		if( isset($page_id_slug) ) {
			
			//Detect if this is a page ID or Slug
			if( is_string($page_id_slug) ) {
				//Convert slug into page ID
				$page_id_slug = apply_filters( 'prso_get_page_id_by_slug', NULL, $page_id_slug );
			}
			
			//Get page data
			if( isset($page_id_slug) ) {
				$page_data = get_page( $page_id_slug );
				
				//Get page content and run wordpress content filters on it
				$content = apply_filters( 'the_content', $page_data->post_content );
			}
			
		}
		
		return $content;
	}
	
	/**
	 * prev_next_pagination()
	 *
	 * This helper will return the post permalink for next or previous posts of similar post type and/or
	 * custom taxonomy.
	 *
	 * The helper uses wp_query to query the posts so simply add you wp_query args into $args and the helper
	 * will automatically create a 1 post/page pagination loop allowing you to use prev/next post buttons for users
	 * to loop through a set of posts.
	 *
	 * Be sure to declare whether you want the 'next' or 'previous' post usnig the 'direction' key in args array
	 *
	 * @param	array	$args - any get_categories args you wish to customize
	 * @return	array	$_children	- Onject array containing all child/grandchild categories
	 */
	public function prev_next_pagination( $args = array() ) {
		
		//Init vats
		global $post;
		$paged				= NULL;
		$total_posts		= NULL;
		$post_terms 		= NULL;
		$post_term_slug		= NULL;
		$taxonomy 			= NULL;
		$portfolio_posts	= NULL;	
		$PostData			= NULL;
		$permalink			= NULL;
		$defaults = array(
			'direction' 		=> NULL,
			'post_type'			=> 'page',
			'posts_per_page' 	=> 1,
			'tax_query'			=> array(
				array(
					'taxonomy' 	=> NULL,
					'field'		=> 'slug',
					'terms'		=> NULL
				)
			)
		);
		
		$args = wp_parse_args($args, $defaults);
		
		extract($args);
		
		//Cache the page var
		$paged = get_query_var('page');
		
		//First get the current portfolio items taxonomy
		if( isset($post->ID) ) {
			$post_terms = wp_get_post_terms( $post->ID, $taxonomy );
			
			//Cache the first term as artist cat
			if( isset($post_terms[0]->slug) ) {
				$post_term_slug = $post_terms[0]->slug;
			}
			
			//Get all portfolio post for this artist
			$args['tax_query'][0]['terms'] = $post_term_slug;
			
			
			//this is the first page and we must find where this sits in relation to other posts	
			$paged = NULL;
			
			//We need to find which page the landing page sits in relation to wp_query pagination
			//So get all pages and loop until we find the page we are on
			$args['posts_per_page'] = -1;
			$portfolio_posts = new WP_Query( $args );
			
			wp_reset_query();
			
			//Loop through all posts and find out where our current post is in the array this will be the page var
			if( isset($portfolio_posts->posts) && !empty($portfolio_posts->posts) ) {
				$post_page_count = 1;
				
				foreach( $portfolio_posts->posts as $key => $post_obj ){
					//If we have found the landing post, cache it's array position as the $paged var
					if( $post_obj->ID === $post->ID ) {
						$paged = $post_page_count;
						break;
					}
					
					$post_page_count++;
				}
				
				//Now run the query again this time find either the page previous or next and get the hyperlink
				if( isset($paged) && is_int($paged) ) {
					
					//First cache the total number of posts found in the pagination
					$total_posts = count( $portfolio_posts->posts );
			
					//As we only have 1 post per page $total_posts is also the total number of pages too!
					$total_pages = $total_posts;
					
					//Based on $direction var set the page to get
					switch( $direction ) {
						case 'previous':
							//Add page arg to url
							$new_page = $paged - 1;
							
							//Make sure we don't go negative
							if( $new_page < 1 ) {
								$new_page = $total_pages;
							}
							
							break;
						case 'next':
							//Add page arg to url
							$new_page = $paged + 1;
							
							//Make sure we don't go over total pages
							if( $new_page > $total_pages ) {
								$new_page = 1;
							}
							
							break;
					}
					
					$args['paged'] = $new_page;
					
					$args['posts_per_page'] = 1;
					$portfolio_posts = new WP_Query( $args );
					
					if( isset($portfolio_posts->posts) ) {
						$PostData = $portfolio_posts;
					}
					
					wp_reset_query();
					
				}
				
			}
			
			//So if we have found the post data lets create the hyperlink for next/previous page
			if( isset($PostData->posts[0]->ID) ){
				
				//Get the post permalink
				$permalink = get_post_permalink( $PostData->posts[0]->ID );
					
			}
			
		}
		
		if( !empty($permalink) ) {
			echo esc_url( $permalink );
		}
		
	}
	
	/**
	* get_most_recent_post_of_user
	* 
	* apply_filters('prso_user_recent_post', $user_id, $args);
	* 
	* Returns the post object for the users most recent post.
	* Can also return other post types by setting the post_type in $args array
	*
	* @param	array		args
	* @access 	public
	* @author	Ben Moody
	*/
	public function get_most_recent_post_of_user( $user_id = NULL, $args ) {
	
		//Init vars
		global $wpdb;
		$most_recent_post 	= array();
		
		$defaults = array(
			'post_type' => 'post'
		);
		
		if( isset($user_id) ) {
			
			$args = wp_parse_args( $args, $defaults );
		
			extract( $args );
			
			//Sanitize vars
			$user_id = (int) $user_id;
			$post_type = esc_attr( $post_type );
			
			$recent_post = $wpdb->get_row( $wpdb->prepare("SELECT ID, post_date_gmt FROM {$wpdb->posts} WHERE post_author = %d AND post_type = '{$post_type}' AND post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 1", $user_id ), ARRAY_A);
		
			// Make sure we found a post
			if ( isset($recent_post['ID']) ) {
				$post_gmt_ts = strtotime($recent_post['post_date_gmt']);
		
				// If this is the first post checked or if this post is
				// newer than the current recent post, make it the new
				// most recent post.
				if ( !isset($most_recent_post['post_gmt_ts']) || ( $post_gmt_ts > $most_recent_post['post_gmt_ts'] ) ) {
					$most_recent_post = get_post( $recent_post['ID'] );
				}
			}
			
		}
	
		return $most_recent_post;
	}
	
	/**
	* deep_mobile_nav
	* 
	* do_action('prso_deep_mobile_nav', $args);
	* 
	* Detects if current page/post has a parent (is at least one level deep).
	* If so, it then detects if the page/post has any children, if it does then
	* the function will build a select dropdown of all the child pages and output
	* some jquery to redirect users to tertiary pages when selected.
	*
	* @param	array		args
	* @access 	public
	* @author	Ben Moody
	*/
	public function deep_mobile_nav( $args ) {
		
		//Init vars
		global $post;
		$child_posts 	= array();
		$html_output	= NULL;
		$defaults 		= array(
			'post_parent'	=>	$post->ID,
			'post_type'		=>	'page',
			'numberposts'	=> -1,
			'post_status'	=> 'published',
			'order'			=> 'ASC',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		//First check if current page has a parent (is at least one page deep)
		if( isset($post->post_parent) && !empty($post->post_parent) ) {
		
			//Get current post/page children
			$child_posts = get_children( $args );
			
			//If is empty try and see of this pages parent has any children
			if( empty($child_posts) ) {
				
				$args['post_parent'] = $post->post_parent;
				
				$child_posts = get_children( $args );
				
			}
			
			//Loop any child posts and build html for drop down menu
			if( !empty($child_posts) && is_array($child_posts) ) {
				
				//Start html
				$html_output = '<select id="prso-mobile-deep-nav">';
				
				//Add first option
				$html_output.= '<option value="">Select a section</option>';
				
				ob_start();
				
				//Loop posts and build dropdown html
				foreach( $child_posts as $post_id => $PostObj ) {
					
					?>
					<option value="<?php echo get_permalink( $post_id ); ?>"><?php esc_attr_e( $PostObj->post_title ); ?></option>
					<?php
					
				}
				
				//Add jquery for menu
				?>
				<script type="text/javascript">
					jQuery.noConflict();
					(function($) {
					
						$(document).ready(function(){
							var prsoDeepNav = $('#prso-mobile-deep-nav');
							
							//Detect change on mobile deep nav and redirect user to that page url
							if( prsoDeepNav.length > 0 ) {
								prsoDeepNav.change(function(){
									window.location = $(this).find('option:selected').val();
								});
							}
							
						});
					
					})(jQuery);
				</script>
				<?php
				
				$html_output.= ob_get_contents();
				ob_end_clean();
				
				//End html
				$html_output.= '</select>';
				
			}
			
		}
		
		//Echo out the html
		if( !empty($html_output) ) {
			$html_output = apply_filters( 'prso_deep_mobile_nav_html', $html_output, $args );
			echo $html_output;
		}
	}
	
	
	
	/**
	* add_action
	* 
	* Helper to deal with Wordpress add_action requests. Checks to make sure that the action is not
	* duplicated if a class is instantiated multiple times.
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	private function add_action( $tag = NULL, $method = NULL, $priority = 10, $accepted_args = NULL ) {
		
		if( isset($tag,$method) ) {
			//Check that action has not already been added
			if( !has_action($tag) ) {
				add_action( $tag, array($this, $method), $priority, $accepted_args );
			}
		}
		
	}
	
	/**
	* add_filter
	* 
	* Helper to deal with Wordpress add_filter requests. Checks to make sure that the filter is not
	* duplicated if a class is instantiated multiple times.
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	private function add_filter( $tag = NULL, $method = NULL, $priority = 10, $accepted_args = NULL ) {
		
		if( isset($tag,$method) ) {
			//Check that action has not already been added
			if( !has_filter($tag) ) {
				add_filter( $tag, array($this, $method), $priority, $accepted_args );
			}
		}
		
	}
	
}