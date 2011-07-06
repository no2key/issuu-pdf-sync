<?php
/*
Plugin Name: Issuu PDF Sync
Plugin URI: http://www.beapi.fr
Description: Synchronize WordPress and the Issuu PDF upload service
Version: 1.1
Author: Benjamin Niess
Author URI: http://www.benjamin-niess.fr
Text Domain: ips
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

define( 'IPS_VERSION', '1.1' );
define( 'IPS_URL', plugins_url( '', __FILE__ ) );
define( 'IPS_DIR', dirname( __FILE__ ) );

define( 'IPS_API_KEY', "1gsyl7vk63qpwzuu7pzuyo5p7f8h80h8" );
define( 'IPS_SECRET_KEY', "42h72g5f3ckqkj8yisho80sxlt6v93tz" );

require( IPS_DIR . '/inc/functions.tpl.php');
require( IPS_DIR . '/inc/functions.plugin.php');
require( IPS_DIR . '/inc/class.client.php');
require( IPS_DIR . '/inc/shortcodes.php');

if ( is_admin() )
	require( IPS_DIR . '/inc/class.admin.php');


// Activate Recommend a friend
register_activation_hook  ( __FILE__, 'IPS_Install' );

// Init IPS
function IPS_Init() {
	global $ips, $ips_options;
	
	// Load up the localization file if we're using WordPress in a different language
	// Place it in this plugin's "lang" folder and name it "ips-[value in wp-config].mo"
	load_plugin_textdomain( 'ips', false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );
	
	$ips_options = get_option ( 'ips_options' );
	
	// Init client
	$ips['client'] = new IPS_Client();
	
	// Admin
	if ( is_admin() )
		$ips['admin'] = new IPS_Admin();
}
add_action( 'plugins_loaded', 'IPS_Init' );
?>