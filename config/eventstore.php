<?php

return [

    'connection' => env('EVENT_STORE_CONNECTION', config('database.default')),

    'table' => env('EVENT_STORE_TABLE', 'event_store'),

    'throw_exceptions' => false,

    /**
     * Here you can set dedicated tables for certain events to be stored in.
     */
    'streams' => [
        // 'custom_table' => [
        //     'custom_event_1',
        //     'custom_event_2',
        // ]
    ]
];