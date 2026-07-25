<?php

namespace BitCode\WELZP\Admin;

use BitCode\WELZP\Admin\Log\Handler as LogHandler;
use BitCode\WELZP\Admin\ZohoPeople\Handler;

class AdminHooks
{
    public function __construct()
    {
        /*
        * Intentionally empty
        */
    }
    public function register()
    {
        $dirs = new \FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $serviceName = basename($dirInfo);
                if (
                    file_exists(__DIR__ . '/' . $serviceName)
                    && file_exists(__DIR__ . '/' . $serviceName . '/Hooks.php')
                ) {
                    $hooks = "BitCode\\WELZP\\Admin\\{$serviceName}\\Hooks";
                    if (method_exists($hooks, 'registerHooks')) {
                        (new $hooks())->registerHooks();
                    }
                }
            }
        }
        if (!wp_next_scheduled('cronDailyEvent')) {
            wp_schedule_event(time(), 'daily', 'cronDailyEvent');
        }
        add_action('cronDailyEvent', [$this, 'runDailySync']);

        add_filter('cron_schedules', [$this, 'addMonthlySchedule']);
        if (!wp_next_scheduled('cronMonthlyLogCleanup')) {
            wp_schedule_event(time(), 'bitwelzp_monthly', 'cronMonthlyLogCleanup');
        }
        add_action('cronMonthlyLogCleanup', [$this, 'runMonthlyLogCleanup']);
    }

    //WP core ships no monthly interval; register one for the log cleanup event
    public function addMonthlySchedule($schedules)
    {
        if (!isset($schedules['bitwelzp_monthly'])) {
            $schedules['bitwelzp_monthly'] = [
                'interval' => 30 * DAY_IN_SECONDS,
                'display'  => __('Once Monthly', 'bitwelzp'),
            ];
        }
        return $schedules;
    }

    //Cron callback: drop activity-log entries older than 30 days
    public function runMonthlyLogCleanup()
    {
        LogHandler::purgeOld(30);
    }

    //Cron callback: run the clinician sync via an instance (constructor loads auth) with no HTTP/JSON output
    public function runDailySync()
    {
        (new Handler())->syncEmployees();
    }
}
