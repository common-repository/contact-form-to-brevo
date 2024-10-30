<?php
/**
 * Plugin Name: Contact Form to Brevo
 * Description: Add Contact Form 7 Data to Brevo Contact lists.
 * Author: Sagar Giri
 * Plugin URI: https://wordpress.org/plugins/contactform-to-brevo
 * Author URI: https://www.linkedin.com/in/sagar-giri-5bb771130/
 * Version: 1.0.7
 * Tested up to: 6.6.1
 * Text Domain: contactform-to-brevo
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 **/
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Unauthorized');
}

//define constants
define("CFB_PSD_PATH", plugin_dir_path(__FILE__));
define("CFB_PSD_URL", plugin_dir_url(__FILE__));


//require once autoload
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */
function cfb_psd_activate_plugin(){
    \CFB_PSD\Base\CFB_PSD_Activate::activate();
}
register_activation_hook(__FILE__, 'cfb_psd_activate_plugin');

/**
 * The code that runs during plugin deactivation
 */
function cfb_psd_deactivate_plugin(){
    \CFB_PSD\Base\CFB_PSD_Deactivate::deactivate();
}
register_deactivation_hook(__FILE__, 'cfb_psd_deactivate_plugin');

if ( class_exists( 'CFB_PSD\\Init' ) ) {
    CFB_PSD\Init::register_services();
}