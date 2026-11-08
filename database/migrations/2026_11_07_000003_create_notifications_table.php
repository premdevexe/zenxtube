<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->string('type', 30)->default('both'); // 'push', 'banner', 'both'
            $table->string('banner_style', 20)->default('info'); // 'info', 'success', 'warning', 'danger'
            $table->string('target_interest', 100)->default('hello');
            $table->boolean('is_banner_active')->default(true);
            $table->string('pusher_publish_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}

