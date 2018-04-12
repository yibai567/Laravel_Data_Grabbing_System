<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_item', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable()->comment('名称');
            $table->boolean('data_type')->default(1)->nullable()->comment('数据类型：1-html（默认）|2-json|3-截图');
            $table->boolean('content_type')->default(1)->nullable()->comment('内容类型：1-短内容（默认）|2-长内容');
            $table->boolean('type')->default(1)->nullable()->comment('类型：1（默认）');
            $table->text('resource_url')->nullable()->comment('资源地址');
            $table->text('short_content_selector')->nullable()->comment('短内容规则');
            $table->text('long_content_selector')->nullable()->comment('长内容规则');
            $table->text('capture_config')->nullable()->comment('截图配置');
            $table->boolean('cron_type', 200)->default(1)->nullable()->comment('执行规则：1-持续执行|2-每分钟执行|3-每五分钟执行|4-每十五分钟执行');
            $table->boolean('is_proxy')->default(1)->nullable()->comment('是否翻墙：1-翻墙|2-不翻墙');
            $table->timestamp('last_job_at')->nullable()->comment('任务最后执行时间');
            $table->boolean('status')->default(1)->nullable()->comment('任务状态：1-初始化|2-测试成功|3-普通测试失败|4-翻墙测试失败|5-测试失败|6-启动|7-停止');
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
        Schema::dropIfExists('t_item');
    }
}
