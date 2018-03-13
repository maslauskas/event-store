# event-store

Simple EventStore implementation package for Laravel using MySQL.

[![Build Status](https://travis-ci.org/maslauskas/event-store.svg?branch=master)](https://travis-ci.org/maslauskas/event-store) [![Latest Stable Version](https://poser.pugx.org/maslauskas/event-store/v/stable)](https://packagist.org/packages/maslauskas/event-store) [![Total Downloads](https://poser.pugx.org/maslauskas/event-store/downloads)](https://packagist.org/packages/maslauskas/event-store) [![License](https://poser.pugx.org/maslauskas/event-store/license)](https://packagist.org/packages/maslauskas/event-store)

## Installation

To start using this package, install it with composer:

```php
composer require maslauskas/event-store
```

Publishing config and migrations:

```
php artisan vendor:publish --provider=Maslauskas\EventStore\EventStoreServiceProvider
```

This package uses Laravel's package auto-discovery feature, so there is no need to modify your `config/app.php` file.

## Configuration

Event Store logs are saved to your main database by default, but it is recommended to use a dedicated MySQL database for it. Once you create the database, make sure to set Event Store to use it:

First, add a dedicated connection to your `config/database.php` file:

```php
'connections' => [

        /*
        ...
        */

        'eventstore' => [
            'driver' => 'mysql',
            'host' => env('EVENT_STORE_HOST', 'localhost'),
            'port' => env('EVENT_STORE_PORT', '3306'),
            'database' => env('EVENT_STORE_DATABASE', 'event_store'),
            'username' => env('EVENT_STORE_USERNAME', 'root'),
            'password' => env('EVENT_STORE_PASSWORD', 'root'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        /*
        ...
        */
]
```

Next, add required environment variables to your `.env` file:

```env
EVENT_STORE_DATABASE="your_event_store_database_name"
EVENT_STORE_TABLE="your_event_store_table_name"
EVENT_STORE_USERNAME="your_event_store_user_name"
EVENT_STORE_PASSWORD="your_event_store_user_password"
```

It is recommended to create a separate user for event store database, and remove UPDATE, DELETE, DROP permissions, to make sure your event store is append-only.

Next, run the migration to create default event store table:

```
php artisan migrate
```

## Usage

To start logging your events, append this line to your code where you wish the event to be logged:

```php
EventStore::add('event_name', $data);
```

Or using the eventstore helper function, which is just a wrapper for the facade:

```php
eventstore()->add('event_name', $data);
```

the `add()` method accepts four arguments:
- `$event_type`: name of your event, e.g. `user_created`, `email_sent`, `order_shipped`, etc.
- `$payload`: array of values to record. e.g. for `user_created` event, you can pass the array of attributes that this user was created with.
- `$target_id`: *(optional)* ID of target model in your database. E.g., for `email_sent` event, you can pass `user_id` as `$target_id`. This helps in the future when you wish to fetch all events related to a particular user.
- `$before`: *(optional)* array of values that were changed. E.g. for `user_updated` event, you may pass `$user->toArray()` to record attributes that were changed and their values before the change. *Note:* the `add()` method automatically filters out only those keys that exist in `$payload` parameter to avoid unnecessary overhead.

Sometimes, certain events occur much more frequently than others, e.g. `user_created` and `user_logged_in`. To help with query performance, you can separate certain events to their dedicated tables by changing the `streams` array in `config/eventstore.php` file:

```php
'streams' => [
    'user_login_stream' => [
        'user_logged_in',
    ]
]
```

This will automatically create a dedicated `user_login_stream` table in your event store database when you try to add `user_logged_in` event. All events that are not defined in this array will be saved in the default event store table.

## Extra methods

### query()

Returns `Illuminate\Database\Eloquent\Builder` instance so you can perform any query on event store tables.

### get()

Gets all events from the default event store table. Returns a collection.

### get($event_name)

Gets all events of specific type from event store table. Automatically determines which table to search in. Returns a collection.

### stream($stream_name)

Sets dedicated table and returns `Illuminate\Database\Eloquent\Builder` instance so you can perform any query on event store tables.

## Exception handling

By default, EventStore suppresses any exceptions that occur during `add()` method call. You can disable this by changing `throw_exceptions` setting in `config/eventstore.php`:

```php
'throw_exceptions' => true,
```

## Testing

Run the tests with 

```
vendor/bin/phpunit
```