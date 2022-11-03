<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://divineinfosys.com
 * @since             1.0.0
 * @package           Banner_Tracking
 *
 * @wordpress-plugin
 * Plugin Name:       Banner Tracking
 * Plugin URI:        https://divineinfosys.com/
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Divine Infosys
 * Author URI:        https://divineinfosys.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       banner-tracking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BANNER_TRACKING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-banner-tracking-activator.php
 */
function activate_banner_tracking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-banner-tracking-activator.php';
	Banner_Tracking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-banner-tracking-deactivator.php
 */
function deactivate_banner_tracking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-banner-tracking-deactivator.php';
	Banner_Tracking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_banner_tracking' );
register_deactivation_hook( __FILE__, 'deactivate_banner_tracking' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-banner-tracking.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_banner_tracking() {

	$plugin = new Banner_Tracking();
	$plugin->run();

}
run_banner_tracking();
