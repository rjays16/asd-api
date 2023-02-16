<?php

// use Illuminate\Support\Facades\Mail;
// use App\Mail\Invoice;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'password'], function() use ($router) {
    $router->get('reset', 'ResetPasswordController@showResetForm');
    $router->post('reset', ['as' => 'password.reset', 'uses' => 'ResetPasswordController@reset']);
});

$router->group(['prefix' => 'admin'], function() use ($router) {
    $router->post('login', 'AuthController@loginAdmin');

    // For testing the invoice in Local
    // $router->get('/payment', function () {
    //     $user = App\Models\User::where('id', 2104)->with('member.type')->first();
    //     $payment = App\Models\Payment::where('id', 1)->first();
    //     Mail::to($user->email)->send(new Invoice($user, $payment));
    //     return new App\Mail\Invoice($user, $payment);
    // });

    $router->group(['prefix' => 'password'], function() use ($router) {
        $router->post('reset-request', 'RequestPasswordController@sendResetLinkEmail');
    });

    $router->group(['middleware' => 'auth'], function() use ($router) {
        $router->post('logout', 'AuthController@logout');

        $router->group(['prefix' => 'user'], function() use ($router) {
            $router->get('', 'UserController@getUser');
        });


        $router->group(['prefix' => 'members'], function() use ($router) {
            $router->get('', 'ConventionMemberController@getConventionMembers');
            $router->get('pending', 'ConventionMemberController@getPending');
            $router->get('active', 'ConventionMemberController@getActive');
            $router->get('paid', 'ConventionMemberController@getPaid');

            $router->group(['prefix' => '{id}'], function() use ($router) {
                $router->group(['prefix' => 'profile'], function() use ($router) {
                    $router->get('', 'ConventionMemberController@getConventionMember');
                    $router->post('', 'ConventionMemberController@update');
                    $router->delete('', 'ConventionMemberController@delete');
                });
                $router->post('resendPaymentEmail', 'PaymentController@resendPaymentEmail');
            });

            $router->post('import', 'ConventionMemberController@import');
            $router->group(['prefix' => 'export'], function() use ($router) {
                $router->get('template', 'ConventionMemberController@exportTemplate');
                $router->get('active', 'ConventionMemberController@exportActive');
            });
        });

        $router->group(['prefix' => 'speakers'], function() use ($router) {
            $router->get('', 'SpeakerController@getSpeakers');

            $router->group(['prefix' => '{id}'], function() use ($router) {
                $router->group(['prefix' => 'profile'], function() use ($router) {
                    $router->get('', 'SpeakerController@getSpeaker');
                    $router->post('', 'SpeakerController@update');
                    $router->delete('', 'SpeakerController@delete');
                });
            });

            $router->post('import', 'SpeakerController@import');
            $router->group(['prefix' => 'export'], function() use ($router) {
                $router->get('template', 'SpeakerController@exportTemplate');
            });
        });

        $router->group(['prefix' => 'delegates'], function() use ($router) {
            $router->get('', 'DelegateController@getDelegates');

            $router->group(['prefix' => '{id}'], function() use ($router) {
                $router->group(['prefix' => 'profile'], function() use ($router) {
                    $router->get('', 'DelegateController@getDelegate');
                    $router->post('', 'DelegateController@update');
                    $router->delete('', 'DelegateController@delete');
                });
            });

            $router->post('import', 'DelegateController@import');
            $router->group(['prefix' => 'export'], function() use ($router) {
                $router->get('template', 'DelegateController@exportTemplate');
            });
        });

        $router->group(['prefix' => 'abstracts'], function() use ($router) {
            $router->get('e-poster', 'AbstractController@getEPosterAbstracts');
            $router->get('free-papers', 'AbstractController@getFreePaperAbstracts');

            $router->group(['prefix' => '{id}'], function() use ($router) {
               $router->get('', 'AbstractController@getAbstract');
            });
        });
        $router->group(['prefix' => 'fees'], function() use ($router) {
            $router->get('', 'FeeController@getFees');
            $router->post('', 'FeeController@create');

            $router->group(['prefix' => '{id}'], function() use ($router) {
                $router->get('', 'FeeController@getFee');
                $router->post('', 'FeeController@update');
                $router->delete('', 'FeeController@delete');
            });
        });

        $router->group(['prefix' => 'payments'], function () use ($router) {
            $router->get('export', 'PaymentController@export');
            $router->get('', 'PaymentController@getPaymentLedger');
            $router->get('{member_id}', 'PaymentController@getPaymentHistory');

            $router->group(['prefix' => '{id}'], function () use ($router) {
                $router->delete('', 'PaymentController@delete');
            });
        });

        $router->group(['prefix' => 'orders'], function () use ($router) {
            $router->group(['prefix' => 'user'], function () use ($router) {
                $router->get('',  'OrderController@getUserOrders');
                $router->post('update',  'OrderController@update');
            });

            $router->group(['prefix' => '{id}'], function () use ($router) {
                $router->get('', 'OrderController@getOrder');
            });
        });

        $router->group(['prefix' => 'settings'], function () use ($router) {
            $router->group(['prefix' => 'ideapay'], function () use ($router) {
                $router->get('',  'ConfigController@getIdeapayFee');
                $router->post('',  'ConfigController@updateIdeapayFee');
            });

            $router->group(['prefix' => 'forex'], function () use ($router) {
                $router->get('active',  'ForExRateController@getActivePHPRate');
                $router->post('',  'ForExRateController@create');
            });

            $router->group(['prefix' => 'registration'], function () use ($router) {
                $router->get('',  'ConfigController@getRegistrationSwitch');
                $router->post('',  'ConfigController@updateRegistrationSwitch');
            });
        });
    });
});
