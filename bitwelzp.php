<?php

/**
 * Plugin Name: Integration of Zoho People
 * Plugin URI:  bitwelzp.bitapps.pro
 * Description:
 * Version:     1.0.2
 * Author:      BitApps
 * Author URI:  bitapps.pro
 * Text Domain: bitwelzp
 * Requires PHP: 5.6
 * Domain Path: /languages
 * License:
 */

/***
 * If try to direct access  plugin folder it will Exit
 **/
if (!defined('ABSPATH')) {
    exit;
}
global $bitwelzp_db_version;
$bitwelzp_db_version = '1.2';

// Define most essential constants.
define('BITWELZP_VERSION', '1.0.2');
define('BITWELZP_PLUGIN_MAIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'includes/loader.php';
function bitwelzp_activate_plugin()
{
    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        wp_die(
            esc_html__('bitwelzp requires PHP version 5.6.', 'bitwelzp'),
            esc_html__('Error Activating', 'bitwelzp')
        );
    }
    do_action('bitwelzp_activation');
}

register_activation_hook(__FILE__, 'bitwelzp_activate_plugin');

function bitwelzp_uninstall_plugin()
{
    do_action('bitwelzp_uninstall');
}
register_uninstall_hook(__FILE__, 'bitwelzp_uninstall_plugin');
