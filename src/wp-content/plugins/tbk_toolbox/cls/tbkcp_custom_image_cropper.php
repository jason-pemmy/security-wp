<?php
/*
Description: Includes a plugin used to crop images on the fly in wordpress. See the external_plugins/custom-image-cropper.php file for documention
Author: Trevor Mills
Usage: 
1. Open a picture in the media gallery and select crop.
2. Select the image size from the drop down
3. Crop and save the image. Wherever that image size (it thumbnail) is present in your theme, that cropped image will show
*/

class tbkcp_custom_image_cropper {
   /*  Function: __construct
       includes the setup file for the plugin
   */
    function __construct() {
		$this->location = 'http://plugins.tbkcreative.com/extend/plugins/custom-image-cropper/update';
		$this->slug = 'custom-image-cropper';
    }
    /* Function: form_options
       Shows a list of current image sizes
   */
  function form_options() {
	echo '<h3>Current Image Sizes</h3>';
	global $_wp_additional_image_sizes;
	foreach (get_intermediate_image_sizes() as $s) {
	    echo $s;
	    if (isset($_wp_additional_image_sizes[$s])) {
		echo ': ' . $width = intval($_wp_additional_image_sizes[$s]['width']);
		echo 'x' . $height = intval($_wp_additional_image_sizes[$s]['height']);
	    } else {
		echo ': ' . $width = get_option($s . '_size_w');
		echo 'x' . $height = get_option($s . '_size_h');
	    }
	    echo '<br/>';
	}
    }

}

?>
