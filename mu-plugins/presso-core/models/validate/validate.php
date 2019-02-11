<?php
/**
 * Validation Model class file.
 *
 * Simplifies the validation and sanitation of data.
 *
 * CONTENTS:
 *
 * 
 * 
 */
 
class PrsoCoreValidateModel {
 	
 	//Cache regex for data type matching
 	private $preg_str 	= '/\_?(string|str)/';
	private $preg_int	= '/\_?(integer|int)/';
	private $preg_html	= '/\_?(html)/';
	private $preg_bool	= '/\_?(bool)/';
 	
 	//Cache regex for data validation
 	private $regex_url			= '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';
 	private $regex_password		= '#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#'; //Ensure pswrd has one [#.-_,$%&!] & cap & lower & number & 8-20 long
 	private $regex_phone_us 	= '/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/';
 	private $regex_zip_us		= '/(^\d{5}$)|(^\d{5}-\d{4}$)/';
 	private $regex_postal_can	= '/^([a-ceghj-npr-tv-z]){1}[0-9]{1}[a-ceghj-npr-tv-z]{1}[0-9]{1}[a-ceghj-npr-tv-z]{1}[0-9]{1}$/i';
 	
 	function __construct() {
 		//Add custom wordpress action hooks for this model
		$this->model_custom_wp_actions();
		
		//Add custom wordpress filter hooks for this model
		$this->model_custom_wp_filters();
 	}
 	
 	private function model_custom_wp_actions() {
		
		//WP Action hook for $this->METHODNAME
		//$this->add_action( HOOKTAG, METHODNAME, PRIORITY, ACCEPTED_ARGS );
		
	}
	
