<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Maslauskas\EventStore\EventStoreFacade as EventStore;
use Maslauskas\EventStore\QueryBuilder;

class QueryTest extends EventStoreTestCase
{
    /** @test */
    function it_returns_query_builder_when_using_query_method()
    {
        $events = eventstore()->query();
        $this->assertInstanceOf(Builder::class, $events);
    }

    /** @test */
    function it_gets_all_events()
    {
        EventStore::withExceptions()->addMany('custom_event_1', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        EventStore::withExceptions()->add('custom_event_2', ['key' => 'value']);

        $events = eventstore()->get();
        $this->assertCount(4, $events);
    }

    /** @test */
    function it_gets_all_events_for_specific_stream()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->addMany('regular_event', [
            ['key' => 'foo'],
            ['key' => 'bar'],
        ]);

        EventStore::withExceptions()->addMany('custom_event_1', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        EventStore::withExceptions()->addMany('event_foo', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
            ['key' => 'foobar'],
        ]);

        $events = eventstore()->stream('custom_event_table')->get();
        $this->assertCount(3, $events);
    }

    /** @test */
    function it_gets_all_events_for_specific_event_type()
    {
        EventStore::withExceptions()->addMany('regular_event', [
            ['key' => 'foo'],
        ]);

        EventStore::withExceptions()->addMany('event_foo', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
            ['key' => 'foobar'],
        ]);

        EventStore::withExceptions()->addMany('event_bar', [
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $events = eventstore()->get('event_foo');
        $this->assertCount(4, $events);
    }

    /** @test */
    function it_gets_all_events_for_specific_event_type_with_dedicated_stream()
    {
        $this->addDedicatedTablesToConfig();

        EventStore::withExceptions()->addMany('regular_event', [
            ['key' => 'foo'],
        ]);

        EventStore::withExceptions()->addMany('event_foo', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
            ['key' => 'foobar'],
        ]);

        EventStore::withExceptions()->addMany('event_bar', [
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $events = eventstore()->get('event_foo');
        $this->assertCount(4, $events);
    }
}