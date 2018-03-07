<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTCrawlTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_crawl_task', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->nullable()->comment('任务名称');
            $table->string('description', 200)->nullable()->comment('任务描述');
            $table->string('resource_url', 100)->nullable()->comment('资源URL');
            $table->unsignedTinyInteger('cron_type')->nullable()->comment('cron类型');
            $table->text('selectors')->nullable()->comment('选择器');
            $table->unsignedTinyInteger('status')->nullable()->comment('状态： 1、未启动 2、测试成功 3、测试失败 4、启动中 5、已停止 6、归档');
            $table->unsignedTinyInteger('response_type')->nullable()->comment('响应类型 1、API（默认只支持）2、邮件 3、短信 4、企业微信');
            $table->string('response_url', 100)->nullable()->comment('发送接口地址');
            $table->string('response_params', 50)->nullable()->comment('参数');
            $table->text('test_result')->nullable()->comment('测试结果');
            $table->dateTime('test_time')->nullable()->comment('测试时间');
            $table->integer('script_last_generate_time')->nullable()->comment('脚本最后生成时间');
            $table->unsignedInteger('setting_id')->nullable()->comment('配置ID');
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
        Schema::dropIfExists('t_crawl_task');
    }
}
