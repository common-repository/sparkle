<?php

/*
Plugin Name: Sparkle
Description: Delete unnecessary data to free valuable disk space.
Version: 0.0.1
Author: Engramium
Author URI: www.engramium.com
Text Domain: Sparkle


This plugin is provided "as is", without any warranty of any kind. This plugin is a switch knife. One the data is being deleted there isn't any way to recover those.

*/

if ( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed this file directly
}

function sparkle_optimizer_settings_link($action_links,$plugin_file){
	if ( $plugin_file == plugin_basename(__FILE__) ) {
		$settings_link = sprintf( '<a href="%s">Settings</a>', esc_url( admin_url( 'options-general.php?page=sparkle' ) ) );
		array_unshift( $action_links,$settings_link );
	}

	return $action_links;
}
add_filter('plugin_action_links','sparkle_optimizer_settings_link', 10, 2);

add_action( 'init', 'sparkle_load_textdomain' );

/**
 * Load plugin textdomain.
 */
function sparkle_load_textdomain() {
  load_plugin_textdomain( 'sparkle', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

if( is_admin() ) {
	require_once('sparkle-dashboard.php');
}
?>