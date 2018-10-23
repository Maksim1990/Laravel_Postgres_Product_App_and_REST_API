<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('user', 'UserController@getAuthenticatedUser');
    Route::get('{user_id}/products', 'API\ProductController@index');
    Route::get('{user_id}/products/{id}', 'API\ProductController@show');
    Route::post('{user_id}/products/import', 'API\ProductController@import');
    Route::post('{user_id}/products/create', 'API\ProductController@store');
    Route::post('{user_id}/products/{id}', 'API\ProductController@update');
    Route::resource('{user_id}/products', 'API\ProductController');

    Route::get('{user_id}/categories', 'API\CategoryController@index');
    Route::get('{user_id}/categories/{id}', 'API\CategoryController@show');
    Route::post('{user_id}/categories/create', 'API\CategoryController@store');
    Route::resource('{user_id}/categories', 'API\CategoryController');

    Route::get('{user_id}/attachments', 'API\AttachmentController@index');
    Route::patch('{user_id}/attachments/{id}/caption/update', 'API\AttachmentController@updateCaption');
    Route::resource('{user_id}/attachments', 'API\AttachmentController');
});