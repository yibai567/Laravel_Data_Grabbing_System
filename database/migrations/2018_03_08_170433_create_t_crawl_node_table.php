<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTCrawlNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_crawl_node', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->nullable()->comment('节点名称');
            $table->string('ip', 50)->nullable()->comment('节点ip');
            $table->unsignedTinyInteger('status')->nullable()->comment('节点状态, 1 可用 2 不可用');
            $table->string('tag', 50)->nullable()->comment('命名标签');
            $table->string('region', 50)->nullable()->comment('所在区域');
            $table->unsignedTinyInteger('docker_num')->nullable()->comment('docker数量');
            $table->unsignedTinyInteger('max_task_num')->nullable()->comment('最大任务数量');
            $table->string('log_path', 100)->nullable()->comment('日志保存路径');
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
        Schema::dropIfExists('t_crawl_node');
    }
}
