<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return 'Auth Service v1.0';
});


$router->post('/register', [
    'as' => 'authApiV1Register',
    'uses' => 'UserController@register'
]);

$router->post('/login', [
    'as' => 'authApiV1Login',
    'uses' => 'UserController@login'
]);

$router->post('/logout', [
    'as' => 'authApiV1Logout',
    'middleware' => 'auth',
    'uses' => 'UserController@logout'
]);

$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/user', [
        'as' => 'authApiV1GetUser',
        'uses' => 'UserController@getUser'
    ]);
});
