<?php
/*
Plugin Name: Issuu PDF Uploader
Plugin URI: http://www.beapi.fr
Description: Synchronize WordPress and the Issuu PDF upload service
Version: 1.0
Author: Benjamin Niess
Author URI: http://www.benjamin-niess.fr
Text Domain: ipu
Domain Path: /languages/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'IPU_VERSION', '1.0' );
define( 'IPU_URL', plugins_url( '', __FILE__ ) );
define( 'IPU_DIR', dirname( __FILE__ ) );

define( 'IPU_API_KEY', "1gsyl7vk63qpwzuu7pzuyo5p7f8h80h8" );
define( 'IPU_SECRET_KEY', "42h72g5f3ckqkj8yisho80sxlt6v93tz" );

require( IPU_DIR . '/inc/functions.tpl.php');
require( IPU_DIR . '/inc/functions.plugin.php');
require( IPU_DIR . '/inc/class.client.php');
require( IPU_DIR . '/inc/class.admin.php');
require( IPU_DIR . '/inc/shortcodes.php');

// Activate Recommend a friend
register_activation_hook  ( __FILE__, 'IPU_Install' );

// Init IPU
function IPU_Init() {
	global $ipu, $ipu_options;
	
	// Load up the localization file if we're using WordPress in a different language
	// Place it in this plugin's "lang" folder and name it "IPU-[value in wp-config].mo"
	load_plugin_textdomain( 'IPU', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );
	
	$ipu_options = get_option ( 'ipu_options' );
	
	// Init client
	$ipu['client'] = new IPU_Client();
	
	// Admin
	//if ( is_admin() )
		$ipu['admin'] = new IPU_Admin();
}
add_action( 'plugins_loaded', 'IPU_Init' );
?>