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
            $table->string('db', 20)->nullable()->comment('数据库');
            $table->boolean('is_proxy')->nullable()->comment('是否需要翻墙：1-需要|2-不需要');
            $table->boolean('data_type')->nullable()->comment('内容类型：1-html|2-json');
            $table->boolean('is_capture_image')->nullable()->comment('是否需要截图：1-true|2-false');
            $table->boolean('status')->nullable()->comment('状态：1-success|2-fail');
            $table->timestamps();
            $table->softDeletes();
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
