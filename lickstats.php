<?php
/*
 * @package Lickstats
 */
/*
Plugin Name: Lickstats
Plugin URI: https://lickstats.com/
Description: The Lickstats WordPress plugin is used to add the Lickstats plugin to your website.
Version: 1.7
Authors: Alexandre Leclair and Sun Knudsen
Author URI: https://lickstats.com/
License: GPLv2 or later
Text Domain: lickstats
*/

//This plugin was inspired by Akismet

// Make sure we don’t expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there!  I\’m just a plugin, not much I can do when called directly.';
	exit;
}

define('LICKSTATS_VERSION', '1.7');
define('LICKSTATS_MINIMUM_WP_VERSION', '3.7');
define('LICKSTATS_PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook( __FILE__, array( 'Lickstats', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Lickstats', 'plugin_deactivation' ) );

require_once( LICKSTATS_PLUGIN_DIR . 'class.lickstats.php' );

add_action('init', array('Lickstats', 'init'));

if (is_admin() || (defined( 'WP_CLI' ) && WP_CLI)) {
	require_once(LICKSTATS_PLUGIN_DIR . 'class.lickstats-admin.php');
	add_action('init', array('Lickstats_Admin', 'init'));
}
