<?php

namespace Omadonex\Vaac\Providers;

use Illuminate\Support\ServiceProvider;

class VaacServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $pathRoot = realpath(__DIR__.'/../..');

        $this->loadViewsFrom("$pathRoot/resources/views", 'vaac');
        $this->loadTranslationsFrom("$pathRoot/resources/lang", 'vaac');
        $this->loadMigrationsFrom("$pathRoot/database/migrations");

        $this->publishes([
            "$pathRoot/config/vaac.php" => config_path('vaac.php'),
        ], 'config');
        $this->publishes([
            "$pathRoot/resources/views" => resource_path('views/vendor/vaac'),
        ], 'views');
        $this->publishes([
            "$pathRoot/resources/lang" => resource_path('lang/vendor/vaac'),
        ], 'translations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
