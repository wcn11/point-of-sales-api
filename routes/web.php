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
    return "Halo Hackers... :)";
});
$router->group(['middleware' => ['auth:api']], function () use ($router){
    $router->get('me', 'AuthController@me');

    $router->get('categories', 'CategoryController@all');

    $router->get('products/{id}', 'ProductController@getProductsByCategoryId');
    $router->get('add-product/{id}', 'ProductController@addProduct');


    $router->get('stock', 'StockController@index');

    $router->get('customers/{id}/category', 'CustomerController@getCustomerByCategoryId');
    $router->get('customers/{id}', 'CustomerController@getCustomerById');
    $router->put('customers/{id}', 'CustomerController@updateCustomer');
    $router->get('customers', 'CustomerController@getCustomerByCategoryId');
    $router->post('customers', 'CustomerController@storeCustomer');

    $router->post('pay', 'PayController@pay');
    $router->get('pay/{id}/invoice', 'PayController@getInvoice');

    $router->get('sales-report', 'SalesController@index');
});

$router->post('login', 'AuthController@login');
$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');
$router->get('stock/{key}/download', 'StockController@download');
