<?php

namespace Maslauskas\EventStore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maslauskas\EventStore\Exceptions\EventModelClassNotFound;

class Store
{
    /**
     * @var bool
     */
    private $withExceptions = true;

    /**
     * @param      $event_type
     * @param      $payload
     * @param null $target_id
     * @param null $before
     */
    public function add($event_type, $payload, $target_id = null, $before = null)
    {
        if($before instanceof Model) {
            $before = array_only($before->attributesToArray(), array_keys($payload));
        }

        try {
            $event = new StoreEvent();
            $event->setEventTable($event_type);

            if($event->needsDedicatedTableCreation()) {
                $this->createDedicatedTable($event->getTable());
            }

            $event->create([
                'event_type' => $event_type,
                'payload' => $payload,
                'target_id' => $target_id,
                'before' => $before,
            ]);
        } catch (\Exception $e) {
            if($this->withExceptions) throw $e;
        }
    }

    /**
     * @return $this
     */
    public function withExceptions()
    {
        $this->withExceptions = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutExceptions()
    {
        $this->withExceptions = false;

        return $this;
    }

    public function createDedicatedTable($table)
    {
        DB::transaction(function() use ($table) {
            $schema = Schema::connection(config('eventstore.connection'));

            $schema->create($table, function(Blueprint $builder) {
                $builder->string('event_type')->index();
                $builder->unsignedInteger('target_id')->nullable()->index();
                $builder->longText('payload');
                $builder->longText('before')->nullable();
                $builder->longText('metadata')->nullable();
                $builder->timestamp('created_at')->default(DB::raw("CURRENT_TIMESTAMP"))->index();
            });
        });
    }
}