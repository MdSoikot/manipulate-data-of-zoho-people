<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Util\Route;

final class Router
{
    public function __construct()
    {
        //
    }


    public static function registerAjax()
    {
        Route::post('generate_token', [Handler::class, 'generate_token']);
        Route::post('integration_save', [Handler::class, 'integration_save']);
        Route::post('integration_update', [Handler::class, 'integration_update']);
        Route::get('get_auth_details', [Handler::class, 'get_auth_details']);
        Route::post('get_peoples_forms', [Handler::class,'get_peoples_forms']);
        Route::post('get_all_employees', [Handler::class,'get_all_employees']);
        Route::get('post_content', [Handler::class,'get_all_employees']);
        Route::post('delete_employees', [Handler::class,'delete_employees']);
        Route::post('review_data_save', [Handler::class,'review_data_save']);
        Route::post('delete_form_details', [Handler::class,'delete_form_details']);
        Route::post('review_approve', [Handler::class,'review_approve']);
        Route::post('review_update', [Handler::class, 'review_update']);
        Route::get('get_employee_data', [Handler::class, 'get_employee_data']);
        Route::post('page_active', [Handler::class,'page_active']);
    }
}
