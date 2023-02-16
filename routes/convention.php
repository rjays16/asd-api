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

$router->group(['prefix' => 'convention'], function() use ($router) {
    $router->post('login', 'AuthController@loginMember');

    $router->group(['prefix' => 'password'], function() use ($router) {
        $router->post('reset-request', 'RequestPasswordController@sendResetLinkEmail');
    });

    $router->group(['prefix' => 'register'], function() use ($router) {     
        $router->group(['prefix' => 'delegate'], function() use ($router) {
            $router->post('validate-email', 'RegistrationController@validateEmail');
            $router->post('validate-pds-number', 'RegistrationController@validatePDSNumber');
            $router->post('', 'RegistrationController@register');
            $router->post('asd', 'RegistrationController@registerASD');
            $router->post('non-asd', 'RegistrationController@registerNonASD');
            $router->post('register-fellow', 'RegistrationController@registerResidentFellow');
        });

        $router->group(['prefix' => 'payment'], function() use ($router) {
            $router->post('ideapay', 'IdeapayController@create');
        });
    });

    $router->group(['prefix' => 'orders'], function() use ($router) {
        $router->post('rates', 'OrderController@calculateRates');
    });

    $router->group(['prefix' => 'rates'], function() use ($router) {
        $router->post('convert', 'OrderController@convertAmount');
    });

    $router->group(['prefix' => 'countries'], function() use ($router) {
        $router->get('', 'CountryController@getCountries');
    });

    $router->group(['middleware' => 'auth'], function() use ($router) {
        $router->post('logout', 'AuthController@logout');

        $router->group(['prefix' => 'user'], function() use ($router) {
            $router->get('', 'UserController@getUser');
        });

        $router->group(['prefix' => 'payments'], function () use ($router) {
            $router->get('{member_id}', 'PaymentController@getPaymentHistory');
        });

        $router->group(['prefix' => 'orders'], function () use ($router) {
            $router->get('',  'OrderController@getUserOrders');
            $router->get('{id}', 'OrderController@getOrder');
        });

        $router->group(['prefix' => 'abstract'], function() use ($router) {
            $router->get('', 'AbstractController@getUserAbstracts');
            $router->post('', 'AbstractController@create');
            $router->get('categories', 'AbstractController@getCategories');
            $router->get('study-designs', 'AbstractController@getStudyDesigns');
            $router->get('{id}', 'AbstractController@getAbstract');
        });

        $router->group(['prefix' => 'member'], function () use ($router) {
            $router->post('ideapay', 'IdeapayController@create');
            $router->group(['prefix' => '{id}/edit'], function () use ($router) {
                $router->post('', 'AuthController@update');
                $router->post('field', 'AuthController@updateField');
            });
        });
    });
});

$router->group(['prefix' => 'web'], function () use ($router) {
    $router->get('payment', 'IdeapayController@verifyOrderStatus');
});