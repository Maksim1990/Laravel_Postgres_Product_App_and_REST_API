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

//Route::get('/home', 'HomeController@index')->name('home');
Route::resource('/product', 'ProductController');
Route::resource('/attachment', 'AttachmentController');
Route::get('/', 'ProductController@index')->name('index');
Route::get('/upload', 'ProductController@upload')->name('upload');
Route::get('/create', 'ProductController@create')->name('create');
Route::get('/edit/{id}', 'ProductController@edit')->name('edit_product');
Route::get('/import/{type}', 'ProductController@import')->name('import');
Route::post('/import/{type}', 'ProductController@importFile')->name('import_file');
Route::post('/upload/files', 'ProductController@uploadFiles')->name('upload_files');

