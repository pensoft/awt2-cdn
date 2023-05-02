<?php

use App\Services\ImageServiceFacade;
use App\Services\MediaServiceFacade;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Route::get('appRoutes', function() {
    \Artisan::call('route:list');
    return "<pre>" . \Artisan::output() . "</pre>";
});

Route::any('/image/{id}/{any?}', function (Request $request, $id, $any = null) {
    return ImageServiceFacade::createProcessor($request, $id, $any)->toMedia();
})->where('any', '.*')->name('image.resize');

Route::any('/media/{id}', function (Request $request, $id, $any = null) {
    return MediaServiceFacade::createProcessor($request, $id)->toMedia();
})->where('any', '.*')->name('media');


