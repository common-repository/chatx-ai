<?php
/**
 * Plugin Name:       ChatX Search
 * Plugin URI:        https://chatx.ai
 * Description:       Advanced Instant Search
 * Version:           0.2.1
 * Author:            chatx.ai
 * Author URI:        https://chatx.ai
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chatx-ai-search
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

define('CHATX_FILE', __FILE__ );

require plugin_dir_path( __FILE__ ) . 'includes/lib/config.php';
require plugin_dir_path( __FILE__ ) . 'includes/lib/helpers.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-chatx-ai-activator.php
 */
function activate_chatx_ai() {
	// If WooCommerce is not active / installed, abort.
	if ( ! class_exists( 'woocommerce' ) ) {
		wp_die('Sorry, but this plugin requires the WooCommerce to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chatx-ai-activator.php';
	Chatx_Ai_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chatx-ai-deactivator.php
 */
function deactivate_chatx_ai() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-chatx-ai-deactivator.php';
	Chatx_Ai_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_chatx_ai' );
register_deactivation_hook( __FILE__, 'deactivate_chatx_ai' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-chatx-ai.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

if(!function_exists('get_plugin_data')){
    require_once ABSPATH . "/wp-admin/includes/plugin.php";
}
$cx_plugin = new Chatx_Ai(get_plugin_data( __FILE__ ));
$cx_plugin->run();

/**
 * @return Chatx_Ai
 */
function CX() {
	global $cx_plugin;
	return $cx_plugin;
}

