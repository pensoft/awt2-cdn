<?php

namespace App\Providers;

use App\Services\ImageService;
use App\Services\MediaService;
use Illuminate\Routing\UrlGenerator;
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
        if(env('FORCE_HTTPS')) {
            $this->app['request']->server->set('HTTPS', true);
        }

        $this->app->bind('ImageService', function($app) {
            return new ImageService();
        });

        $this->app->bind('MediaService', function($app) {
            return new MediaService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        if(env('FORCE_HTTPS')) {
            $url->forceScheme('https');
        }
    }
}
