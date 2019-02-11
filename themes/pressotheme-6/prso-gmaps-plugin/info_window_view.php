<?php
/**
* info_window_view.php
* 
* View template for any Info windows in your map.
*
* Also note the global var $prso_google_maps_infowindow. This contains all the data required to build anything
* in this view. How you use this is up to you. Although I would recommend creating some custom actions
* in the plugin class to output any html based on data in this var.
* 
* @param	global/Array	$prso_google_maps_infowindow
* @access 	public
* @author	Ben Moody
*/
global $prso_google_maps_infowindow;
?>
<div class="twelve columns">
	<?php echo '<h2 style="color:red;">Hail to the king baby!</h2>'; ?>
	<p><?php echo $prso_google_maps_infowindow['title']; ?></p>
</div>