	private function model_custom_wp_filters() {
		
		//WP Filter hook for $this->METHODNAME
		//$this->add_filter( HOOKTAG, METHODNAME, PRIORITY, ACCEPTED_ARGS );
		
		//WP Filter hook for $this->validate
		$this->add_filter( 'prso_core_validate', 'validate', 1, 2 );
		
		//WP Filter hook for $this->sanitize
		$this->add_filter( 'prso_core_sanitize', 'sanitize', 1, 2 );
		
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
 	
 	/**
	* sanitize
	* 
	* Can sanitize 4 main data types, strings, integers, html, bool.
	* Can sanitize single strings at a time or entire data arrays (1 deep only)
	*
	* How to use:
	* STRINGS -> Pass the data var then you must declare the data type in $type. This can be
	* 'string|str', 'integer|int', 'html', 'bool'. You can manually provide the type or if you are following
	* the naming convension for your field id slugs place _int, _integer, _string, _str, _html, _bool at the end
	* of your field id slug and the method will detect the data type.
	*
	* If the type can't be detected in the field name or was not provided then the method will sanitize the field as a string,
	* striping out any html and javascript tags. So be sure to follow the naming convension.
	*
	* E.G
	* global $PrsoValidate;
	* $data_array = $PrsoValidate->sanitize( $data ); //Validate entire array - KEYS MUST FOLLOW FIELD NAMING CONVENSION
	*
	* OR
	*
	* global $PrsoValidate;
	* foreach( $data as $key => $val ) { $PrsoValidate->sanitize( $val, $key ); } //If following field naming convension
	* 
	* @param	mixed	$data
	* @param	str		$type
	* @return	mixed	wp_error on major errors OR the data passed on success
	* @access 	public
	* @author	Ben Moody
	*/
	public function sanitize( $data, $type = null ) {
		
		//Init vars
		$clean 		= false; //Cache sanitized data - mixed
		$preg_str 	= $this->preg_str;
		$preg_int	= $this->preg_int;
		$preg_html	= $this->preg_html;
		$preg_bool	= $this->preg_bool;
		
		//Detect data type - string or array
		if( isset($data) ) {
		
			if( is_string($data) ) {
			
				//Data is a string lets see if type has been provided
				if( isset($type) ) {
				
					//Preg match data type to detect supported - default to string on error
					if( preg_match( $preg_str, $type ) ) {
						//Strip any scripts and html tags
						$clean = sanitize_text_field( $data );
					} elseif( preg_match( $preg_int, $type ) ) {
						//Cast as integer
						$clean = intval( $data );
					} elseif( preg_match( $preg_html, $type ) ) {
						//Strip evil tags and maintain post standard html tags
						$clean = wp_kses_post( $data );
					} elseif( preg_match( $preg_bool, $type ) ) {
						//Filter var with FILTER_VALIDATE_BOOLEAN
						if( filter_var( $data, FILTER_VALIDATE_BOOLEAN ) ) {
							$clean = $data;
						} else {
							$clean = false;
						}
					} else {
						//Strip any scripts and html tags
						$clean = sanitize_text_field( $data );
					}
				
				} else {
					//Data type was not provided so lets just go ahead and strip any evil scripts
					$clean = wp_kses_post( $data );
				}
				
			} elseif( is_array($data) ) {
				
				//Data is an array, loop and sanitize data
				foreach( $data as $key => $value ) {
				
					//Check for multidimensional arrays
					if( !is_array($value) ) {
						
						//Preg match data type to detect supported - default to string on error
						if( preg_match( $preg_str, $key ) ) {
							//Strip any scripts and html tags
							$clean[$key] = sanitize_text_field( $value );
						} elseif( preg_match( $preg_int, $key ) ) {
							//Cast as integer
							$clean[$key] = intval( $value );
						} elseif( preg_match( $preg_html, $key ) ) {
							//Strip evil tags and maintain post standard html tags
							$clean[$key] = wp_kses_post( $value );
						} elseif( preg_match( $preg_bool, $key ) ) {
							//Filter var with FILTER_VALIDATE_BOOLEAN
							if( filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ) {
								$clean[$key] = $value;
							} else {
								$clean[$key] = null;
							}
						} else {
							//Strip any scripts and html tags
							$clean[$key] = sanitize_text_field( $value );
						}
						
					} elseif( is_array($value) ) {
						
						//Loop second dimension
						foreach( $value as $key_b => $value_b ) {
							
							//Check for multidimensional arrays
							if( !is_array($value_b) ) {
							
								//Preg match data type to detect supported - default to string on error
								if( preg_match( $preg_str, $key_b ) ) {
									//Strip any scripts and html tags
									$clean[$key][$key_b] = sanitize_text_field( $value_b );
								} elseif( preg_match( $preg_int, $key_b ) ) {
									//Cast as integer
									$clean[$key][$key_b] = intval( $value_b );
								} elseif( preg_match( $preg_html, $key_b ) ) {
									//Strip evil tags and maintain post standard html tags
									$clean[$key][$key_b] = wp_kses_post( $value_b );
								} elseif( preg_match( $preg_bool, $key_b ) ) {
									//Filter var with FILTER_VALIDATE_BOOLEAN
									if( filter_var( $value_b, FILTER_VALIDATE_BOOLEAN ) ) {
										$clean[$key][$key_b] = $value_b;
									} else {
										$clean[$key][$key_b] = null;
									}
								} else {
									//Strip any scripts and html tags
									$clean[$key][$key_b] = sanitize_text_field( $value_b );
								}
								
							} elseif( is_array($value_b) ) {
								
								//Loop third dimension
								foreach( $value_b as $key_c => $value_c ) {
									
									//Preg match data type to detect supported - default to string on error
									if( preg_match( $preg_str, $key_c ) ) {
										//Strip any scripts and html tags
										$clean[$key][$key_b][$key_c] = sanitize_text_field( $value_c );
									} elseif( preg_match( $preg_int, $key_c ) ) {
										//Cast as integer
										$clean[$key][$key_b][$key_c] = intval( $value_c );
									} elseif( preg_match( $preg_html, $key_c ) ) {
										//Strip evil tags and maintain post standard html tags
										$clean[$key][$key_b][$key_c] = wp_kses_post( $value_c );
									} elseif( preg_match( $preg_bool, $key_c ) ) {
										//Filter var with FILTER_VALIDATE_boolEAN
										if( filter_var( $value_c, FILTER_VALIDATE_boolEAN ) ) {
											$clean[$key][$key_b][$key_c] = $value_c;
										} else {
											$clean[$key][$key_b][$key_c] = null;
										}
									} else {
										//Strip any scripts and html tags
										$clean[$key][$key_b][$key_c] = sanitize_text_field( $value_c );
									}
								
								}
								
							}
							
						}
						
					}
					
					
					
				}
				
			}
			
		}
		
		unset($data);
		
		if( !$clean ) {
			//Critical error
			wp_die( __( 'There was an error while trying to sanitize your data.' ) );
		} else {
			return $clean;
		}
	}
	
	/**
	* validate
	* 
	* Method handles field validation as well as calling flash helper to output
	* individual field validation error messages in the form view.
	*
	* This method not only validates fields in the $validate_array but also
	* sanitizes all the data even if not in the validate_array.
	*
	* How to use:
	*	Call the method and pass your data array. If you wish to validate some fields in the data array
	*	then pass validate_array as such:
	*
	*		$validate_array['field_slug'] => array( 'nice_name' => 'Email Address', 'type' => 'email', 'message' => 'Invalid Email', 'empty' => true,'regex' => null );
	*	
	*	If regex arg is provided then the method will ignore the type arg and validate the field data using the regex provided.
	*
	*	NOTE the data array must have field names that follow the pressoholics field name convension 
	*	EG field_email_address_email
	*		{field_name}_{type} another example field_title_html, or field_website_url
	* 
	*	ON ERROR
	*	If a field fails validation its value in data in nulled and a new key is added to the master data array ['error'] and set to true.
	*	This is passed back along with the master data array allowing you to detect if there was a failer in validation.
	*
	* @param	data			array	Master form data array
	* @param	validate_array	array	Array of fields to validate in master data array SEE How to use ABOVE
	* @return	data	array
	* @access 	public
	* @author	Ben Moody
	*/
	public function validate( $data = NULL, $validate_array = array() ) {
		
		//Init vars
		$failed = array(); //Cache all fields that failed validation
		
		//FIRST: Sanitize the data array
		$data = $this->sanitize( $data );
		
		if( !empty($validate_array) ) {		
			//NEXT: Loop the validate array and do some validation
			foreach( $validate_array as $field_slug => $validation_args ) {
				//FIRST Check to see if this field is empty and if it is allowed to be
				if( empty( $data[ $field_slug ] ) ) {
					//Is this field allowed to be empty?
					if( isset( $validation_args['empty'] ) && $validation_args['empty'] ){
						//No worries it's allowed to be empty
					} else {
						//Oh on, not allowed to be empty
						$data[ $field_slug ] = null;
						//Also lets be nice and add a user field message
						$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
						//Add error to the core data array for callback
						$data['error'] = true;
					}
					
				} elseif( isset( $validation_args['regex'] ) && !empty( $validation_args['regex'] ) && is_string( $validation_args['regex'] ) ) {
				//NEXT check to see if a regex has been provided to validate against
				
					//Match the field data with the regex provided
					if( !preg_match( $validation_args['regex'], $data[ $field_slug ] ) ) {
						//Failed! so lets empty the field
						$data[ $field_slug ] = null;
						//Also lets be nice and add a user field message
						$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
						//Add error to the core data array for callback
						$data['error'] = true;
					}
					
				} else {
				//FINALLY Check field against the type string provided
				
					//Switch $type and conduct appropriate validation method
					$type = strtolower( $validation_args['type'] );
					
					switch( $type ) {
						case 'email':
							if( !is_email( $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						case 'integer':
							if( !intval( $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						case 'url':
							if( !preg_match( $this->regex_url, $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						case 'password':
							if( !preg_match( $this->regex_password, $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						case 'phone_us':
							if( !preg_match( $this->regex_phone_us, $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						case 'zip':
							if( !preg_match( $this->regex_zip_us, $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						case 'postal_can':
							if( !preg_match( $this->regex_postal_can, $data[ $field_slug ] ) ) {
								//Failed! so lets empty the field
								$data[ $field_slug ] = null;
								//Also lets be nice and add a user field message
								$failed[ $field_slug ] = array( 'message' => $validation_args['message'] );
								//Add error to the core data array for callback
								$data['error'] = true;
							}
						break;
						default:
							//Cast value as string
							if( !is_array($data[ $field_slug ]) ) {
								$data[ $field_slug ] = (string) $data[ $field_slug ];
							}
						break;
					}
					
				}
			}
		}
		
		//Check if we need to pass any validation field messages to the pressoholics flash helper
		if( !empty($failed) ) {
			//$PrsoFlash->set_validate_flash( $failed );
			do_action( 'prso_core_set_validation_flash', $failed );
		}
		
		return $data;
	}
 	
}