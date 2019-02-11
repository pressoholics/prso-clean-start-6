<?php
/**
* main_view.php
* 
* View template for main google maps view.
*
* Note that main map canvas and the action link area. You MUST keep these ID's intact unless
* you are going to change them in the main js file.
*
* Also note the global var $prso_google_maps_main. This contains all the data required to build anything
* in this view. How you use this is up to you. Although I would recommend creating some custom actions
* in the plugin class to output any html based on data in this var.
* 
* @param	global/Array	$prso_google_maps_main
* @access 	public
* @author	Ben Moody
*/
global $prso_google_maps_main;
?>
<!-- Google Maps Canvas !-->
<div id="prso-gmaps-map"></div>

<!-- Action links !-->
<div id="prso-gmaps-actions">
	<ul>
		<li>
			<a href="#" class="prso-gmaps-action" rel="0">Info Window 0</a>
		</li>
	</ul>
</div>