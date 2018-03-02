<?php

return [

    'connection' => env('EVENT_STORE_CONNECTION', config('database.default')),

    'table' => env('EVENT_STORE_TABLE', 'event_store'),

    /**
     * Here you can set dedicated tables for certain events to be stored in.
     */
    'dedicated_tables' => [
        // 'custom_table' => [
        //     'custom_event_1',
        //     'custom_event_2',
        // ]
    ]
];