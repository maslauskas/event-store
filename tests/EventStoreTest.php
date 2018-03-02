<?php

use Orchestra\Testbench\TestCase;

class EventStoreTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    /** @test */
    function it_registers_testing_config()
    {
        $this->assertEquals('eventstore', config('eventstore.connection'));
        $this->assertEquals('event_store', config('eventstore.table'));
    }

    /** @test */
    function it_migrates_default_tables_to_database()
    {
        $this->assertDatabaseMissing('event_store', []);
    }

    /** @test */
    function it_registers_helper_function()
    {
        $this->assertInstanceOf(\Maslauskas\EventStore\Store::class, eventstore());
    }

    /** @test */
    function it_adds_event_to_default_events_table()
    {
        EventStore::withExceptions()->add('some_event', ['key' => 'value']);
        $this->assertDatabaseHas('event_store', [
            'event_type' => 'some_event',
            'payload' => json_encode(['key' => 'value'])
        ]);
    }

    /** @test */
    function it_gets_table_name_for_event_in_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        $table = (new \Maslauskas\EventStore\StoreEvent())->getStream('custom_event_1');
        $this->assertEquals('custom_event_table', $table);
    }

    /** @test */
    function it_sets_table_property_on_store_event_model()
    {
        $this->addDedicatedTablesToConfig();

        $event = (new \Maslauskas\EventStore\StoreEvent())->setStream('custom_event_1');
        $this->assertEquals('custom_event_table', $event->getTable());
    }

    /** @test */
    function it_creates_custom_event_table()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->add('custom_event_1', ['key' => 'value']);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('custom_event_table'));
    }
    
    /** @test */
    function it_does_not_create_custom_table_if_it_already_exists()
    {
        EventStore::createStreamTable('custom_table');

        $event = new \Maslauskas\EventStore\StoreEvent();
        $event->setTable('custom_table');

        $this->assertFalse($event->needsDedicatedStreamTableCreation());
    }

    /** @test */
    function it_adds_events_to_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->add('custom_event_1', ['key' => 'value']);
        $this->assertDatabaseHas('custom_event_table', [
            'event_type' => 'custom_event_1',
            'payload' => json_encode(['key' => 'value'])
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [\Maslauskas\EventStore\EventStoreServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'EventStore' => \Maslauskas\EventStore\EventStoreFacade::class
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
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'eventstore');
        $app['config']->set('eventstore.connection', 'eventstore');

        $app['config']->set('database.connections.eventstore', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    private function addDedicatedTablesToConfig()
    {
        $this->app['config']->set('eventstore.streams', [
            'custom_event_table' => [
                'custom_event_1',
                'custom_event_2',
            ],
        ]);
    }
}