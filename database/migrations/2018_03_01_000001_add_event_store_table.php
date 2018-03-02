<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = Schema::connection(config('eventstore.connection'));

        $schema->create(config('eventstore.table'), function(Blueprint $table) {
            $table->bigIncrements('event_id')->index();
            $table->string('event_type')->index();
            $table->unsignedInteger('target_id')->nullable()->index();
            $table->longText('payload');
            $table->longText('metadata')->nullable();
            $table->timestamp('created_at')->default(DB::raw("CURRENT_TIMESTAMP"))->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schema = Schema::connection(config('eventstore.connection'));

        $schema->dropIfExists(config('eventstore.table'));
    }
}
