<?php
/**
 * Html Helper class file.
 *
 * Simplifies the construction of HTML elements.
 *
 * CONTENTS:
 *
 * 1. center_thumbnail		- filter 'prso_center_thumbnail'
 * 2. get_the_excerpt		- action 'prso_get_the_excerpt'
 * 3. trim_string			- filter 'prso_trim_string' 
 * 4. get_social_button		- filter 'prso_share_button'
 * 5. detect_ie				- filter 'prso_detect_ie'
 *
 */
 
class HtmlHelper {
 	
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
 		* 1. center_thumbnail
 		* 	 Returns contents for style attr for img
 		*/
 		$this->add_filter( 'prso_center_thumbnail', 'center_thumbnail', 10, 3 );
 		
 		/**
 		* 2. get_the_excerpt
 		* 	 Detects if post content is using 'more' tag and echos the_content OR
 		*	 if 'more' tag is not used then defaults to calling the_excerpt()
 		*/
 		$this->add_action( 'prso_get_the_excerpt', 'get_the_excerpt', 10 );
 		
 		/**
 		* 3. trim_string
 		* 	 Filter a string and trim it to a number of words
 		*/
 		$this->add_filter( 'prso_trim_string', 'trim_string', 10, 3 );
 		
 		/**
 		* 4. get_social_button
 		* 	 Returns html and JS required to output the requested social media share button
 		*/
 		$this->add_filter( 'prso_share_button', 'get_social_button', 10, 1 );
 		
 		/**
 		* 5. detect_ie
 		* 	 Returns true/false if any version of IE is dectected
 		*/
 		$this->add_filter( 'prso_detect_ie', 'detect_ie', 10, 1 );
 		
