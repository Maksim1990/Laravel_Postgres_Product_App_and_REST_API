<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/norights',function(){
    return view('includes.norights');
});

Route::group(['middleware' => ['web','auth','admin']], function () {
    Route::resource('/products', 'ProductController');
    Route::resource('/attachment', 'AttachmentController');
    Route::get('/', 'ProductController@index')->name('index');
    Route::get('/upload', 'ProductController@upload')->name('upload');
    Route::get('/create', 'ProductController@create')->name('create');
    Route::get('/edit/{id}', 'ProductController@edit')->name('edit_product');
    Route::get('/import/{type}', 'ProductController@import')->name('import');
    Route::post('/import/{type}', 'ProductController@importFile')->name('import_file');
    Route::post('/upload/files', 'ProductController@uploadFiles')->name('upload_files');
    Route::get('/category/{id}', 'CategoryController@index')->name('category');


    Route::post('/delete_attachment_ajax', 'AttachmentController@ajaxDeleteAttachment')->name('delete_attachment_ajax');
    Route::post('/update_caption_ajax', 'ProductController@ajaxUpdateCaption')->name('update_caption_ajax');
    Route::post('/get_categories_ajax', 'CategoryController@ajaxGetCategories')->name('get_categories_ajax');
    Route::post('/check_attached_resource_ajax', 'AttachmentController@ajaxCheckResources')->name('check_attached_resource_ajax');
    Route::post('/delete_categories_ajax', 'CategoryController@ajaxDeleteCategories')->name('delete_categories_ajax');
});

