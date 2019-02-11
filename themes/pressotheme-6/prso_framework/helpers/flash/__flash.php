<?php
/**
 * Flash Message Helper class file.
 *
 * Simplifies the display of user flash messages.
 *
 * CONTENTS:
 *
 * 
 * 
 */
 
class FlashHelper {
	
	//Set default session name for flash messages
	private $name = 'prso_flash';
	private	$validate_name = 'prso_flash_validate';
	
	function __construct() {
		//Flash messages make use of sessions, start session support
		if( session_id() == '' ) {
			session_start();
		}
		
		//If admin area then hook into admin_notices wp action to output flash message
		if( is_admin() ) {
			add_action( 'admin_notices', array( &$this, 'get_flash' ) );
		} else {
			//If front end then add create a new action for flash messages
			add_action( 'prso_flash', array( &$this, 'get_flash' ) ); //Call do_action( 'prso_flash' ); in template to output flash messages
		}
	}
	
	/**
	* set_flash
	* 
	* Simplifies setting user flash messages. Will customize output based on $type param,
	* You can set multiple types of flash messages by providing the $name param not required.
	* The method will automatically adjust the html output for the message based on whether the
	* request is from the admin section or the front end.
	*
	* Types:
	* [is_admin] 	=> 'success', 'error'
	* [!is_admin]	=> 'success', 'error', 'warning'
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function set_flash( $msg = null, $type = 'success', $name = null ) {
		
		//Init vars
		$flash = null;
		if( empty($name) ): $name = $this->name; endif;
		
		//Detect if this is an admin panel requrest or theme request
		if( is_admin() ) {
			
			if( $type === 'success' ) {
				$flash = "<div class='updated' id='message' ><p>{$msg}</p></div>";
			} elseif( $type === 'error' ) {
				$flash = "<div class='error' id='message' ><p>{$msg}</p></div>";
			}
			
		} else {
			
			if( $type === 'success' ) {
				
				ob_start();
				?>
				<div class="alert-box success">
					<?php echo $msg; ?>
					<a href="" class="close">&times;</a>
				</div>
				<?php
				$flash = ob_get_flush();
				
			} elseif( $type === 'error' ) {
				
				ob_start();
				?>
				<div class="alert-box error">
					<?php echo $msg; ?>
					<a href="" class="close">&times;</a>
				</div>
				<?php
				$flash = ob_get_flush();
				
			} elseif( $type === 'warning' ) {
				
				ob_start();
				?>
				<div class="alert-box warning">
					<?php echo $msg; ?>
					<a href="" class="close">&times;</a>
				</div>
				<?php
				$flash = ob_get_flush();
				
			}
			
		}
		
		
		//Should we save the session var or has a reset been requested
		if( $msg != 'reset' ) {
			$_SESSION[$name] = $flash;
		} else {
			//Clear session array
			unset( $_SESSION[$name] );
		}
		
	}
	
	/**
	* get_flash
	* 
	* Use to get a flash message and echo it to the page.
	* Once echoed the method will unset the session to ensure the message
	* is not repeated.
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function get_flash( $name = null ) {
		
		//Init vars
		if( empty($name) ): $name = $this->name; endif;
		
		if( isset( $_SESSION[$name] ) ) {
			echo $_SESSION[$name];
			//Clear message
			$this->set_flash( 'reset', null, $name );
		}
	}
	
	/**
	* set_validate_flash
	* 
	* Called by Pressohilics validation model. This method takes the array
	* of fields that failed validation and stores the array in a flash session.
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function set_validate_flash( $failed_fields = array() ) {
	
		if( !empty($failed_fields) ) {
			//Init vars
			$name = $this->validate_name;
			//Save data in session
			$_SESSION[$name] = $failed_fields;
		}
	
	}
	
	/**
	* get_validate_flash
	* 
	* Called in the view to return jquery required to unhide field error divs and
	* populate them with the error message arg set when passing the field to the 
	* pressoholics validation model class.
	*
	* This method looks for the validation flash session and then loops the array
	* of fields which failed validation. For each field the method outputs a line of
	* jquery to first show the hidden valiation div and then update the html with the
	* message arg from the flash session array (message is first set when you pass the field
	* to the validation method - see ValidateModel->validate() in Pressoholics theme plugin.
	*
	* To show the field error message add the hidden div below your input field:
	*
	* <div id='{$field_slug}-error' style='display:none;color: red;font-weight: bold;'></div>
	*
	* NOTE the id {field_slug}-error
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function get_validate_flash() {
		
		//Init vars
		$name = $this->validate_name;
		$failed_fields = null;
		$output_script = null;
		$script_buffer = null;
		
		if( isset( $_SESSION[$name] ) ) {
			
			$failed_fields = $_SESSION[$name];	
			
			if( is_array($failed_fields) && !empty($failed_fields) ) {
				
				//Loop failed_fields and use data to build jQuery to populate and show field validation messages
				foreach( $failed_fields as $field_slug => $args ) {
					$error_msg = 'Error';
					if( isset($args['message']) ){
						$error_msg = $args['message'];
					}
					$script_buffer.= "jQuery('#{$field_slug}-error').html('{$error_msg}').show();";
					$script_buffer.= "jQuery('#{$field_slug}').attr('style', 'border-color: red;');";
				}
				
				//Echo out the script
				ob_start();
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						<?php echo $script_buffer; ?>
					});
				</script>
				<?php
				$output_script = ob_get_contents();
				ob_end_clean();
				
				//Destroy session
				unset( $_SESSION[$name] );
				
				return $output_script;
			}
					
		}
		
	}

}