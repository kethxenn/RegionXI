<?php

namespace Orchestra\Memory;

use Illuminate\Contracts\Foundation\Application;
use Orchestra\Support\Providers\ServiceProvider;

class MemoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('orchestra.memory', function (Application $app) {
            $manager = new MemoryManager($app);
            $namespace = $this->hasPackageRepository() ? 'orchestra/memory::' : 'orchestra.memory';

            $manager->setConfig($app->make('config')->get($namespace));

            return $manager;
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/../resources');

        $this->addConfigComponent('orchestra/memory', 'orchestra/memory', "{$path}/config");

        if (! $this->hasPackageRepository()) {
            $this->bootUnderLaravel($path);
        }

        $this->loadMigrationsFrom([
            "{$path}/database/migrations",
        ]);

        $this->bootMemoryEvent();
    }

    /**
     * Register memory events during booting.
     *
     * @return void
     */
    protected function bootMemoryEvent()
    {
        $this->app->terminating(function () {
            $this->app->make('orchestra.memory')->finish();
        });
    }

    /**
     * Boot under Laravel setup.
     *
     * @param  string  $path
     *
     * @return void
     */
    protected function bootUnderLaravel($path)
    {
        $this->mergeConfigFrom("{$path}/config/config.php", 'orchestra.memory');

        $this->publishes([
            "{$path}/config/config.php" => config_path('orchestra/memory.php'),
        ]);
    }
}
