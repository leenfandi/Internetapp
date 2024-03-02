<?php

namespace App\Providers;



use App\Aspects\CheckBeforAddAspect;
use App\Aspects\CheckBeforDeleteAspect;
use App\Aspects\CheckinAspect;
use App\Aspects\CheckinMultiAspect;
use App\Aspects\TransactionAspect;
use App\Aspects\CheckoutAspect;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Http\Middleware\AuthenticateAspect;
use Illuminate\Support\Facades\App;
use App\Services\FileService;
use App\Services\LoggingServices;
use App\Proxies\FileUploaderProxy;
use App\Proxies\FileDeleteProxy;
use App\Aspects\FileLockingAspect;
use App\Aspects\FileCheckDeletAspect;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Proxies\AddFileToGroupProxy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the AuthenticateAspect middleware to the auth.aspect key in the service container
    App::bind('auth.aspect', function () {
        return new AuthenticateAspect();
    });
    $this->app->bind('file.service', function () {
        return new FileService();
    });
    ///AuthenticateAspectFacade::handle($request, $next);

    $this->app->bind(FileController::class, function ($app) {

        $CheckoutAspect = $app->make(CheckoutAspect::class);
        $CheckinAspect = $app->make(CheckinAspect::class);
        $checkinMultiAspect = $app->make(CheckinMultiAspect::class);
        return new FileUploaderProxy($CheckoutAspect,$CheckinAspect,$checkinMultiAspect);
    });
    $this->app->bind(GroupController::class, function ($app) {
        $CheckBeforAddAspect = $app->make(CheckBeforAddAspect::class);
        $CheckBeforDeleteAspect = $app->make(CheckBeforDeleteAspect::class);
        return new AddFileToGroupProxy($CheckBeforAddAspect , $CheckBeforDeleteAspect);
    });


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        if($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
    }
}
