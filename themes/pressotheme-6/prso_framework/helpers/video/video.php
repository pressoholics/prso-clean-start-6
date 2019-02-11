<?php
/**
 * Video Helper
 *
 * Simplifies the display of videos within a theme both via API's and self hosted.
 *
 * CONTENTS:
 *
 *	youtube_thumb( $youtube_id = null, $thumb_index = 0 )
 *	youtube_api( $video_id = null, $return = null )
 *	vimeo_api( $vimeo_id = null )
 * 
 */
class VideoHelper {

	function __construct() {
	
	}
	
	/**
	* youtube_thumb
	*
	* Used to get a youtube video screenshot thumbnail from img.youtube.com
	*
	* @param	str		$youtube_id - Youtube video id
	* $param	str		$thumb_index - which thumbnail size to return - 1,2,3 or 0 Full size
	* @return	str		url to the thumbnail img
	*/
	public function youtube_thumb( $youtube_id = null, $thumb_index = 0 ) {
		
		//Init vars
		$output 				= null;
		$_youtube_url_pre 		= 'http://img.youtube.com/vi/';
		$_youtube_url_append 	= '/' . $thumb_index . '.jpg';
		
		if( isset($youtube_id) ) {
			
			$output = $_youtube_url_pre . $youtube_id . $_youtube_url_append;
			
		}
		
		return $output;
	}
 
	/**
	* youtube_api
	*
	* Used to get a youtube video screenshot thumbnail from img.youtube.com
	*
	* @param	str		$youtube_id - Youtube video id
	* $param	str		$thumb_index - which thumbnail size to return - 1,2,3 or 0 Full size
	* @return	str		url to the thumbnail img
	*/
	public function youtube_api( $video_id = null, $return = null ) {
		
		//Init vars
		$data	= array();
		$output = false;
		
		if( isset($video_id) ) {
			
			//The Youtube's API url
			if( !defined( 'YT_API_URL' ) ) {
				define('YT_API_URL', 'http://gdata.youtube.com/feeds/api/videos?q=');
			}
		 
		//Using cURL php extension to make the request to youtube API
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, YT_API_URL . $video_id);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//$feed holds a rss feed xml returned by youtube API
		$feed = curl_exec($ch);
		curl_close($ch);
		 
		//Using SimpleXML to parse youtube's feed
		$xml = simplexml_load_string($feed);
		$entry = $xml->entry[0];
		$media = $entry->children('media', true);
		$group = $media[0];
		 
		$data['title'] 		= $group->title;//$title: The video title
		$data['desc'] 		= $group->description;//$desc: The video description
		$data['keywords'] 	= $group->keywords;//$vid_keywords: The video keywords
		$thumb 				= $group->thumbnail[0];//There are 4 thumbnails, The first is the largest.
		
		//$thumb_url: the url of the thumbnail. $thumb_width: thumbnail width in pixels.
		//$thumb_height: thumbnail height in pixels. $thumb_time: thumbnail time in the vií?¥Ë?í?Œ?deo
		
		//Get thumb attributes 
		list($thumb_url, $thumb_width, $thumb_height, $thumb_time) = $thumb->attributes();
		$content_attributes = $group->content->attributes();
		
		//Cache thumb attributes in data array
		$data['thumb']['url'] 		= $thumb_url;
		$data['thumb']['width'] 	= $thumb_width;
		$data['thumb']['height'] 	= $thumb_height;
		$data['thumb']['time'] 		= $thumb_time;
		
		//$vid_duration: the duration of the video in seconds. Ex.: 192.
		$data['duration_sec'] 	= $content_attributes['duration'];
		
		//$duration_formatted: the duration of the video formatted in "mm:ss". Ex.:01:54
		$data['duration_nice'] 	= str_pad(floor($data['duration_sec']/60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($data['duration_sec']%60, 2, '0', STR_PAD_LEFT);
			
			
			$output = $data;
			
		}
		
		return $output;
	}
 
	/**
	* vimeo_api
	*
	* Used to get data array on vimeo video
	*
	* Return array key:
	* [id]
	* [title]
	* [description]
	* [url]
	* [thumbnail_] - small, medium, large
	* [user_name]
	* [user_url]
	* [user_portrait_small] - medium, large, huge
	* [stats_number_of_likes]
	* [stats_number_of_comments]
	* [duration]
	* [width]
	* [height]
	* [tags]
	* [embed_privacy]
	*
	* @param	str		$vimeo_id - Vimeo video id
	* @return	array	Array of video data
	*/
	public function vimeo_api( $vimeo_id = null ) {
		
		//Init vars
		$vimeo_api_url = "http://vimeo.com/api/v2/video/";
		$api_return = null;
		$output = array();
		
		if( isset($vimeo_id) ) {
			
			//Make request to vimeo api
			$api_return = file_get_contents("{$vimeo_api_url}{$vimeo_id}.php");
			
			//Unserialize returned data
			if( !empty($api_return) ) {
				$output = unserialize( $api_return );
				
				//Format duration before return (in secs)
				$output[0]['duration'] = round( $output[0]['duration']/60 ) . ' minutes';
				
				return $output[0];
			}
			
		}
		
		return false;
	}

}