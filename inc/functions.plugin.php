<?php 
function IPU_Install(){
	
	//enable default features on plugin activation
	$ipu_options = get_option ( 'ipu_options' );
	if ( empty( $ipu_options ) )
		update_option( 'ipu_options', array( 'allow_full_screen' => 1, 'auto_upload' => 1, 'width' => 640, 'height' => 480 ) );
}

?>