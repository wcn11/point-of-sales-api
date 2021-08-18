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
    return $router->app->version();
});

//$router->group(['middleware' => 'cors'], function () use ($router){
//
//    $router->post('login', 'AuthController@login');
//});

$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');
$router->get('me', 'AuthController@me');

$router->get('categories', 'CategoryController@all');

$router->get('products/{id}', 'ProductController@getProductsByCategoryId');
$router->get('add-product/{id}', 'ProductController@addProduct');

$router->get('customers/{id}', 'CustomerController@getCustomerByCategoryId');
$router->post('customers', 'CustomerController@storeCustomer');

$router->post('pay', 'PayController@pay');
