<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTCrawlResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_crawl_result', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('crawl_task_id')->nullable()->comment('任务id');
            $table->text('original_data')->nullable()->comment('原始数据');
            $table->dateTime('task_start_time')->nullable()->comment('任务开始时间');
            $table->dateTime('task_end_time')->nullable()->comment('任务结束时间');
            $table->text('setting_selectors')->nullable()->comment('选择器：抓取规则例：{"tilte" :".result c-container", "url" : "a.href"}');
            $table->text('setting_keywords')->nullable()->comment('匹配关键词');
            $table->unsignedTinyInteger('setting_data_type')->nullable()->comment('1、html 2、json 3、xml 暂不支持');
            $table->string('task_url', 200)->nullable()->comment('任务url地址');
            $table->text('format_data')->nullable()->comment('格式化数据');
            $table->unsignedTinyInteger('status')->nullable()->comment('状态 1、未处理 2、已处理');
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
        Schema::dropIfExists('t_crawl_result');
    }
}
