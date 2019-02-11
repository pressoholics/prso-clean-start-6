<?php

/**
* prso_scan_mu_plugins
* 
* Scans the mu plugins dir to find any mu plugin folders
* 
*/
function prso_scan_mu_plugins() {
		
	//Init vars
	$dir	= plugin_dir_path( __FILE__ );
	$result = false;
	$scan	= null; //Cache result of dir scan
	
	if( isset($dir) ) {
		$scan = scandir( $dir );
		
		//Loop scandir result and store any found dirs in $result
		foreach( $scan as $dir ) {
			//Ignore any root designations
			if( !empty($dir) && $dir != '.' && $dir != '..' && $dir != 'load.php' ) {
				if( is_string($dir) ) {
					$result[] = $dir;
				}
			}
		}
	}
	
	return $result;
}
	
/**
* prso_load_mu_plugins
* 
* 
*/
function prso_load_mu_plugins( $mu_plugins = FALSE ) {
	
	//Init vars
	$dir	= plugin_dir_path( __FILE__ );
	
	if( $mu_plugins && is_array( $mu_plugins ) ) {
		
		//Loop the result of the helpers dir scan and try to instantiate each helper class
		foreach( $mu_plugins as $plugin ) {
			
			//Check if helper file exsists
			if( file_exists( $dir . '/' . $plugin . '/' . $plugin . '.php' ) ) {
				
				//Include the helper file
				include_once( $dir . '/' . $plugin . '/' . $plugin . '.php' );
				
			}
		}
		
	}
	
}

//Init vars
$mu_plugins = FALSE;

//Scan for mu plugins
$mu_plugins = prso_scan_mu_plugins();

//Load mu plugins
prso_load_mu_plugins( $mu_plugins );

?>