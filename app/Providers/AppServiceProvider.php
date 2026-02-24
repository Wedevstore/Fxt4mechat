<?php
namespace App\Providers;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        Paginator::useBootstrap();
        // Schema::defaultStringLength(191);

        $this->app->singleton('settings', function () {
            return collect();
        });
        $this->app->singleton('languages', function () {
            return collect();
        });
        $this->app->singleton('currencies', function () {
            return collect();
        });
        $this->app->singleton('permission_list', function () {
            return collect();
        });

        //load migration for subdirectory
        $mainPath       = database_path('migrations');
        $directories    = glob($mainPath . '/*' , GLOB_ONLYDIR);
        $paths          = array_merge([$mainPath], $directories);
        $this->loadMigrationsFrom($paths);

        $this->loadMigrationsFrom(base_path('app/Addons/Webhook/migrations'));

        $this->loadMigrationsFrom(base_path('app/Addons/EmiroSync/migrations'));

        $this->loadMigrationsFrom(base_path('app/Addons/SaleBotECommerce/migrations'));
    }
    
}
