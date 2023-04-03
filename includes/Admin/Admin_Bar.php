<?php

namespace BitCode\WELZP\Admin;

use BitCode\WELZP\Core\Util\DateTimeHelper;
use  BitCode\WELZP\Admin\ZohoPeople\Handler;

/**
 * The admin menu and page handler class
 */

class Admin_Bar
{
    public function register()
    {
        add_action('in_admin_header', [$this, 'RemoveAdminNotices']);
        add_action('admin_menu', array($this, 'AdminMenu'));
        add_action('admin_enqueue_scripts', array($this, 'AdminAssets'), 15);
    }


    /**
     * Register the admin menu
     *
     * @return void
     */
    public function AdminMenu()
    {
        $capability = apply_filters('bitwelzp_access_capability', 'manage_options');
        if (current_user_can($capability)) {
            add_menu_page(__('WellQor Zoho People', 'bitwelzp'), 'Zoho People', $capability, 'bitwelzp', array($this, 'RootPage'), 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" width="36.34" height="36.34" data-name="Layer 1"><defs/><circle cx="18.17" cy="18.17" r="16.2" fill="none" stroke="#000" stroke-miterlimit="10" stroke-width="1.5"/><path d="M27.06 9.47v2.62H16.32a7 7 0 0 0-6.91 5.7 6.51 6.51 0 0 0-.11 1V16.5a1.46 1.46 0 0 1 0-.3 7 7 0 0 1 1.53-4.11 7.09 7.09 0 0 1 5.49-2.62Z" class="cls-2"/><path d="M12 20.71a4.89 4.89 0 0 0-.15 1.21 4.34 4.34 0 0 0 .26 1.5 4.51 4.51 0 0 0 8.54 0 4.15 4.15 0 0 0 .25-1.46h2.5a7 7 0 1 1-14.07 0 7.19 7.19 0 0 1 .3-2 6.71 6.71 0 0 1 .56-1.32 7.81 7.81 0 0 1 .69-1 7.06 7.06 0 0 1 5.49-2.62h7v2.62h-7.44a3.61 3.61 0 0 0-1.59.34 4.65 4.65 0 0 0-1.55 1.24 4.36 4.36 0 0 0-.79 1.39.38.38 0 0 1 0 .1Z" class="cls-2"/></svg>'), 30);
        }
    }

    /**
     * Load the asset libraries
     *
     * @return void
     */
    public function AdminAssets($current_screen)
    {
        global $isbitwelzpLicActive;
        if (strpos($current_screen, 'bitwelzp') === false) {
            return;
        }
        if (wp_script_is('bitwelzp-vendors', 'registered')) {
            wp_deregister_script('bitwelzp-vendors');
        }
        if (wp_script_is('bitwelzp-runtime', 'registered')) {
            wp_deregister_script('bitwelzp-runtime');
        }
        if (wp_script_is('bitwelzp-admin-script', 'registered')) {
            wp_deregister_script('bitwelzp-admin-script');
        }

        if (wp_style_is('bitwelzp-styles', 'registered')) {
            wp_deregister_style('bitwelzp-styles');
        }
        $parsed_url = parse_url(get_admin_url());
        $site_url = $parsed_url['scheme'] . "://" . $parsed_url['host'];
        $site_url .= empty($parsed_url['port']) ? null : ':' . $parsed_url['port'];
        $base_path_admin =  str_replace($site_url, '', get_admin_url());

        wp_enqueue_script(
            'bitwelzp-vendors',
            BITWELZP_ASSET_JS_URI . '/vendors-main.js',
            null,
            BITWELZP_VERSION,
            true
        );

        wp_enqueue_script(
            'bitwelzp-runtime',
            BITWELZP_ASSET_JS_URI . '/runtime.js',
            null,
            BITWELZP_VERSION,
            true
        );

        if (wp_script_is('wp-i18n')) {
            $deps = array('bitwelzp-vendors', 'bitwelzp-runtime', 'wp-i18n');
        } else {
            $deps = array('bitwelzp-vendors', 'bitwelzp-runtime',);
        }

        wp_enqueue_script(
            'bitwelzp-admin-script',
            BITWELZP_ASSET_JS_URI . '/index.js',
            $deps,
            BITWELZP_VERSION,
            true
        );

        wp_enqueue_style(
            'bitwelzp-styles',
            BITWELZP_ASSET_URI . '/css/bitwelzp.css',
            null,
            BITWELZP_VERSION,
            'screen'
        );

        $all_people = [];
        $auth_details = (new Handler())->get_auth_details();
        $get_all_employees = (new Handler())->get_all_employees();
        $get_form_details=(new Handler())->get_form_details();
        $bitwelzp = apply_filters(
            'bitwelzp_localized_script',
            array(
                'nonce'     => wp_create_nonce('bitcffp_nonce'),
                'assetsURL' => BITWELZP_ASSET_URI,
                'baseURL'   => $base_path_admin . 'admin.php?page=bitwelzp#',
                'ajaxURL'   => admin_url('admin-ajax.php'),
                'allForms'  => $all_people,
                'erase_all'  => get_option('bitwelzp_erase_all'),
                'dateFormat'  => get_option('date_format'),
                'timeFormat'  => get_option('time_format'),
                'timeZone'  => DateTimeHelper::wp_timezone_string(),
                'integration_details' => $auth_details,
                'auth_details' => count((array)$auth_details)>0 ?$auth_details->auth_details : '',
                'all_employees' => $get_all_employees,
                'reviewsDetails' => $get_form_details,
                'redirect' => get_rest_url() . 'bitwelzp/redirect',

            )
        );


        if (get_locale() !== 'en_US' && file_exists(BITWELZP_PLUGIN_DIR_PATH . '/languages/generatedString.php')) {
            include_once BITWELZP_PLUGIN_DIR_PATH . '/languages/generatedString.php';
            $bitwelzp['translations'] = $bitwelzp_i18n_strings;
        }
        wp_localize_script('bitwelzp-admin-script', 'bitwelzp', $bitwelzp);
    }

    /**
     * Bitforms  apps-root id provider
     * @return void
     */
    public function RootPage()
    {
        require_once BITWELZP_PLUGIN_DIR_PATH . '/views/view-root.php';
    }

    public function RemoveAdminNotices()
    {
        global $plugin_page;
        if (strpos($plugin_page, 'bitwelzp') === false) {
            return;
        }
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
