<?php
/**
  Plugin Name: Event Espresso - Certificate
  Plugin URI: http://eventespresso.com/
  Description: Certificate system for Event Espresso

  Version: 1.0

  Author: Seth Shoultes
  Author URI: http://www.eventespresso.com

  Copyright (c) 2011 Event Espresso  All Rights Reserved.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */
 
global $wpdb;
//global $espresso_path;
define( "ESPRESSO_CERTIFICATE_TABLE", $wpdb->prefix . 'events_certificate_templates');
define( "ESPRESSO_CERTIFICATE_PATH", "/" . plugin_basename( dirname( __FILE__ ) ) . "/" );
define( "ESPRESSO_CERTIFICATE_FULL_PATH", WP_PLUGIN_DIR . ESPRESSO_CERTIFICATE_PATH );
define( "ESPRESSO_CERTIFICATE_FULL_URL", WP_PLUGIN_URL . ESPRESSO_CERTIFICATE_PATH );
define( "ESPRESSO_CERTIFICATE_ACTIVE", TRUE );
define( "ESPRESSO_CERTIFICATE_VERSION", '1.0' );
define("EVENTS_CERTIFICATE_TEMPLATES", $wpdb->prefix . "events_certificate_templates");
//echo $espresso_path;
require_once('functions.php');
require_once('manager/index.php');
/*function event_espresso_certificate_config_mnu() {
}*/
//Install plugin
register_activation_hook( __FILE__, 'espresso_certificate_install' );
register_deactivation_hook( __FILE__, 'espresso_certificate_deactivate' );
//Deactivate the plugin
if ( !function_exists( 'espresso_certificate_deactivate' ) ){
    function espresso_certificate_deactivate() {
        update_option( 'espresso_certificate_active', 0 );
    }
}

//Install the plugin
if ( !function_exists( 'espresso_certificate_install' ) ){

    function espresso_certificate_install() {

        update_option( 'espresso_certificate_version', ESPRESSO_CERTIFICATE_VERSION );
        update_option( 'espresso_certificate_active', 1 );
        global $wpdb;

        $table_version = ESPRESSO_CERTIFICATE_VERSION;

       	$table_name = "events_certificate_templates";
    	$sql = "id int(11) unsigned NOT NULL AUTO_INCREMENT,
			certificate_name VARCHAR(100) DEFAULT NULL,
			certificate_file VARCHAR(100) DEFAULT 'basic.html',
			certificate_subject VARCHAR(250) DEFAULT NULL,
			certificate_content TEXT,
			certificate_logo_url TEXT,
			certificate_meta LONGTEXT DEFAULT NULL,
			wp_user int(22) DEFAULT '1',
			UNIQUE KEY id (id)";
		
	if ( ! function_exists( 'event_espresso_run_install' )) {
		require_once( EVENT_ESPRESSO_PLUGINFULLPATH . 'includes/functions/database_install.php' ); 		
	}
	event_espresso_run_install($table_name, $table_version, $sql);
		
		$certificate_options = array(
			'use_gravatar' => 'N',
			'use_name_badge' => 'N',
			'image_file' => 'certificate-bg.jpg',
			'background_color' => '000000',
			'enable_personal_qr_code' => 'Y',
			'show_venue' => 'Y',
			'show_map' => 'Y',
			'show_price' => 'Y',
			'show_espresso_footer' => 'Y'
		);

		add_option('espresso_certificate_settings', $certificate_options);
		
	}
	
}

//Export PDF Certificate
if (isset($_REQUEST['certificate_launch'])&&$_REQUEST['certificate_launch'] == 'true') {
	//echo espresso_certificate_launch($_REQUEST['id'], $_REQUEST['registration_id']);
}


wp_enqueue_style('espresso_certificate_menu', ESPRESSO_CERTIFICATE_FULL_URL . 'css/admin-menu-styles.css');

if (isset($_REQUEST['page']) && $_REQUEST['page']=='event_certificates') {
	wp_enqueue_style('espresso_certificates', ESPRESSO_CERTIFICATE_FULL_URL . 'css/admin-styles.css');
}