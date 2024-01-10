<?php

namespace BitCode\WELZP\Admin;

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
        add_action('cronDailyEvent', [Handler::class, 'getPeoplesForms']);
    }
}
