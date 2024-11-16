<?php

use Illuminate\Support\Facades\Route;

Route::get('/', '\App\Http\Controllers\MediaUploaderController@index');
Route::post('/any-upload', '\App\Http\Controllers\MediaUploaderController@anyUpload')->name('any-upload');
Route::post('/image-upload', '\App\Http\Controllers\MediaUploaderController@imageUpload')->name('image-upload');
Route::post('/webp-upload', '\App\Http\Controllers\MediaUploaderController@webpUpload')->name('webp-upload');
Route::post('/url-upload', '\App\Http\Controllers\MediaUploaderController@urlUpload')->name('url-upload');
Route::post('/base-upload', '\App\Http\Controllers\MediaUploaderController@baseUpload')->name('base-upload');
Route::post('/content-upload', '\App\Http\Controllers\MediaUploaderController@contentUpload')->name('content-upload');
Route::post('/thumb-upload', '\App\Http\Controllers\MediaUploaderController@thumbUpload')->name('thumb-upload');
