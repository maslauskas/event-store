<?php

namespace Tests;

use Maslauskas\EventStore\EventStoreFacade;
use Maslauskas\EventStore\EventStoreServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class EventStoreTestCase extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [EventStoreServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'EventStore' => EventStoreFacade::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'eventstore');
        $app['config']->set('eventstore.connection', 'eventstore');

        $app['config']->set('database.connections.eventstore', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     *
     */
    public function addDedicatedTablesToConfig()
    {
        $this->app['config']->set('eventstore.streams', [
            'custom_event_table' => [
                'custom_event_1',
                'custom_event_2',
            ],
            'other_event_stream' => [
                'event_foo',
                'event_bar',
            ],
        ]);
    }
}