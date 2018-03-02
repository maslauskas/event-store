<?php

if(!function_exists('eventstore')) {
    function eventstore() {
        return app('EventStore');
    }
}