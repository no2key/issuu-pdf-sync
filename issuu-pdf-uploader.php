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

require( IPU_DIR . '/inc/functions.tpl.php');
require( IPU_DIR . '/inc/class.client.php');
require( IPU_DIR . '/inc/class.admin.php');

// Init IPU
function IPU_Init() {
	global $ipu;
	
	// Load up the localization file if we're using WordPress in a different language
	// Place it in this plugin's "lang" folder and name it "IPU-[value in wp-config].mo"
	load_plugin_textdomain( 'IPU', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );
	
	// Init client
	$ipu['client'] = new IPU_Client();
	
	// Admin
	if ( is_admin() )
		$ipu['admin'] = new IPU_Admin();
}
add_action( 'plugins_loaded', 'IPU_Init' );
?>