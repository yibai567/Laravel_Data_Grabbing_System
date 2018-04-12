<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_queue_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable()->comment('队列名');
            $table->text('description')->nullable()->comment('描述信息');
            $table->unsignedInteger('current_length')->nullable()->comment('当前队列长度');
            $table->boolean('is_proxy')->nullable()->comment('是否需要翻墙：1-需要|2-不需要');
            $table->boolean('content-type')->nullable()->comment('内容类型：1-短内容|2-长内容');
            $table->boolean('status')->nullable()->comment('状态');
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
        Schema::dropIfExists('t_queue_info');
    }
}
