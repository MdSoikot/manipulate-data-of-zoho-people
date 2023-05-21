<?php
namespace BitCode\WELZP\Core\Util;

/**
 * Class handling plugin uninstallation.
 *
 * @since 1.0.0
 * @access private
 * @ignore
 */
final class Uninstallation
{
    /**
     * Registers functionality through WordPress hooks.
     *
     * @since 1.0.0-alpha
     */
    public function register()
    {
        add_action('bitwelzp_uninstall', array($this, 'uninstall'));
    }

    public function uninstall()
    {
        global $wpdb;
        $tableArray = [
            $wpdb->prefix . "bitwelzp_log_details",
            $wpdb->prefix . "bitwelzp_people",
            $wpdb->prefix . "bitwelzp_zoho_people_auth_details",
            // $wpdb->prefix . "bitwelzp_zoho_people_employee_info",
        ];
        foreach ($tableArray as $tablename) {
            $wpdb->query("DROP TABLE IF EXISTS $tablename");
        }
        $columns = ["bitwelzp_db_version", "bitwelzp_installed", "bitwelzp_version"];
        foreach ($columns as $column) {
            $wpdb->query("DELETE FROM `{$wpdb->prefix}options` WHERE option_name='$column'");
        }
    }
}
