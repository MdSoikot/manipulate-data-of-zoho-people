<?php

namespace BitCode\WELZP\Admin\Log;

use BitCode\WELZP\Core\Util\Route;

final class Router
{
    public static function registerAjax()
    {
        Route::post('log/get', [Handler::class, 'get']);
        Route::post('log/delete', [Handler::class, 'delete']);
        Route::post('log/clear', [Handler::class, 'clear']);
    }
}
