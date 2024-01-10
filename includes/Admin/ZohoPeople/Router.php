<?php

namespace BitCode\WELZP\Admin\ZohoPeople;

use BitCode\WELZP\Core\Util\Route;

final class Router
{
    public static function registerAjax()
    {
        Route::post('generate_token', [Handler::class, 'generateToken']);
        Route::post('integration_save', [Handler::class, 'integrationSave']);
        Route::post('integration_update', [Handler::class, 'integrationUpdate']);
        Route::get('get_auth_details', [Handler::class, 'getAuthDetails']);
        Route::post('get_peoples_forms', [Handler::class,'getPeoplesForms']);
        Route::post('get_all_employees', [Handler::class,'getAllEmployees']);
        Route::get('post_content', [Handler::class,'getAllEmployees']);
        Route::post('delete_employees', [Handler::class,'deleteEmployees']);
        Route::post('review_data_save', [Handler::class,'saveReviews']);
        Route::post('delete_form_details', [Handler::class,'deleteReviews']);
        Route::post('review_approve', [Handler::class,'approveReview']);
        Route::post('review_update', [Handler::class, 'updateReview']);
        Route::post('page_active', [Handler::class,'handlePageStatus']);
    }
}
