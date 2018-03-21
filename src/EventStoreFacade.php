<?php

namespace Maslauskas\EventStore;

use Illuminate\Support\Facades\Facade;

class EventStoreFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'EventStore';
    }
}
