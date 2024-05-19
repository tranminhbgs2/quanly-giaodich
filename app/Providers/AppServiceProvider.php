<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            \App\Repositories\Customer\CustomerRepositoryInterface::class,
            \App\Repositories\Customer\CustomerRepo::class,
            \App\Repositories\FirmBank\FirmBankRepositoryInterface::class,
            \App\Repositories\FirmBank\FirmBankRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        //
        DB::listen(function ($query) {
            // $query->sql;
            // $query->bindings;
            // $query->time;
            //Log::info(json_encode($query->sql));
        });
    }
}
