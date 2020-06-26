<?php

namespace App\Providers;

use App\Http\Services\RedisService;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->hasHeader('Authorization') || $request->hasHeader('authorization')) {
                $bearer = $request->hasHeader('Authorization') ? $request->header('Authorization', '') : $request->header('authorization');
                $bearer = str_replace('bearer ', '', $bearer);
                $bearer = str_replace('Bearer ', '', $bearer);
                // The Analytics API will check the Laravel Backend Redis Store for the existence of that token
                try {
                    $storedUserId = (new RedisService)->getData("auth:user:token:{$bearer}");

                    if (is_numeric($storedUserId)) {
                        try {
                            $output = User::whereId($storedUserId)->firstOrFail();
                        } catch (\Exception $e) {
                            $output = null;
                        }
                    } else {
                        $output = null;
                    }
                } catch (\Exception $e) {
                    $output = null;
                }
            } else {
                $output = null;
            }
            $request->merge([
                'user' => $output
            ]);
            return $output;
        });
    }
}
