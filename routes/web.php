<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->post('passwords','UserController@passwords');
$router->post('pwd','UserController@pwd');
$router->post('jyg','UserController@jyg');
$router->post('lc','UserController@lc');
$router->post('reg','UserController@reg');
$router->post('doLogin','UserController@doLogin');
$router->get('loginToken','UserController@loginToken');

$router->group(['middleware' => 'token'], function () use($router) {
    $router->get('my','UserController@my');
});

