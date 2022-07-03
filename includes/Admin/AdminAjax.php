<?php

namespace BitCode\WELZP\Admin;


use BitCode\WELZP\Core\Util\Route;

class AdminAjax
{
    public function __construct()
    {
        //
    }
    public function register()
    {
        if (strpos($_REQUEST['action'], 'bitwelzp') === false) {
            return;
        }
        $dirs = new \FilesystemIterator(__DIR__);
        foreach ($dirs as $dirInfo) {
            if ($dirInfo->isDir()) {
                $serviceName = basename($dirInfo);
                if (
                    file_exists(__DIR__ . '/' . $serviceName)
                    && file_exists(__DIR__ . '/' . $serviceName . '/Router.php')
                ) {
                    $routes = __NAMESPACE__ . "\\{$serviceName}\\Router";
                    if (method_exists($routes, 'registerAjax')) {
                        (new $routes())->registerAjax();
                    }
                }
            }
        }
        Route::post('erase_all', [$this, 'toggle_erase_all']);
        return;
    }

    public function toggle_erase_all($data)
    {
        if (!property_exists($data, 'toggle')) {
            wp_send_json_error(__('Toggle status can\'t be empty', 'bitwelzp'));
        }
        update_option('bitwelzp_erase_all', (bool)  $data->toggle);
        wp_send_json_success(__('Erase in delete toggled', 'bitwelzp'));
    }
}
