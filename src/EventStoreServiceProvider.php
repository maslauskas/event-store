<?php

namespace Maslauskas\EventStore;

use Illuminate\Support\ServiceProvider;

class EventStoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/eventstore.php' => config_path('eventstore.php'),
        ], 'eventstore');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eventstore.php', 'eventstore');

        $this->registerMigrations();

        $this->app->singleton('EventStore', function () {
            return new Store;
        });
    }

    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'eventstore-migrations');
    }
}
