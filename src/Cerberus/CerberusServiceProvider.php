<?php namespace Michalisantoniou6\Cerberus;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Michalisantoniou6\Cerberus\Observers\UserObserver;

class CerberusServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('cerberus.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrationCommand::class
            ]);
        }

        $this->bladeDirectives();

        $this->registerUserObserver();
    }

    private function registerUserObserver() {
        $userModel = app(Config::get('cerberus.user'));
        $userModel::observe(UserObserver::class);
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
        if ( ! class_exists('\Blade')) {
            return;
        }

        //single tenant
        \Blade::directive('role', function ($expression) {
            return "<?php if (\\Cerberus::hasRole({$expression})) : ?>";
        });

        \Blade::directive('endrole', function ($expression) {
            return "<?php endif; // Cerberus::hasRole ?>";
        });

        \Blade::directive('permission', function ($expression) {
            return "<?php if (\\Cerberus::hasPermission({$expression})) : ?>";
        });

        \Blade::directive('endpermission', function ($expression) {
            return "<?php endif; // Cerberus::hasPermission ?>";
        });

        // Call to Cerberus::ability
        \Blade::directive('ability', function ($expression) {
            return "<?php if (\\Cerberus::ability({$expression})) : ?>";
        });

        \Blade::directive('endability', function ($expression) {
            return "<?php endif; // Cerberus::ability ?>";
        });

        //multi tenant
        \Blade::directive('roleforsite', function ($expression) {
            return "<?php if (\\Cerberus::hasRoleForSite({$expression})) : ?>";
        });

        \Blade::directive('endroleforsite', function ($expression) {
            return "<?php endif; // Cerberus::hasRoleForSite ?>";
        });

        \Blade::directive('permissionforsite', function ($expression) {
            return "<?php if (\\Cerberus::hasPermissionForSite({$expression})) : ?>";
        });

        \Blade::directive('endpermissionforsite', function ($expression) {
            return "<?php endif; // Cerberus::hasPermissionForSite ?>";
        });

        // Call to Cerberus::ability
        \Blade::directive('abilityforsite', function ($expression) {
            return "<?php if (\\Cerberus::abilityForSite({$expression})) : ?>";
        });

        \Blade::directive('endabilityforsite', function ($expression) {
            return "<?php endif; // Cerberus::abilityForSite ?>";
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCerberus();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerCerberus()
    {
        $this->app->bind('cerberus', function ($app) {
            return new Cerberus($app);
        });

        $this->app->alias('cerberus', 'Michalisantoniou6\Cerberus\Cerberus');
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.cerberus.migration', function ($app) {
            return new MigrationCommand();
        });
    }

    /**
     * Merges user's and cerberus's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'cerberus'
        );
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.cerberus.migration',
        ];
    }
}
