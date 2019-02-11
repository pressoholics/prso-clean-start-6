<?php
/**
 * Mailchimp Model class file.
 *
 * Acts as a framework to allow prso plugins to interact with Mailchimp API via
 * wordpress actions and filters.
 *
 * CONTENTS:
 *
 * 
 * 
 */
 
class PrsoCoreMailchimpModel {
	
	private $data = array();
	
	//API Key - see http://admin.mailchimp.com/account/api
    private $apikey = NULL;
    
    // A List Id to run examples against. use lists() to view all
    // Also, login to MC account, go to List, then List Tools, and look for the List ID entry
    private $listId = NULL;
    
    // A Campaign Id to run examples against. use campaigns() to view all
    private $campaignId = NULL;

    //just used in xml-rpc examples
    private $apiUrl = 'http://api.mailchimp.com/1.3/';
    
    //File name of mailchimp api
    private $api_file_name = 'MCAPI.class.php';
    
    //Class name of mailchimp api
    private $api_class_name	= 'MCAPI';
	
	//Magic methods set and get
	public function __set( $name, $value ) {
		
		if( isset($this->data) ) {
			$this->data[$name] = $value;
		}
		
	}
	
	public function __get( $name ) {
	
		if( isset($this->data) && array_key_exists( $name, $this->data ) ) {
			return $this->data[$name];
		}
		
		return NULL;
	}
	
	function __construct() {
		
		//Add custom wordpress action hooks for this model
		//$this->model_custom_wp_actions();
		
		//Add custom wordpress filter hooks for this model
		//$this->model_custom_wp_filters();
	}
	
	private function model_custom_wp_actions() {
		
		
		
	}
	
	private function model_custom_wp_filters() {
		
		//Add filter for list_subscribe api action
		$this->add_filter( 'prso_mailchimp_listSubscribe', 'list_subscribe', 1, 2 );
		
	}
	
	/**
	* list_subscribe
	* 
	* Carries out listSubscribe action
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	public function list_subscribe( $result = NULL, $args = array() ) {
		
		//Init vars
		$method		= 'listSubscribe';
		$ClassObj	= NULL;
		$result		= false;
		$defaults 	= array(
			'api_key' 			=> NULL,
			'list_ID'			=> NULL,
			'email'				=> NULL,
			'merge_vars'		=> array(), //see mailchimp api for this one
			'email_type'		=> 'html', //html,text,mobile
			'double_optin'		=> true,
			'update_existing'	=> false, //throw error if exists
			'replace_interests'	=> true,
			'send_welcome'		=> false //if double_optin is false & this is true welcome email is sent
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		if( isset($api_key, $list_ID, $email) ) {
			
			//Sanitize args
			$api_key 	= filter_var( $api_key, FILTER_SANITIZE_STRING );
			$email		= filter_var( $email, FILTER_SANITIZE_EMAIL );
			
			//Get instance of api class
			$ClassObj = $this->get_mailchimp_api_instance( $api_key );
			
			if( is_object($ClassObj) ) {
				if( method_exists( $ClassObj, $method ) ) {
					
					//Call listSubscribe method and pass params
					$result = $ClassObj->$method( $list_ID, $email, $merge_vars, $email_type, $double_optin, $update_existing, $replace_interests, $send_welcome );
					
					//Check for errors
					if( $ClassObj->errorCode ) {
						
						//Add error flag to result array as well as errorCode and errorMessage
						$result = array(
							'result' 		=> false,
							'error_code'	=> $ClassObj->errorCode,
							'error_message'	=> $ClassObj->errorMessage
						);
						
					} else {
						
						$result = array(
							'result' => true
						);
						
					}
					
				}
			}
			
		}
		
		return $result;
			
	}
	
	/**
	* load_mailchimp_api
	* 
	* Finds and includes mailchimp api file
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	private function load_mailchimp_api() {
		
		//Init vars
		$api_file_name 	= $this->api_file_name;
		$api_file_path	= dirname(__FILE__) . "/includes/{$api_file_name}";
		
		if( file_exists($api_file_path) ) {
			include_once($api_file_path);
		}
		
	}
	
	/**
	* get_mailchimp_api_instance
	* 
	* Confirms class exsists and returns api Object
	* 
	* @access 	protected
	* @author	Ben Moody
	*/
	private function get_mailchimp_api_instance( $api_key = NULL ) {
		
		//Init vars
		$class_name = $this->api_class_name;
		$output = false;
		
		if( isset($api_key) ) {
			
			//First include mailchimp api class
			$this->load_mailchimp_api();
			
			if( class_exists($class_name) ) {
				return new $class_name( $api_key );
				exit;
			}
			
		}
		
		return $output;
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
				add_action( $tag, array(&$this, $method), $priority, $accepted_args );
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
				add_filter( $tag, array(&$this, $method), $priority, $accepted_args );
		}
		
	}
	
}