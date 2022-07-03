<?php

namespace BitCode\WELZP;

/**
 * Main class for the plugin.
 *
 * @since 1.0.0-alpha
 */

use BitCode\WELZP\Core\Database\DB;
use BitCode\WELZP\Admin\Admin_Bar;
use BitCode\WELZP\Admin\AdminHooks;
use BitCode\WELZP\Core\Util\Request;
use BitCode\WELZP\Core\Util\Activation;
use BitCode\WELZP\Core\Util\Deactivation;
use BitCode\WELZP\Core\Util\Uninstallation;
use BitCode\WELZP\Core\Ajax\AjaxService;
use BitCode\WELZP\Integration\Integrations;

final class Plugin
{

    /**
     * Main instance of the plugin.
     *
     * @since 1.0.0-alpha
     * @var   Plugin|null
     */
    private static $instance = null;

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function initialize()
    {
        add_action('plugins_loaded', array($this, 'init_plugin'), 12);
        (new Activation())->activate();
        (new Deactivation())->register();
        (new Uninstallation())->register();
    }

    public function init_plugin()
    {
        add_action('init', array($this, 'init_classes'), 11);
        add_filter('plugin_action_links_' . plugin_basename(BITWELZP_PLUGIN_MAIN_FILE), array($this, 'plugin_action_links'));
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if (Request::Check('admin')) {
            (new Admin_Bar())->register();
        }
        if (Request::Check('ajax')) {
            new AjaxService();
        }
        (new AdminHooks())->register();
        (new Integrations())->registerHooks();
    }

    /**
     * Plugin action links
     *
     * @param  array $links
     *
     * @return array
     */
    public function plugin_action_links($links)
    {
        $links[] = '<a href="https://bitpress.pro/documentation" target="_blank">' . __('Docs', 'bitwelzp') . '</a>';

        return $links;
    }

    /**
     * Retrieves the main instance of the plugin.
     *
     * @since 1.0.0-alpha
     *
     * @return WELZP Plugin main instance.
     */
    public static function instance()
    {
        return static::$instance;
    }

    public static function update_tables()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        global $bitwelzp_db_version;
        $installed_db_version = get_site_option("bitwelzp_db_version");
        if ($installed_db_version != $bitwelzp_db_version) {
            DB::migrate();
        }
    }
    /**
     * Loads the plugin main instance and initializes it.
     *
     * @since 1.0.0-alpha
     *
     * @param string $main_file Absolute path to the plugin main file.
     * @return bool True if the plugin main instance could be loaded, false otherwise./
     */
    public static function load($main_file)
    {
        if (null !== static::$instance) {
            return false;
        }
        // static::update_tables();
        static::$instance = new static($main_file);
        static::$instance->initialize();
        return true;
    }
}
