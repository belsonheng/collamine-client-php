<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', 'HomeController@Index');

Route::post('/linechart', 'HomeController@RetrieveByCrawledDate');

Route::get('/total', 'HomeController@RetrieveTotal');

Route::get('/documents', 'HomeController@RetrieveDoc');

?>
