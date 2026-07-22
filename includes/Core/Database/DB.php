<?php

/**
 * Class For Database Migration
 *
 * @category Database
 * @author   BitCode Developer <developer@bitcode.pro>
 */

namespace BitCode\WELZP\Core\Database;

/**
 * Database Migration
 */
final class DB
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;
        global $bitwelzp_db_version;
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if (!empty($wpdb->collate)) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }
        $table_schema = array(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bitwelzp_zoho_people_auth_details` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `auth_details` LONGTEXT DEFAULT NULL, /* form_id = 0 means all/app */
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bitwelzp_zoho_people_employee_info` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `zoho_id` varchar(255) DEFAULT NULL,
                `employee_id` varchar(255) DEFAULT NULL,
                `email_Id` varchar(255) DEFAULT NULL,
                `employee_status` varchar(255) DEFAULT NULL,
                `headshot_download_url` varchar(255) DEFAULT NULL,
                `fname` varchar(255) DEFAULT NULL,
                `lname` varchar(255) DEFAULT NULL,
                `preferred_name_nickname` varchar(255) DEFAULT NULL,
                `clinical_title` varchar(255) DEFAULT NULL,
                `medical_qualification` LONGTEXT DEFAULT NULL,
                `designation` LONGTEXT DEFAULT NULL,
                `skills`LONGTEXT DEFAULT NULL,
                `advanced_degree_from` varchar(255) DEFAULT NULL,
                `languages` varchar(255) DEFAULT NULL,
                `certifications` LONGTEXT DEFAULT NULL,
                `cultural_competency` LONGTEXT DEFAULT NULL,
                `public_bio` LONGTEXT DEFAULT NULL,
                `licensed_in` varchar(255) DEFAULT NULL,
                `allow_telehealth_access` varchar(255) DEFAULT NULL,
                `post_id` bigint(20)  DEFAULT NULL,
                `page_status` varchar(255)  DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}bitwelzp_form_details` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `form_details` LONGTEXT DEFAULT NULL, 
                `created_at` date DEFAULT NULL,
                `updated_at` date DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $collate;",
        );

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        foreach ($table_schema as $table) {
            dbDelta($table);
        }

        update_site_option(
            'bitwelzp_db_version',
            $bitwelzp_db_version
        );
    }
}
