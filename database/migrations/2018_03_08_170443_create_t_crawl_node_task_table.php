<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTCrawlNodeTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_crawl_node_task', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('node_id')->nullable()->comment('所属节点ID');
            $table->unsignedBigInteger('crawl_task_id')->nullable()->comment('任务ID');
            $table->string('cmd_startup', 200)->nullable()->comment('启动命令');
            $table->string('cmd_stop', 200)->nullable()->comment('停止命令');
            $table->unsignedTinyInteger('status')->nullable()->comment('节点任务状态, 1 启动 2 停止');
            $table->string('log_path', 100)->nullable()->comment('日志保存路径');
            $table->dateTime('start_on')->nullable()->comment('启动时间');
            $table->dateTime('end_on')->nullable()->comment('停止时间');
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
        Schema::dropIfExists('t_crawl_node_task');
    }
}