 		/**
 		* 6. remove_empty_p
 		* 	 Filters out any empty p tags and br created by wp_auto_p
 		*/
 		$this->add_filter( 'prso_remove_p', 'remove_empty_p', 10, 1 );
 		
 		
 		/**
 		* 7. format_shortcode_content
 		* 	 Prepares shortcode content, runs do_shortcode, wpautop, remove_p
 		*/
 		$this->add_filter( 'prso_format_shortcode_content', 'format_shortcode_content', 10, 1 );
 		
 	}
 	
	/**
	* center_thumbnail()
	*
	* Calculates dom width, height and padding required to center a thumbnail based on
	* the confines of the dom element provided in $dom_size array.
	*
	* E.g We want to dynamically output multiple post thumbnails in a grid pattern but
	* each thumbnail is a different size. We are going to use the WP the_post_thumbnail()
	* to output the thumnail within the dom dimensions but they will not all be centered.
	* To center each image we will need to dynamically change the dom styles based on the
	* resulting thumnail returned by the_post_thumbnail().
	*
	* Usage: Call function and pass it the post obj and declare the dimensions of your img dom
	* cache the resulting style string and echo it into style="" of your dom.
	*
	* @param	array	$args - any get_posts args you wish to customize
	* @return	array	$_posts	- posts array returned by wp get_posts
	*/
	public function center_thumbnail( $Post = null, $dom_size = array(), $unit = 'px' ) {
	
		//Init vars
		$_dom_style		= null;
		$_img_data 		= array();
		$_img_height 	= null;
		$_img_width		= null;
		
		if( isset($Post) && !empty($dom_size) ) {
			if( isset($dom_size['width']) && isset($dom_size['height']) ) {
				
				//Setup post data from post obj
				setup_postdata($Post);
				
				//Get thumbnail image data based on dom height and width provided
				$_img_data 	= wp_get_attachment_image_src( 
					get_post_thumbnail_id(), 
					array($dom_size['width'], $dom_size['height']) 
				);
				
				//Cache thumbnail height and width
				$_img_height = $_img_data[2];
				$_img_width  = $_img_data[1];
				
				//Calculate amount of top and left padding req to center thumbnail in dom object
				$_dom_padding_top	= 0;
				$_dom_padding_left	= 0;
				//Calc top padding
				if( $_img_height !== $dom_size['height'] ){
					$_dom_padding_top = ($dom_size['height'] - $_img_height)/2;
				
					//Recalculate dom height based on padding
					$dom_size['height'] = $dom_size['height'] - $_dom_padding_top;
				}
				
				//Calc left padding
				if( $_img_width !== $dom_size['width'] ){
					$_dom_padding_left = ($dom_size['width'] - $_img_width)/2;
				
					//Recalculate div min width based on padding
					$dom_size['width'] = $dom_size['width'] - $_dom_padding_left;
				}
				
				//Create dom style data string
				ob_start();
				
				echo 'min-height:' . $dom_size['height'] . $unit .';';
				echo 'max-height:' . $dom_size['height'] . $unit .';';
				echo 'min-width:' . $dom_size['width'] . $unit .';';
				echo 'max-width:' . $dom_size['width'] . $unit .';';
				echo 'padding:' . $_dom_padding_top . $unit .' 0 0 ' . $_dom_padding_left . $unit .';';
				echo 'overflow:hidden;';
				
				$_dom_style = ob_get_contents();
				
				ob_end_clean();
			}
		}
		
		return $_dom_style;
	}
	
	/**
	* get_the_excerpt()
	*
	* Action:: 'prso_get_the_excerpt'
	*
	* Allows users to make use of the 'more' tag in post content to
	* set where the excerpt should end.
	*
	* If a post doesn't use 'more' tag then the_excerpt will be used as a backup
	*
	* @access	public
	*/
	public function get_the_excerpt() {
		global $post;
		
		//Detect if current post content contains a more tag if not then use excerpt
		if( isset($post->post_content) && preg_match('/<!--more(.*?)?-->/', $post->post_content) ) {
			the_content('', TRUE);
		} else {
			the_excerpt();
		}
		
	}
	
	/**
	* trim_string
	*
	* Filter:: 'prso_trim_string'
	* 
	* Trims a string to the number of words set by max_length arg.
	* Can also pass html to place a the end of the string via end_cap
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function trim_string( $string, $max_length = 75, $end_cap = ' &hellip;' ) {
    
	    //Init vars
	    $length 	= 0;
	    $max_length	= 42;
		    
	    $length = strlen( $string );
    
	    if( $length >= $max_length ) {
		    $string = substr( $string, 0, $max_length ) . $end_cap;
	    }
	    
	    return wp_kses_post( $string );
	}
	
	/**
	* get_social_button
	* 
	* Returns html and js required to display either 'Twitter' or 'Facebook'
	* Like buttons
	*
	* @param	string	$type	-	'facebook', 'twitter'
	* @return	string
	* @access 	public
	* @author	Ben Moody
	*/
	public function get_social_button( $type = NULL ) {
		
		//Init vars
		$path 	= NULL;
		$url  	= NULL;
		$output	= NULL;
		
		//Cache path of current page
		if( isset($_SERVER['REQUEST_URI']) ) {
			$path = $_SERVER['REQUEST_URI'];
			$url = get_site_url() . $path;
		}
		
		if( $type === 'twitter' && isset($url) ) {
			
			ob_start()
			?>
			<div id="twitter" class="social-button">
				<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $url; ?>" data-via="pressoholics"></a>
				<script>
				jQuery(window).load(function(){
					!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
				});
				</script>
			</div>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			
		} elseif( $type === 'facebook' && isset($url) ) {
			
			ob_start()
			?>
			<div id="facebook" class="social-button">
				<div id="fb-root"></div>
				<script>
					jQuery(window).load(function(){
						(function(d, s, id) {
						  var js, fjs = d.getElementsByTagName(s)[0];
						  if (d.getElementById(id)) return;
						  js = d.createElement(s); js.id = id;
						  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
						  fjs.parentNode.insertBefore(js, fjs);
						}(document, 'script', 'facebook-jssdk'));
					});
				</script>
				<div class="fb-like" data-href="<?php echo $url; ?>" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>
			</div>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			
		}
		
		return $output;
	}
	
	static function prso_theme_get_share_url( $args = array() ) {
		
		//Init vars
		global $post;
		$url = NULL;
		$text = NULL;
		$output = NULL;
		$email_subject = NULL;
		$email_body = NULL;
		
		if( isset($args['service']) ) {
			
			switch( $args['service'] ) {
				
				case 'facebook':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = get_permalink( $post->ID );
					}
					
					$output = "http://www.facebook.com/sharer.php?u={$url}";
					
					break;
				case 'twitter':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = get_permalink( $post->ID );
					}
					
					//Check for text
					if( isset($args['text']) ) {
						$text = urlencode( $args['text'] );
					} else {
						$text = urlencode( get_the_title( $post->ID ) );
					}
					
					$output = "http://twitter.com/share?url={$url}&text={$text}";
					
					break;
				case 'pinterest':
					
					$output = "javascript:void((function()%7Bvar%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)%7D)());";
					
					break;
				case 'google':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = get_permalink( $post->ID );
					}
					
					$output = "https://plus.google.com/share?url={$url}";
					
					break;
				case 'email':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = get_permalink( $post->ID );
					}
					
					//Check for email body
					if( isset($args['body']) ) {
						$email_body = "&Body=" . urlencode( $args['body'] ) . " " . $url;
					} else {
						$email_body = "&Body=" . $url;
					}
					
					$email_body = apply_filters( 'prso_email_share_link_body', $email_body, $url, $post->ID );
					
					//Check for email subject
					if( isset($args['subject']) ) {
						$email_subject = "Subject=" . urlencode( $args['subject'] );
					}
					
					$email_subject = apply_filters( 'prso_email_share_link_subject', $email_subject, $url, $post->ID );
					
					$output = "mailto:?{$email_subject}{$email_body}";
					
					break;
					
			}
			
		}
		
		return $output;
	}
	
	static function prso_theme_get_share_count( $args = array() ) {
		
		//Init vars
		global $post;
		$url = NULL;
		$output = NULL;
		
		if( isset($args['service']) ) {
			
			//Check for permalink override
			if( isset($args['permalink']) ) {
				$url = $args['permalink'];
			} else {
				$url = get_permalink( $post->ID );
			}
			
			switch( $args['service'] ) {
				
				case 'facebook':
		
					ob_start();
					?>
					<script type="text/javascript">
					jQuery.noConflict();
					(function($) {
						$(document).ready(function(){
							function fb_count() {
								
								//Init vars
								var el = $('span[data-count="facebook"]');
				                var shares = 0;
				                var url = "<?php echo $url; ?>";
				                
				                //Get share count
				                $.getJSON('http://graph.facebook.com/?callback=?&ids=' + url, function (data) {
				                	
				                	if(typeof data[url]['likes'] != 'undefined') {
				                		
					                	shares = data[url]['likes'];
										
				                	}
									
									//Update dom element with share count
									el.html(shares);
									
				                })
				                
				                
				            }
				            fb_count();
						})
					})(jQuery);
		            </script>
					<?php
					$output = ob_get_contents();
					ob_end_clean();
					
					break;
				case 'twitter':
					
					ob_start();
					?>
					<script type="text/javascript">
					jQuery.noConflict();
					(function($) {
						$(document).ready(function(){
							function fb_count() {
								
								//Init vars
								var el = $('span[data-count="twitter"]');
				                var shares = 0;
				                var url = "<?php echo $url; ?>";
				                
				                //Get share count
				                $.getJSON('http://urls.api.twitter.com/1/urls/count.json?callback=?&url=' + url, function (data) {
				                	
				                	if(typeof data['count'] != 'undefined') {
				                		
					                	shares = data['count'];
										
				                	}
									
									//Update dom element with share count
									el.html(shares);
									
				                })
				                
				                
				            }
				            fb_count();
						})
					})(jQuery);
		            </script>
					<?php
					$output = ob_get_contents();
					ob_end_clean();
					
					break;
				case 'pinterest':
					
					ob_start();
					?>
					<script type="text/javascript">
					jQuery.noConflict();
					(function($) {
						$(document).ready(function(){
							function fb_count() {
								
								//Init vars
								var el = $('span[data-count="pinterest"]');
				                var shares = 0;
				                var url = "<?php echo $url; ?>";
				                
				                //Get share count
				                $.getJSON('http://api.pinterest.com/v1/urls/count.json?callback=?&url=' + url, function (data) {
				                	
				                	if(typeof data['count'] != 'undefined') {
				                		
					                	shares = data['count'];
										
				                	}
									
									//Update dom element with share count
									el.html(shares);
									
				                })
				                
				                
				            }
				            fb_count();
						})
					})(jQuery);
		            </script>
					<?php
					$output = ob_get_contents();
					ob_end_clean();
					
					break;
			}
			
		}
		
		return $output;
	}
	
	/**
	* detect_ie
	*
	* Filter:: 'prso_detect_ie'
	* 
	* Detects any version of IE and returns true/false
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function detect_ie( $result = NULL ) {
    
	    $IE6 = (ereg('MSIE 6',$_SERVER['HTTP_USER_AGENT'])) ? true : false;
        $IE7 = (ereg('MSIE 7',$_SERVER['HTTP_USER_AGENT'])) ? true : false;
        $IE8 = (ereg('MSIE 8',$_SERVER['HTTP_USER_AGENT'])) ? true : false;
                
        return $IE6 + $IE7 + $IE8;
	}
	
	public function remove_empty_p( $content ){

	    $content = force_balance_tags($content);
	    $content = preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
	    $content = shortcode_unautop( $content );
	    
	    return preg_replace('#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content);
	}
	
	public function format_shortcode_content( $content ) {
		
		//Format content
		$content = do_shortcode( $content );
		$content = wpautop( trim($content) );
		$content = apply_filters( 'prso_remove_p', $content );
		
		//Sanitize content - allow iframe for video embeds
		$allowed_tags = wp_kses_allowed_html( 'post' );
		$allowed_tags["iframe"] = array( //add new allowed tags
			"src" 				=> array(),
			"height" 			=> array(),
			"width" 			=> array(),
			"frameborder" 		=> array(),
			"allowfullscreen" 	=> array()
		);
		
		$content = wp_kses( $content, $allowed_tags );
		
		return $content;
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