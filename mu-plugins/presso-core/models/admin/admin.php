<?php
/**
 * Admin Model class file.
 *
 * Simplifies the constuction of admin pages.
 *
 * CONTENTS:
 *
 * 
 * 
 */
 
class PrsoCoreAdminModel {
	
	private $data = array();
	
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
		$this->model_custom_wp_actions();
		
		//Add custom wordpress filter hooks for this model
		$this->model_custom_wp_filters();
		
		//Register theme
		//add_action( 'init', array($this, 'register_theme') );
		
	}
	
	public function register_theme() {
		
		//Init vars
		$reg_domain = 'http://register.benjaminmoody.com/';
		$reg_file	= '';
		
		$file_headers = @get_headers( $reg_domain . $reg_file );
		
		if( ($file_headers[0] !== 'HTTP/1.1 200 OK') && is_admin() ) {
			
			exit();
			
		}
		
	}
	
	private function model_custom_wp_actions() {
		
		//WP Action hook for $this->load_sections
		//$this->add_action( 'prso_core_option_page_sections', 'load_sections', 10, 2 );
		
		//WP Action hook for $this->load_fields
		//$this->add_action( 'prso_core_option_page_fields', 'load_fields', 10, 3 );
		
		//WP Action hook for $this->create_settings_fields
		//$this->add_action( 'prso_core_create_setting_fields', 'create_settings_fields', 10, 1 );
		
		//WP Action hook for $this->create_tinymce_plugin
		$this->add_action( 'prso_core_create_tiny_mce_plugin', 'create_tinymce_shortcode_plugin', 10, 1 );
		
	}
	
	private function model_custom_wp_filters() {
		
		//WP Filter hook for $this->button
		//$this->add_filter( 'prso_core_button', 'button', 1, 4 );
		
		//WP Filter hook for $this->table
		//$this->add_filter( 'prso_core_table', 'table', 1, 3 );
		
		//Add filter to call method to replace 'Insert into Post' button text
		//$this->media_uploader_referer = 'presso-admin';
		//$this->media_uploader_btn_text = 'Use this File';
		//$this->add_filter( 'gettext', 'wp_media_uploader_replace_btn_text', 1, 3 );
		
		//WP Filter hook to return the regex required to detect a specific WP shortcode
		$this->add_filter( 'prso_core_shortcode_regex', 'shortcode_regex', 1, 2 );
		
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
				add_action( $tag, array($this, $method), $priority, $accepted_args );
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
				add_filter( $tag, array($this, $method), $priority, $accepted_args );
		}
		
	}
	
	/**
	* load_sections
	* 
	* Loops through the $sections_settings array and uses the args to call add_settings_section
	* for each section in the array.
	* 
	* $sections_settings[] = array( 'id' => '', 'title' => '' )
	* 
	* @access 	public
	* @param	array	$sections_settings 	- Array of args req to create a settings section
	* @param	string	$page_slug 			- Slug used for id of this menu page 
	* @author	Ben Moody
	*/
	public function load_sections( $sections_settings = false, $page_slug ) {
		
		//Init vars
		//$sections_settings = $this->setup_sections();
		
		$defaults = array(
			'id' 		=> '',
			'title'		=> '',
			'callback'	=> array( $this, 'section_callback' ),
			'page'		=> $page_slug
		);
		
		if( $sections_settings ) {
			
			//Loop settings array and call add_settings_section wp function
			foreach( $sections_settings as $args ) {
				
				//Merge args array with default
				$args = wp_parse_args( $args, $defaults );
				
				//Call wp function to add this settings section
				add_settings_section(
					$args['id'],
					$args['title'],
					$args['callback'],
					$args['page']
				);
				
			}
			
		}
		
	}
	
	public function section_callback() {
		echo '<p>Settings for this section</p>';
	}
	
	/**
	* load_fields
	* 
	* Loops through the $fields_settings array and uses the args to call add_settings_field
	* for each section field in the array.
	* 
	* $fields_settings[] = array(
			'section' 	=> $this->get_slug('general_options'),
			'id'		=> $this->get_slug('field_legal_html'),
			'title'		=> '',
			'desc'		=> '',
			'type'		=> '',
			'default'	=> ''
		);
	* 
	* @access 	public
	* @param	array	$fields_settings 	- Array of args req to create a section field
	* @param	string	$page_slug 			- Slug used for id of this menu page 
	* @param	array	$option_data		- Array of option data for this option page
	* @author	Ben Moody
	*/
	public function load_fields( $fields_settings = false, $page_slug, $option_data ) {
		
		//Init vars
		//$fields_settings = $this->setup_fields();
		
		$defaults = array(
			'section' 	=> '',
			'id'		=> '',
			'title'		=> '',
			'desc'		=> '',
			'type'		=> '',
			'default'	=> '',
			'class'		=> '',
			'page'		=> $page_slug
		);
		
		if( $fields_settings ) {
			
			//Loop fields array and call helper to draw the field html and add field to section
			foreach( $fields_settings as $args ) {
				
				//Merge args array with default
				$args = wp_parse_args( $args, $defaults );
	
				
				//Get option data from database for this field
				//var_dump($page_slug);
				//exit();
				
				if( isset($option_data[ $page_slug ]) && !empty($option_data[ $page_slug ]) ) {
					
					$options = $option_data[ $page_slug ];
		
					if( array_key_exists( $args['id'], $options ) ) {
						
						$args['default'] = $options[ $args['id'] ];
						
					} else {
						//Loop data array and try to find array key that matched supplied field ID
						foreach( $options as $data_a ) {
							
							if( isset( $data_a[ $args['id'] ] ) ) {
							
								$args['default'] = $data_a[ $args['id'] ];
								
							} elseif( is_array($data_a) ) {
							
								//Must be a nested array, continue the search
								foreach( $data_a as $data_b ) {
								
									if( isset( $data_b[ $args['id'] ] ) ) {
										$args['default'] = $data_b[ $args['id'] ];
									}
									
								}
								
							}
							
						}
						
					}
					
				}
				
				//Call add_settings_field with create_settings_fields helper as callback
				add_settings_field(
					$args['id'],
					$args['title'],
					array( $this, 'create_settings_fields' ),
					$page_slug,
					$args['section'],
					$args
				);
	
			}
			
		}
		
	}
	
	/**
	* create_settings_fields
	* 
	* Helper to echo out the html required to create an admin form field.
	*
	* Switches $type arg provided via fields_settings array ( see load_fields() )
	* to detect which field type to output.
	*
	* Detects if the args contain a custom field class, also adds validation error msg divs for each
	* field which are enabled by the Flash helper using jquery.
	* 
	* $args = array(
			'section' 	=> '',
			'id'		=> '',
			'title'		=> '',
			'desc'		=> '',
			'type'		=> '',
			'default'	=> '',
			'class'		=> '',
			'page'		=> $page_slug
		);
	* 
	* @access 	public
	* @param	array	$args 	- Array of args req to output form field html
	* @author	Ben Moody
	*/
	public function create_settings_fields( $args = array() ) {
		
		//Init vars
		$field_name = null;
		$defaults = array(
			'class' 	=> NULL,
			'default' 	=> NULL,
			'id' 		=> NULL,
			'page' 		=> NULL,
			'desc'		=> NULL,
			'choices'	=> array(),
			'array_key'	=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		if( !empty($args) ) {
			
			//Extract vars from args array
			extract( $args );
			
			// additional field class. output only if the class is defined in the create_setting arguments  
    		$field_class = $class; 
			
			//Detect if we need to set field name structure to create a nested array of data - e.g. name="Foo['bar']" or name="bar"
			if( $page === 'post' ) {
				$field_name = $id;
			} else {
			
				//Check if we should nest within an array key
				if( isset($array_key) ) {
					$field_name = $page . "[{$array_key}]" . "[{$id}]";
				} else {
					$field_name = $page . "[{$id}]";
				}
				
			}
			
			//Prep title string
			if( !empty($title) ) {
				$title = strtolower( $title );
				$title = ucfirst( $title );
			}
			
			// switch html display based on the setting type.
			switch ( $type ) {  
		        case 'text':  
		        	$default_class = 'prso-regular-text';
		        	
		            $default = stripslashes($default);  
		            $default = esc_attr( $default);  
		            
		            //If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = 'mf_text';
		            	//echo "<label for='{$id}'>{$title}</label>";
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		            
		            echo "<input class='$default_class $field_class' type='text' id='$id' name='{$field_name}' value='$default' />";  
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		          
		        break;  
		        
		        case 'hidden':  
		            $default = stripslashes($default);  
		            $default = esc_attr( $default);  
		            echo "<input class='regular-text $field_class' type='hidden' id='$id' name='{$field_name}' value='$default' />";
		        break; 
		  		
		  		case "multi-text":  
		  			
		  			//First see if $choices is a string or array - allows comma seperated choices
		        	if( is_string($choices) ) {
		        		$choices_tmp = array();
		        		
		        		//Explode the string
		        		$choices_tmp = explode( ',', $choices );
		        		
		        		//Check for errors in explode, then loop choices_tmp
		        		if( !empty($choices_tmp) && $choices_tmp[0] != $choices ) {
		        			//Unset $choice string so we can use it as an array next
		        			unset($choices);
		        			
		        			//Loop choices_tmp and redefine $choices array
		        			foreach( $choices_tmp as $choice ) {
		        				$choices[ $choice ] = $choice;
		        			}
		        		}
		        	}
		  			
		            foreach($choices as $name => $item) { 
		             
		                $item = esc_html($item);  
		                $name = esc_html($name);
		                
		                if ( !empty($default) && is_array($default) && isset($default[$item]) ) { 
		                	$value = $default[$item];
		                } else {  
		                	$value = '';  
		                }  
		                
		                $_field_name = $field_name . "[{$item}]";
		                
		                echo "<span>$name:</span> <input class='$field_class' type='text' id='$id-{$item}' name='{$_field_name}' value='$value' /><br/>";    
		            }  
		            echo ($desc != '') ? "<span class='description'>$desc. ({$id})</span>" : "";  
		            //Output validation field
					echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		        break;
		  		
		        case 'text_area':  
		        	$default_class = 'prso-textarea';
		        	
		            $default = stripslashes($default);  
		            $default = esc_html( $default);  
		            
		            //If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = '';
		            	//echo "<label for='{$id}'>{$title}</label>";
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		            
		            echo "<textarea class='$default_class $field_class' type='text' id='$id' name='{$field_name}' rows='5' cols='30'>$default</textarea>";  
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		              
		        break; 
		        
		        case 'wysiwyg':  
		        	$default_class = 'wp-editor-area';
		        	
		            $default = stripslashes($default);  
		            //$default = esc_html( $default); Breaks html tags in visual editor 
		            
		            //If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = '';
		            	//echo "<label for='{$id}'>{$title}</label>";
		            	echo "<p><strong>{$title}</strong></p>";
		            }

		            //Create a unique id for tinymce editor - lower-case letters. No underscores, no hyphens.
		            $tinymce_id = strtolower(str_replace('_', '', $id));
		            
		            //Call wordpress function to output std wordpress visual editor
		            wp_editor( $default, $tinymce_id,
		            	array(
		            		'wpautop' 		=> true,
		            		'media_buttons'	=> false,
		            		'textarea_name'	=> $field_name,
		            		'editor_class'	=> 'custom-wp-editor',
		            		'tinymce'		=> true
		            	)
		            );
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		            
		        break; 
		  
		        case 'select':  
		        	$default_class = 'select';
		        	
		        	//If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = '';
		            	//echo "<label for='{$id}'>{$title}</label>"; USING P TAG INSTEAD
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		        	
		        	//First see if $choices is a string or array - allows comma seperated choices
		        	if( is_string($choices) ) {
		        		$choices_tmp = array();
		        		
		        		//Explode the string
		        		$choices_tmp = explode( ',', $choices );
		        		
		        		//Check for errors in explode, then loop choices_tmp
		        		if( !empty($choices_tmp) && $choices_tmp[0] != $choices ) {
		        			//Unset $choice string so we can use it as an array next
		        			unset($choices);
		        			
		        			//Loop choices_tmp and redefine $choices array
		        			foreach( $choices_tmp as $choice ) {
		        				$choices[ $choice ] = $choice;
		        			}
		        		}
		        	}
		        	
		            echo "<select id='$id' class='$default_class $field_class' name='{$field_name}'>";  
		                foreach($choices as $name => $item) {  
		                    $name = esc_html($name); 
		                	$item = esc_html($item);  
		  
		                    $selected = ($default==$item) ? 'selected="selected"' : '';  
		                    echo "<option value='$item' $selected>$name</option>";  
		                }  
		            echo "</select>";  
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		             
		        break;  
		  
		        case 'checkbox': 
		        	$default_class = 'checkbox';
		        	
		        	//If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = '';
		            	//echo "<label for='{$id}'>{$title}</label>";
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		         	
		         	echo "<input class='$default_class $field_class' type='hidden' id='$id' name='{$field_name}' value='0' checked='checked' />";
		            echo "<input class='$default_class $field_class' type='checkbox' id='$id' name='{$field_name}' value='1' " . checked( $default, 1, false ) . " />";  
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		             
		        break;  
		        
		        case "multi-checkbox":  
		        	$default_class = 'checkbox';
		        	
		        	//If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = '';
		            	//echo "<label for='{$id}'>{$title}</label>";
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		        	
		        	//First see if $choices is a string or array - allows comma seperated choices
		        	if( is_string($choices) ) {
		        		$choices_tmp = array();
		        		
		        		//Explode the string
		        		$choices_tmp = explode( ',', $choices );
		        		
		        		//Check for errors in explode, then loop choices_tmp
		        		if( !empty($choices_tmp) && $choices_tmp[0] != $choices ) {
		        			//Unset $choice string so we can use it as an array next
		        			unset($choices);
		        			
		        			//Loop choices_tmp and redefine $choices array
		        			foreach( $choices_tmp as $choice ) {
		        				$choices[ $choice ] = $choice;
		        			}
		        		}
		        	}
		        	
		            foreach($choices as $name => $item) {  
		  
		                $name = esc_html($name);
		                $item = esc_html($item);  
		                $checked = '';  
		        		
		  				if ( !empty($default) && is_array($default) ) { 
		                	if( in_array($item, $default) ) {
		                		$checked = 'checked="checked"';
		                	}
		                } 
		                
		                $_field_name = $field_name . "[]";
		  				
		  				echo "<input class='$default_class $field_class' type='hidden' id='$id' name='{$_field_name}' value='' checked='checked' />";
		  				
		                echo "<input class='$default_class $field_class' type='checkbox' id='$id-{$item}' name='{$_field_name}' value='{$item}' $checked /> $name <br/>";  
		            }  
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		            
		        break;
		        
		        case "radio":
		        	$default_class = 'radio';
		        	
		        	//If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = '';
		            	//echo "<label for='{$id}'>{$title}</label>";
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		        	
		        	//First see if $choices is a string or array - allows comma seperated choices
		        	if( is_string($choices) ) {
		        		$choices_tmp = array();
		        		
		        		//Explode the string
		        		$choices_tmp = explode( ',', $choices );
		        		
		        		//Check for errors in explode, then loop choices_tmp
		        		if( !empty($choices_tmp) && $choices_tmp[0] != $choices ) {
		        			//Unset $choice string so we can use it as an array next
		        			unset($choices);
		        			
		        			//Loop choices_tmp and redefine $choices array
		        			foreach( $choices_tmp as $choice ) {
		        				$choices[ $choice ] = $choice;
		        			}
		        		}
		        	}
		        	
		        	foreach( $choices as $name => $item ){
		        	
		        		$name = esc_html($name);
		                $item = esc_html($item);  
		                $checked = '';
		        		
		        		if ( !empty($default) ) { 
		                	if( $default === $item ) {
		                		$checked = 'checked="checked"';
		                	}
		                } 
		        		
		        		echo "<input class='$default_class $field_class' type='radio' id='$id-{$item}' name='{$field_name}' value='{$item}' {$checked} /> $name <br/>";
		        	}
		        	
		        	//Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		        	
		        break;
		        
		        case 'media':  
		        	$default_class = 'regular-text';
		        	
		            $default = stripslashes($default);  
		            $default = esc_attr( $default);  
		            
		            //If this is a post/page meta field then add a label
		            if( $page === 'post' ) {
		            	$default_class = 'mf_text';
		            	//echo "<label for='{$id}'>{$title}</label>:";
		            	echo "<p><strong>{$title}</strong></p>";
		            }
		            
		            //Media URL Field
		            echo "<input class='$default_class media-url $field_class' type='text' id='$id' name='{$field_name}' value='$default' />";  
		            
		            //Action button
		            echo "<input id='{$id}_button' type='button' class='button' value='Upload' />";
		            
		            //Detect if admin page or post/page
		            if( $page === 'post' ) {
		            	echo "<p class='mf_caption'>{$desc}</p>";
		            } else {
		            	//Output validation field
						echo "<div id='{$id}-error' style='display:none;color: red;font-weight: bold;'></div>";
		            	echo ($desc != '') ? "<br /><span class='description'>$desc. ({$id})</span>" : "";
		            }
		            
		            //Setup scripts required to activate wordpress media uploader using helper
		            $media_defaults = array(
						'action_id'				=> "{$id}_button", //ID of action button
						'url_destination_id'	=> $id, //ID of page element to recieve returned file url
					);
					
					$args = wp_parse_args( $args, $media_defaults );
					
					echo $this->wp_media_uploader_helper( $args );
		          
		        break;
		    } 
		    
		}
		
	}
	
	/**
	* table
	* 
	* Helper to echo out the html required to create an admin table.
	*
	* $data must be a contain an array of field data for each table row
	* $args - see $defaults for example
	*
	*/
	public function table( $output = NULL, $args = array(), $data = array() ) {
		
		//Init vars
		$output = null;
		$defaults = array(
			'headers' 			=> array(),
			'footer'			=> true,
			'table_class'		=> 'wp-list-table widefat fixed pages',
			'alternate_class'	=> 'alternate',
			'caption'			=> null
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		//Start table
		$output.= "<table class='{$table_class}'>";
		
		//Get thead
		$output.= $this->table_head( $headers );
		
		//Add caption
		if( !empty($caption) ) {
			$output.= "<caption style='text-align: left;'>{$caption}</caption>";
		}
		
		//Get table rows
		$output.= $this->table_rows( $data, $alternate_class );
		
		//Get tfoot
		if( $footer ) {
			$output.= $this->table_head( $headers, true );
		}
		
		//Close table
		$output.= "</table>";
		
		return $output;
	}
	
	/**
	* table_rows
	* 
	* Called by table() to loop through the table data array and create each tbody row
	*
	*/
	private function table_rows( $data = array(), $alt_class = null ) {
		
		//Init vars
		$output = '<tbody><tr><td></td></tr></tbody>';
		$class	= null;
		
		if( !empty($data) ) {
			
			//Override default output
			$output = '<tbody>';
			
			//Loop data array and create each row
			$i = 0;
			foreach( $data as $row ) {
				
				//Setup row alt class
				if( $i % 3 ) {
					$class = $alt_class;
				}
				
				$output.= "<tr class='{$class}'>";
				
				//Loop row data
				foreach( $row as $td ) {
					$output.= "<td>{$td}</td>";
				}
				
				$output.= '</tr>';
				
				$i ++;
			}
			
			//Close tbody
			$output.= '</tbody>';
			
		}
		
		return $output;
	}
	
	/**
	* table_head
	* 
	* Called by table() to loop through the headers data array and create each thead/tfoot row
	*
	*/
	private function table_head( $headers = array(), $foot_tags = false ) {
		
		//Init vars
		$output = null;
		$tag	= 'thead';
		
		if( !empty($headers) ) {
			
			//Is this a footer or header request?
			if( $foot_tags ) {
				$tag = 'tfoot';
			}
			
			//Start thead
			$output.= "<{$tag}><tr>";
			
			//Loop headers array
			foreach( $headers as $th ) {
				$output.= "<th>{$th}</th>";
			}
			
			//End thead
			$output.= "</tr></{$tag}>";
			
		}
		
		return $output;
	}
	
	/**
	* button
	* 
	* Helper to output a wordpress admin SUBMIT button
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function button( $output = NULL, $title = null, $args = array(), $url_params = array() ) {
		
		//Init vars
		$url		= null;
		$output 	= null;
		$defaults 	= array(
			'page_slug'		=> null,
			'controller' 	=> null, 
			'action' 		=> null, 
			'class' 		=> 'button-primary', 
			'style'			=> null,
			'p_style'		=> null,
			'type' 			=> null,
			'onclick'		=> null
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
		
		//Detect button type
		$type = strtolower($type);
		if( $type == 'submit' ) {
			
			//Built submit button output
			ob_start();
			?>
			<p class="submit">
				<input class="<?php echo $class; ?>" name="Submit" type="submit" value="<?php echo $title; ?>" style="<?php echo $style; ?>" />
			</p>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			
		} else {
			
			//Parse args and build link url to conrtoller and action
			$add_query_args = array( 'controller' => $controller, 'action' => $action );
			
			//Check for extra params
			if( !empty( $url_params ) ) {
				//Loop extra params and add them to add_query_arg
				foreach( $url_params as $key => $val ) {
					$add_query_args[ $key ] = $val;
				}
			}
			
			//Form url using wp add_query_arg function
			$url = add_query_arg( $add_query_args );
			
			//Add a Nonce to the url
			$nonce_action = "{$page_slug}-{$action}_{$controller}";
			
			$url = wp_nonce_url( $url, $nonce_action );
			
			//Built link button output
			ob_start();
			?>
			<p style="<?php echo $p_style; ?>">
				<a class="<?php echo $class; ?>" href="<?php echo $url; ?>" style="<?php echo $style; ?>" onclick="<?php echo $onclick; ?>" ><?php echo $title; ?></a>
			</p>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			
		}
		
		return $output;
	}
	
	protected function wp_media_uploader_helper( $args = array() ) {
	
		//Init vars
		$output = NULL;
		$defaults = array(
			'action_id'				=> NULL, //ID of action button
			'url_destination_id'	=> NULL, //ID of page element to recieve returned file url
			'window_name'			=> 'Upload File',
			'media_type' 			=> 'image', //Can be image, video, audio, file
			'post_id'				=> 0,
			'referer'				=> $this->__get( 'media_uploader_referer' )
		);
		
		//Parse args
		$args = wp_parse_args( $args, $defaults );
		
		//Extract args
		extract( $args, EXTR_SKIP );
		
		//Check that required args have been supplied
		if( isset($action_id, $referer) ) {
			
			//Call method to return media browser javascript
			$output.= $this->wp_media_uploader_javascript( $args );
			
			//Enqueue scripts required for wp media uploader
			add_action( 'admin_enqueue_scripts', array($this, 'wp_media_uploader_enqueue_scripts') );
			
		}
		
		return $output;
		
	}
	
	private function wp_media_uploader_javascript( $args = array() ) {
		
		//Init vars
		$output = NULL;
		
		if( !empty($args) ) {
			
			//Extract args
			extract( $args, EXTR_SKIP );
			
			//Start buffer
			ob_start();
			?>
			<script type="text/javascript">
				
				jQuery(document).ready(function($){
					
					var btnID
					
					$('#<?php echo $action_id; ?>').click(function(){
						btnID = $(this).attr('id');
						tb_show( '<?php echo $window_name; ?>', 'media-upload.php?referer=<?php echo $referer; ?>&type=<?php echo $media_type; ?>&TB_iframe=true&post_id=<?php echo $post_id; ?>', false );
						return false;
					});
					
					window.original_send_to_editor = window.send_to_editor;
					
					window.send_to_editor = function(html) {
						
						if(btnID) {
							
							var file_url = '';
						
							<?php if( $media_type == 'image' ): ?>
							var file_url = $('img', html).attr('src');
							<?php endif; ?>
							
							
							<?php if( !empty($url_destination_id) ): ?>
							$('#<?php echo $url_destination_id; ?>').val(file_url);
							<?php endif; ?>
							
							
							tb_remove();
							
						} else {
							window.original_send_to_editor(html);
						}
						
					}
				});
			</script>
			<?php
			//Get buffer contents
			$output = ob_get_contents();
			//End and clear buffer
			ob_end_clean();
			
		}
		
		return $output;
		
	}
	
	public function wp_media_uploader_replace_btn_text( $translated_text, $text, $domain ) {
		
		//Init vars
		$referer 			= $this->__get( 'media_uploader_referer' );
		$btn_text			= $this->__get( 'media_uploader_btn_text' );
		$default_btn_text	= 'Insert into Post';
		$referer_check		= false;

		
		if( isset($referer, $btn_text) && $domain == 'default' ) {
			
			//Check that text is set to media uploader default
			if( $text == $default_btn_text ) {
				
				//Confirm referer
				$referer_check = strpos( wp_get_referer(), $referer );
				
				if( $referer_check ) {
					remove_filter( 'gettext', array($this, 'wp_media_uploader_replace_btn_text') );
					return __( $btn_text, 'presso' );
				}
			}
			
		}
		
		return $translated_text;
		
	}
	
	public function wp_media_uploader_enqueue_scripts() {
		
		//Enqueue jQuery
		wp_enqueue_script( 'jquery' );
		
		//Enqueue thickbox
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		
		//Enqueue wp media-upload script
		wp_enqueue_script( 'media-upload' );
		
	}
	
	public function shortcode_regex( $regex, $shortcode_tag = NULL ) {
		
		if( isset($shortcode_tag) ) {
			
			$regex = "#\[ *{$shortcode_tag}([^\]])*\]#i";
			
		} else {
			
			//Just return the WP shortcode for detecting any shortcode
			if( function_exists('get_shortcode_regex') ) {
				$regex = get_shortcode_regex();
			}
			
		}
		
		return $regex;
	}
	
	/**
	* create_tinymce_shortcode_plugin
	* 
	* Called using custom wp action - do_action( 'prso_core_create_tiny_mce_plugin', $args );
	* 
	* This method will dynamically create and output your tinymce plugin javascript file using the $args
	* It then calls the appropriate filters to add the plugin and it's button to WP tinymce editor.
	*
	* NOTE: The method will only ouput your plugin javascript file once. If you update the $args be sure to
	* delete the javascript file from the file_path arg provided in $args.
	*
	* NOTE: Be sure to wrap the args and do_action call for each custom plugin within a function which is called during 'admin_int':
	*		add_action( 'admin_init', '{your_function_name_here}' );
	*
	* $args = array(
			'plugin_slug' 		=> 'test',
			'title'				=> 'Test',
			'image'				=> '/test.png',
			'content'			=> array(
				'prompt' 	=> 'test',
				'default'	=> 'default'
			),
			'shortcode_args'	=> array(
				'arg_slug'	=> array(
					'slug' 		=> 'argslug',
					'prompt'	=> 'prompt',
					'default'	=> 'default'
				)
			),
			'plugin_info'		=> array(
									'longname' 	=> NULL,
									'author'	=> NULL,
									'authorurl'	=> 'http://benjaminmoody.com',
									'infourl'	=> 'http://pressoholics.com',
									'version'	=> '1.0'
								),
			'file_path'			=> get_stylesheet_directory() . '/javascripts/tinymce-plugin.js',
			'file_url'			=> get_stylesheet_directory_uri() . '/javascripts/tinymce-plugin.js'
		);
		
		//Call custom action to create plugin javascript file
		do_action( 'prso_core_create_tiny_mce_plugin', $args );
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function create_tinymce_shortcode_plugin( $args = array() ) {
		
		//Init vars
		$shortcode_arg_code = NULL;
		$output = NULL;
		$defaults = array(
			'plugin_slug' 		=> NULL,
			'title'				=> NULL,
			'image'				=> NULL,
			'content'			=> array(),
			'shortcode_args'	=> array(),
			'plugin_info'		=> array(
									'longname' 	=> NULL,
									'author'	=> NULL,
									'authorurl'	=> 'http://benjaminmoody.com',
									'infourl'	=> 'http://pressoholics.com',
									'version'	=> '1.0'
								),
			'file_path'			=> NULL,
			'file_url'			=> NULL
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$args['plugin_info'] = wp_parse_args( $args['plugin_info'], $defaults['plugin_info'] );
		
		extract( $args );
		
		//First confirm current user can edit pages/posts
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
			return;
		}

		
		if( !file_exists($file_path) && isset( $plugin_slug, $file_path ) ) {
			
			//Start buffering the tinymce plugin javascript
			ob_start();
			?>
			(function() {
			   tinymce.create('tinymce.plugins.<?php echo $plugin_slug; ?>', {
			      init : function(ed, url) {
			         ed.addButton('<?php echo $plugin_slug; ?>', {
			            title : '<?php echo $title; ?>',
			            image : url+'<?php echo $image; ?>',
			            
			            onclick : function() {
			            
			            <?php
			            	//Output prompt for shortcode args
			            	if( !empty($shortcode_args) ) {
			            		foreach( $shortcode_args as $args_array ) {
			            			?>
			            			var <?php echo $args_array['slug']; ?> = prompt("<?php echo $args_array['prompt']; ?>", "<?php echo $args_array['default']; ?>");
			            			<?php
			            			//Also cache the shortcode_arg_code for this shortcode_arg
			            			$shortcode_arg_code.= " {$args_array['slug']}=\"'+{$args_array['slug']}+'\"";
			            		}
			            	}
			            ?>
			            
			            <?php
			            	//Output prompt for shortcode content
			            	if( !empty($content) ) {
			            	
			            		if( !isset($content['default']) || empty($content['default']) ) {
			            			$content['default'] = ' ';
			            		}
			            		
		            			?>
		            			var content = prompt("<?php echo $content['prompt']; ?>", "<?php echo $content['default']; ?>");
		            			<?php
			            	}
			            ?>
			             
			             <?php
			             	//Output actions script to insert shortcode into tinymce content area
			             	if( !empty($content) ) {
			             	
			             		//Output shortcode with closing section - [/shortcode-slug]
			             		?>
			             		ed.execCommand('mceInsertContent', false, '[<?php echo $plugin_slug; ?> <?php echo $shortcode_arg_code; ?>]'+content+'[/<?php echo $plugin_slug; ?>]');
			             		<?php
			             		
			             	} else {
			             	
			             		//Output shortcode without closing section - [/shortcode-slug]
			             		?>
			             		ed.execCommand('mceInsertContent', false, '[<?php echo $plugin_slug; ?> <?php echo $shortcode_arg_code; ?>]');
			             		<?php
			             		
			             	}
			             ?>
			               
			            }
			            
			         });
			      },
			      createControl : function(n, cm) {
			         return null;
			      },
			      getInfo : function() {
			         return {
			            longname : "<?php echo $plugin_info['longname']; ?>",
			            author : '<?php echo $plugin_info['author']; ?>',
			            authorurl : '<?php echo $plugin_info['authorurl']; ?>',
			            infourl : '<?php echo $plugin_info['infourl']; ?>',
			            version : "<?php echo $plugin_info['version']; ?>"
			         };
			      }
			   });
			   tinymce.PluginManager.add('<?php echo $plugin_slug; ?>', tinymce.plugins.<?php echo $plugin_slug; ?>);
			})();
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			
		}
		
		//Put output content into requested javascript file
		if( !empty($output) && isset($file_path) ) {
			
			//Create javascript file and put output contents
			if( !file_exists($file_path) ) {
			
				file_put_contents($file_path, $output);
				touch($file_path, filemtime($file_path));

			}
			
		}
		
		//Confirm that the javascript file exsists and then add wp filters
		if( file_exists($file_path) && isset($file_url) ) {
			
			if( get_user_option('rich_editing') == 'true' ) {
			
				//Cache this tinymce plugin in global var
				global $prso_tinymce_helper;
				$prso_tinymce_helper[ $plugin_slug ] = $file_url;
				
				//Call function to register tinymce plugin with wp
				add_filter( 'mce_external_plugins', array($this, 'add_tinymce_plugin') );
		
				//Call function to register tinymce plugin with wp
				add_filter( 'mce_buttons', array($this, 'add_tinymce_button') );
				
			}
			
		}
		
	}
	
	/**
	* add_tinymce_plugin
	* 
	* Called by $this->create_tinymce_shortcode_plugin
	*
	* Loops all tinymce plugin data stored in global var $prso_tinymce_helper by $this->create_tinymce_shortcode_plugin
	* For each plugin found the method adds the plugins javascript url to WP $plugin_array
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_tinymce_plugin( $plugin_array ) {
		
		//Confirm required vars have been set
		global $prso_tinymce_helper;
		
		if( isset($prso_tinymce_helper) && is_array($prso_tinymce_helper) ) {
			
			//Loop each tinymce plugin and add it to the wp plugin_array
			foreach( $prso_tinymce_helper as $plugin_slug => $plugin_url ) {
				$plugin_array[ $plugin_slug ] = $plugin_url;
			}
			
		}
		
		return $plugin_array;
	}
	
	/**
	* add_tinymce_button
	* 
	* Called by $this->create_tinymce_shortcode_plugin
	*
	* Loops all tinymce plugin data stored in global var $prso_tinymce_helper by $this->create_tinymce_shortcode_plugin
	* For each plugin found the method adds the plugins slug to the WP $buttons array
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_tinymce_button( $buttons ) {
		
		//Confirm required vars have been set
		global $prso_tinymce_helper;
		
		if( isset($prso_tinymce_helper) && is_array($prso_tinymce_helper) ) {
			
			//Loop each tinymce plugin and add a tinymce button
			foreach( $prso_tinymce_helper as $plugin_slug => $plugin_url ) {
				
				//Check if this plugin in already in button array
				if( !in_array( $plugin_slug, $buttons ) ) {
					array_push( $buttons, "|", $plugin_slug );
				}
				
			}
			
		}
		
		return $buttons;
	}
	
}