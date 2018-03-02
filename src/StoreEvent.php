<?php

namespace Maslauskas\EventStore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StoreEvent extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('eventstore.connection'));
        $this->setTable(config('eventstore.table'));

        parent::__construct($attributes);
    }

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'before' => 'array',
    ];

    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * @param $event
     * @return $this
     */
    public function setEventTable($event)
    {
        $table = $this->getEventTableName($event);
        $this->setTable($table);

        return $this;
    }

    /**
     * @param $event
     * @return \Illuminate\Config\Repository|int|mixed|string
     */
    public function getEventTableName($event)
    {
        $dedicated_tables = config('eventstore.dedicated_tables');

        if(empty($dedicated_tables)) {
            return config('eventstore.table');
        }

        foreach($dedicated_tables as $table => $events) {
            if (array_search($event, $events) !== false) {
                return $table;
            }
        }

        return config('eventstore.table');
    }

    /**
     * @return bool
     */
    public function needsDedicatedTableCreation()
    {
        return $this->getTable() !== config('eventstore.table')
            && !Schema::connection(config('eventstore.connection'))->hasTable($this->getTable());
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = parent::newInstance($attributes, $exists);
        $model->setTable($this->getTable());

        return $model;
    }
}