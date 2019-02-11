<?php
/**
 * Social Helper class file.
 *
 *
 */
 
class SocialHelper {
	
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
	static function get_social_button( $type = NULL ) {
		
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
	
	static function get_share_url( $args = array() ) {
		
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
						$url = wp_get_shortlink( $post->ID );
					}
					
					$output = "http://www.facebook.com/sharer.php?u={$url}";
					
					break;
				case 'twitter':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = wp_get_shortlink( $post->ID );
					}
					
					//Check for text
					if( isset($args['text']) ) {
						$text = urlencode( $args['text'] );
					} else {
						$text = urlencode( get_the_title( $post->ID ) );
					}
					
					$output = "http://twitter.com/share?url={$url}&text={$text}";
					
					break;
				case 'linkedin':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = wp_get_shortlink( $post->ID );
					}
					
					//Check for text
					if( isset($args['title']) ) {
						$title = urlencode( $args['title'] );
					} else {
						$title = urlencode( get_the_title( $post->ID ) );
					}
					
					//Check for excerpt
					if( isset($args['excerpt']) ) {
						$excerpt = urlencode( $args['excerpt'] );
					} else {
						$excerpt = urlencode( $post->post_excerpt );
					}
					
					$output = "https://www.linkedin.com/shareArticle?mini=true&url={$url}&title={$title}&summary={$excerpt}";
					
					break;
				case 'pinterest':
					
					$output = "javascript:void((function()%7Bvar%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)%7D)());";
					
					break;
				case 'google':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = wp_get_shortlink( $post->ID );
					}
					
					$output = "https://plus.google.com/share?url={$url}";
					
					break;
				case 'email':
				
					//Check for permalink override
					if( isset($args['permalink']) ) {
						$url = $args['permalink'];
					} else {
						$url = wp_get_shortlink( $post->ID );
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
	
	static function get_share_count( $args = array() ) {
		
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
	
	//Helper to get twitter feed using twitter api v1.1
	static function get_twitter_feed( $api_keys = array(), $api_url = 'statuses/user_timeline', $params = array() ) {
		
		//Init vars
		$connection			= NULL;
		$content 			= NULL;
		
		$api_keys = wp_parse_args( $api_keys, 
			array(
				'oauth_token'			=>	NULL,
				'oauth_token_secret'	=>	NULL,
				'consumber_key'			=>	NULL,
				'consumer_secret'		=>	NULL,
			)
		);
		
		extract( $api_keys );
		
		//Include libraray
		$lib_inc_path = dirname(__FILE__) . '/includes/twitteroauth-master/twitteroauth/twitteroauth.php';
			
		require_once( $lib_inc_path );
		
		$connection = new TwitterOAuth( $consumber_key, $consumer_secret, $oauth_token, $oauth_token_secret );
		
		$content = $connection->get( $api_url, $params );
		
		return $content;	
	}
	
	static function twitter_convert_urls( $tweet, $targetBlank = true, $linkMaxLen=250 ){
		
		// The target
		$target=$targetBlank ? " target=\"_blank\" " : "";
		 
		//Convert urls to <a> links
		$tweet = preg_replace("/([\w]+\:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/", "<a {$target} href=\"$1\">$1</a>", $tweet);

		//Convert hashtags to twitter searches in <a> links
		$tweet = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a {$target} href=\"http://twitter.com/search?q=$1\">#$1</a>", $tweet);

		//Convert attags to twitter profiles in &lt;a&gt; links
		$tweet = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a {$target} href=\"http://www.twitter.com/$1\">@$1</a>", $tweet);
		
		// return the status
		return $tweet;
		
	}
	
	static function get_facebook_feed( $api_keys = array(), $page_id = NULL ) {
		
		//Init vars
		$config = array();
		$content = NULL;
		
		$api_keys = wp_parse_args( $api_keys, 
			array(
				'appId'			=>	NULL,
				'secret'		=>	NULL,
				'fileUpload'	=>	FALSE,
			)
		);
		
		extract( $api_keys );
		
		// include the facebook sdk
		$lib_inc_path = dirname(__FILE__) . '/includes/facebook-php-sdk-master/src/facebook.php';
		
		require_once( $lib_inc_path );
		
	    // connect to app
	    $config['appId'] 		= $appId;
	    $config['secret'] 		= $secret;
	    $config['fileUpload'] 	= $fileUpload; // optional
	
	    // instantiate
	    $facebook = new Facebook($config);
	
	    // now we can access various parts of the graph, starting with the feed
	    $content = $facebook->api("/" . $page_id . "/feed");
		
		return $content;	
	}
	
	static function get_pinterest_feed( $api_keys = array() ) {
		
		//Init vars
		$config 	= array();
		$content 	= array();
		
		$api_keys = wp_parse_args( $api_keys, 
			array(
				'pin_id'		=>	NULL,
				'board'			=>	NULL
			)
		);
		
		extract( $api_keys );
		
		$pin_feed	= "http://pinterest.com/" . $pin_id . "/" . $board . "/rss";
		$result_pin = SocialHelper::fetchPinterest( $pin_feed );
		
		$pin_count = 0;
		foreach($result_pin->channel->item as $key => $item) {

				$item_description = $item->description;
				
				$regex = "/<img((?:[^<>])*)>/";
				
				preg_match($regex, $item_description, $matches);
				
				$content[$pin_count]['image'] 		= $matches[0];
				
				//Get large images not thumbnails
				$content[$pin_count]['image'] = str_replace('192x', '736x', $content[$pin_count]['image']);
				
				//Get image src too		
				preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content[$pin_count]['image'], $matches);
				
				$content[$pin_count]['image_src']	= $matches[1];
				
				$content[$pin_count]['description']	= preg_replace($regex, '', $item_description);
				$content[$pin_count]['link'] 		= (string) $item->link;
				$content[$pin_count]['title'] 		= (string) $item->title;
				$content[$pin_count]['timestamp'] 	= strtotime( $item->pubDate );
					
				$pin_count++;
		}
		
		return $content;	
	}
	
	static function fetchPinterest( $url ) {
		
		 $result = file_get_contents($url);
		 $x	= simplexml_load_string($result);
		 
		 return $x;
		
	}
	
	/*
	
	If you don't have a registered client already, create one at: http://instagram.com/developer/clients/manage/

	Put your Client ID and the redirect url of your client at the following URL and open it:
	
	https://instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=token
	
	LOOK UP USER ID:
	
	http://jelled.com/instagram/lookup-user-id#
	
	*/
	static function get_instagram_feed( $api_keys = array() ) {
		
		//Init vars
		$config 	= array();
		$content 	= array();
		$result 	= array();
		
		$api_keys = wp_parse_args( $api_keys, 
			array(
				'access_token'	=>	NULL,
				'display_size'	=>	'thumbnail', // low_resolution, standard_resolution
				'count'			=>	10,
				'user_id'		=>	NULL
			)
		);
		
		extract( $api_keys );
		
		$result 	= SocialHelper::fetch_instagram_data("https://api.instagram.com/v1/users/{$user_id}/media/recent?count={$count}&display_size={$display_size}&access_token={$access_token}");
		
		$content 	= json_decode($result);
		
		return $content;	
	}
	
	static function fetch_instagram_data($url){
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }
	
	static function nice_date( $time ) {
		
		$time = strtotime( $time );
		
	    $time = time() - $time; // to get the time since that moment
	
	    $tokens = array (
	        31536000 => 'year',
	        2592000 => 'month',
	        604800 => 'wk',
	        86400 => 'd',
	        3600 => 'h',
	        60 => 'm',
	        1 => 's'
	    );
	
	    foreach ($tokens as $unit => $text) {
	        if ($time < $unit) continue;
	        $numberOfUnits = floor($time / $unit);
	        return $numberOfUnits.' '.$text;
	    }
		
	}
	
}