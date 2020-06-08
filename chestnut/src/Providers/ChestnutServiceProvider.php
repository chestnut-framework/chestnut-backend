<?php

namespace Chestnut\Providers;

use Chestnut\Auth\ApiGuard;
use Chestnut\Auth\Nuts\Permission;
use Chestnut\Auth\Nuts\Role;
use Chestnut\Dashboard\Shell;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ChestnutServiceProvider extends ServiceProvider
{
    protected $commands = [
        'Chestnut\Command\NutMakeCommand',
    ];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('shell', Shell::class);
        $this->commands($this->commands);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/chestnut.php' => config_path('chestnut.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../assets' => public_path('vendor/chestnut'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/../translates' => resource_path('lang/vendor/chestnut'),
        ], 'translate');

        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadViewsFrom(__DIR__ . '/../views/', 'chestnut');
        $this->loadTranslationsFrom(__DIR__ . '/../translates', 'chestnut');

        Auth::extend(
            'chestnut',
            function ($app, $name, array $config) {
                return new ApiGuard(Auth::createUserProvider($config['provider']), $app['request']);
            }
        );

        $shell = $this->app->shell;

        $shell->nutsIn($this->app->config->get('chestnut.dashboard.nutsIn', app_path('Nuts')));
        $shell->nuts([new Role(), new Permission()]);

        // Uploader::registerRoute($this->app->router);
        Gate::before(function ($user) {
            return $user->hasRole('Chestnut Manager') ? true : null;
        });

    }
}
