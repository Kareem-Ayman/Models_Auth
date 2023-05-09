<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

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


Route::get('/cleareverything', 'VerificationController@cleareverything');


Route::get('/email_verify_done/{_code}', 'VerificationController@email_verify_done')->name('user.email_verify_done');


Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'getLang']
], function () {

    // register with user
    Route::group(['middleware' => ['auth_gurad:user_api']], function () {
        Route::post('/checklogin', 'LoginController@checkLogin')->name('user.checklogin');
        Route::post('/emailVerify', 'VerificationController@emailVerify')->name('user.emailVerify');
        Route::post('/phoneVerify', 'VerificationController@phoneVerify')->name('user.phoneVerify');
        Route::post('/phone_verify_done', 'VerificationController@phone_verify_done')->name('user.phone_verify_done');
        Route::post('/logout', 'LoginController@logout')->name('user.logout');

    });

    Route::group(['middleware' => 'guest'], function () {
        Route::post('/register', 'RegisterController@register')->name('user.register');
        Route::post('/refreshToken', 'LoginController@refreshToken')->name('user.refreshToken');
        Route::post('/login', 'LoginController@login')->name('user.login');
        Route::post('/send_forget', 'ForgetPasswordController@send_forget')->name('user.send_forget');
        Route::post('/receive_forget', 'ForgetPasswordController@receive_forget')->name('user.receive_forget');

    });



});




