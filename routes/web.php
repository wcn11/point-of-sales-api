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

    $router->get('order-online', 'OrderOnlineController@all');

    $router->get('order-online/{id}', 'OrderOnlineController@getOrderById');

    $router->post('order-online/{id}', 'OrderOnlineController@updateOrderToSuccess');

    $router->get('products/{id}/{name}', 'ProductController@getProductsByCategoryId');
    $router->get('add-product/{id}', 'ProductController@addProduct');

    $router->get('stocks', 'StockController@index');
    $router->get('stock/{no}', 'StockController@getStockByNo');

    $router->get('customers/{id}/category', 'CustomerController@getCustomerByCategoryId');
    $router->get('customers/{id}', 'CustomerController@getCustomerById');
    $router->put('customers/{id}', 'CustomerController@updateCustomer');
    $router->get('customers', 'CustomerController@getCustomerByCategoryId');
    $router->post('customers', 'CustomerController@storeCustomer');

    $router->post('pay', 'PayController@pay');
    $router->get('pay/{id}/invoice', 'PayController@getInvoice');

    $router->get('sales-report', 'SalesController@index');
});

$router->group(['prefix' => "admin", "namespace" => "Admin"], function () use ($router){

    $router->group(['middleware' => "auth:api-admin"], function () use ($router){

        $router->get('db-lists', "AccurateController@db_lists");

        $router->post('set-database-session/{id}', "AccurateController@getSessionId");

        $router->get('categories', "CategoryController@all");

        $router->get('items', "ProductController@index");

        $router->get('category/{id}/products', "CategoryController@getProductsByCategoryId");

        $router->post('items/{id}', "ProductController@updateProduct");

        $router->get('items/stock/{id}/product', "ProductController@getUserStockByProductId");

        $router->post('items', "ProductController@add");

        $router->get('items/check/{no}', "ProductController@check");

        $router->get('items/list-unit', "ProductController@listUnit");

        $router->get('items/categories', "ProductController@listCategory");

        $router->post('items/stock/update', "ProductController@updateStock");

        $router->get('sync', "ProductController@sync");

        $router->get('/sync/stock/{no}/user/{userId}', "ProductController@syncStockByProductId");

        $router->get('/sync/{id}/price', "ProductController@syncPriceByProductId");

        //user add
        $router->get("branches-lists", "UserController@branchesLists");

        $router->get("warehouse-lists", "UserController@warehouseLists");

        $router->get("customer-category-lists", "UserController@customerCategoryLists");

        $router->get("customer-default-lists", "UserController@customerDefaultLists");

        $router->get("glaccount-lists", "UserController@glaccountLists");

        $router->post("user", "UserController@saveUser");
        //end of user add

        $router->get('users', "UserController@all");
        $router->get('user/{id}', "UserController@getUserById");
        $router->put('user/{id}', "UserController@updateUser");

        $router->put('users/active', "UserController@updateActive");
        $router->put('users/admin', "UserController@updateAdmin");

        $router->post('logout', 'AuthController@logout');
    });

    $router->post('login', 'AuthController@login');
});

$router->post('online-order', 'OrderOnlineController@onlineOrder');

$router->post('login', 'AuthController@login');
$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');
$router->get('stock/{key}/download', 'StockController@download');
