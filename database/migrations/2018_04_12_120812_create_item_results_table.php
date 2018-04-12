<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_item_result', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id')->comment('Item ID');
            $table->integer('item_run_log_id')->comment('Item 运行日志 ID');
            $table->text('short_content')->nullable()->comment('短内容');
            $table->text('md5_short_contents')->nullable()->comment('短内容MD5');
            $table->text('long_content0')->nullable()->comment('长内容');
            $table->text('long_content1')->nullable()->comment('长内容');
            $table->text('images')->nullable()->comment('图片信息');
            $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->timestamp('end_at')->nullable()->comment('结束时间');
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
        Schema::dropIfExists('t_item_result');
    }
}
