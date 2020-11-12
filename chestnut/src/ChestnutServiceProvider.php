<?php

namespace Chestnut;

use Chestnut\Auth\ApiGuard;
use Chestnut\Auth\Nuts\Manager;
use Chestnut\Auth\Nuts\Permission;
use Chestnut\Auth\Nuts\Role;
use Chestnut\Dashboard\Shell;
use Chestnut\Dashboard\Uploader\Uploader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ChestnutServiceProvider extends ServiceProvider
{
    protected $commands = [
        Command\NutMakeCommand::class,
        Command\NutInstallCommand::class,
        Command\NutManagerCommand::class,
        Command\NutRoleCommand::class,
    ];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('shell', Shell::class);
        $this->mergeConfigFrom(__DIR__ . "/config/chestnut.php", "chestnut");

        if (app()->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/chestnut.php' => config_path('chestnut.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/config/auth.php' => config_path('auth.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/assets' => public_path('/'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/translates' => resource_path('lang/vendor/chestnut'),
        ], 'translate');

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__ . '/views/', 'chestnut');
        $this->loadTranslationsFrom(__DIR__ . '/translates', 'chestnut');
        $this->loadMigrationsFrom(__DIR__ . "/migrations");

        if (!app()->runningInConsole()) {
            app("shell")->nutsIn($this->app->config->get('chestnut.dashboard.nutsIn', app_path('Nuts')));

            if (config('chestnut.auth.rbac', false)) {
                app("shell")->nuts([new Manager(), new Role(), new Permission()]);
            }

            Uploader::registerRoute($this->app->router);

            Auth::extend(
                'chestnut',
                function ($app, $name, array $config) {
                    return new ApiGuard(Auth::createUserProvider($config['provider']), $app['request']);
                }
            );

            Gate::before(function ($user) {
                return $user->hasRole('Chestnut Manager') ? true : null;
            });
        }

    }
}
