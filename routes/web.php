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
$router->post('/security/tokenService',           'SecurityController@sendRequestTokenService');  //solicitar token service
$router->post('/security/whtokenService',           'SecurityController@whReceiveTokenService');  //Webhook  recibir respusta de Core
$router->post('/security/getTokenService',           'SecurityController@getTokenService');       //ApiGateway consulta


// Inquerie -> $router->post('/inquiries/transactionInfo',           'TransactionsController@transactionInfo');  //consultar transacciones de CI CO
$router->post('/transaction/transaction',                'TransactionsController@transaction'); // transaccionar con CI y CO
$router->post('/transaction/reverse',                'TransactionsController@reverse'); // poder rever
