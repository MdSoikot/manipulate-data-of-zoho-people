<?php
if (!defined('ABSPATH')) {
    exit;
}
$scheme = parse_url(home_url())['scheme'];

define('BITWELZP_PLUGIN_BASENAME', plugin_basename(BITWELZP_PLUGIN_MAIN_FILE));
define('BITWELZP_PLUGIN_DIR_PATH', plugin_dir_path(BITWELZP_PLUGIN_MAIN_FILE));
define('BITWELZP_ROOT_URI', set_url_scheme(plugins_url('', BITWELZP_PLUGIN_MAIN_FILE), $scheme));
define('BITWELZP_ASSET_URI', BITWELZP_ROOT_URI . '/assets');
define('BITWELZP_ASSET_JS_URI', BITWELZP_ROOT_URI . '/assets/js');
// Autoload vendor files.
require_once BITWELZP_PLUGIN_DIR_PATH . 'vendor/autoload.php';
// Initialize the plugin.
BitCode\WELZP\Plugin::load(BITWELZP_PLUGIN_MAIN_FILE);

