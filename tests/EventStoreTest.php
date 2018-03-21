<?php

namespace Tests;

use Maslauskas\EventStore\Store;
use Maslauskas\EventStore\StoreEvent;
use Maslauskas\EventStore\EventStoreFacade as EventStore;

class EventStoreTest extends EventStoreTestCase
{
    /** @test */
    public function it_registers_testing_config()
    {
        $this->assertEquals('eventstore', config('eventstore.connection'));
        $this->assertEquals('event_store', config('eventstore.table'));
    }

    /** @test */
    public function it_migrates_default_tables_to_database()
    {
        $this->assertDatabaseMissing('event_store', []);
    }

    /** @test */
    public function it_registers_helper_function()
    {
        $this->assertInstanceOf(Store::class, eventstore());
    }

    /** @test */
    public function it_adds_event_to_default_events_table()
    {
        EventStore::withExceptions()->add('some_event', ['key' => 'value']);
        $this->assertDatabaseHas('event_store', [
            'event_type' => 'some_event',
            'payload' => json_encode(['key' => 'value']),
        ]);
    }

    /** @test */
    public function it_gets_table_name_for_event_in_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        $table = (new StoreEvent())->getStream('custom_event_1');
        $this->assertEquals('custom_event_table', $table);
    }

    /** @test */
    public function it_sets_table_property_on_store_event_model()
    {
        $this->addDedicatedTablesToConfig();

        $event = (new StoreEvent())->setStream('custom_event_1');
        $this->assertEquals('custom_event_table', $event->getTable());
    }

    /** @test */
    public function it_creates_custom_event_table()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->add('custom_event_1', ['key' => 'value']);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('custom_event_table'));
    }

    /** @test */
    public function it_does_not_create_custom_table_if_it_already_exists()
    {
        EventStore::createStreamTable('custom_table');

        $event = new StoreEvent();
        $event->setTable('custom_table');

        $this->assertFalse($event->needsDedicatedStreamTableCreation());
    }

    /** @test */
    public function it_adds_events_to_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->add('custom_event_1', ['key' => 'value']);
        $this->assertDatabaseHas('custom_event_table', [
            'event_type' => 'custom_event_1',
            'payload' => json_encode(['key' => 'value']),
        ]);
    }

    /** @test */
    public function it_inserts_multiple_events_at_once()
    {
        EventStore::withExceptions()->addMany('some_event', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $this->assertDatabaseHas('event_store', [
            'event_type' => 'some_event',
            'payload' => json_encode(['key' => 'baz']),
        ]);
    }

    /** @test */
    public function it_inserts_multiple_events_at_once_to_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->addMany('custom_event_1', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $this->assertDatabaseHas('custom_event_table', [
            'event_type' => 'custom_event_1',
            'payload' => json_encode(['key' => 'bar']),
        ]);
    }
}
