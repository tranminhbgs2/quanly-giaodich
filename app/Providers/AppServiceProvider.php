<?php

namespace App\Providers;

use App\Repositories\Agent\AgentRepo;
use App\Repositories\Bank\BankRepo;
use App\Repositories\BankAccount\BankAccountRepo;
use App\Repositories\Category\CategoryRepo;
use App\Repositories\Category\DepartmentRepo;
use App\Repositories\Customer\CustomerRepo;
use App\Repositories\HoKinhDoanh\HoKinhDoanhRepo;
use App\Repositories\Log\LogRepo;
use App\Repositories\MoneyComesBack\MoneyComesBackRepo;
use App\Repositories\Pos\PosRepo;
use App\Repositories\Transaction\TransactionRepo;
use App\Repositories\Transfer\TransferRepo;
use App\Repositories\User\UserRepo;
use App\Repositories\WithdrawPos\WithdrawPosRepo;
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
        $this->app->singleton(CustomerRepo::class);
        $this->app->singleton(AgentRepo::class);
        $this->app->singleton(BankRepo::class);
        $this->app->singleton(BankAccountRepo::class);
        $this->app->singleton(CategoryRepo::class);
        $this->app->singleton(DepartmentRepo::class);
        $this->app->singleton(HoKinhDoanhRepo::class);
        $this->app->singleton(LogRepo::class);
        $this->app->singleton(MoneyComesBackRepo::class);
        $this->app->singleton(PosRepo::class);
        $this->app->singleton(TransactionRepo::class);
        $this->app->singleton(TransferRepo::class);
        $this->app->singleton(WithdrawPosRepo::class);
        $this->app->singleton(UserRepo::class);


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
            // Log::info('sql to: '. json_encode($query->sql));
        });
    }
}
