<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTCrawlTaskSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_crawl_task_setting', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->nullable()->comment('任务名称');
            $table->string('description', 200)->nullable()->comment('任务描述');
            $table->string('url', 100)->nullable()->comment('资源URL');
            $table->text('selectors')->nullable()->comment('选择器：抓取规则例：{"tilte" :".result c-container", "url" : "a.href"}');
            $table->text('keywords')->nullable()->comment('匹配关键词');
            $table->unsignedTinyInteger('data_type')->nullable()->comment('1、html 2、json 3、xml 暂不支持');
            $table->unsignedTinyInteger('content_type')->nullable()->comment('内容类型1、list 2、content');
            $table->unsignedTinyInteger('is_proxy')->nullable()->comment('是否需要代理 1、需要代理 2、不需要代理');
            $table->unsignedTinyInteger('response_type')->nullable()->comment('响应类型 1、API（默认只支持）2、邮件 3、短信 4、企业微信');
            $table->text('content')->nullable()->comment('抓取模版内容');
            $table->unsignedTinyInteger('type')->nullable()->comment('抓取模版类型 1 通用模版 2自定义模版');
            $table->unsignedTinyInteger('status')->nullable()->comment('状态 1、可用 2、不可用');
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
        Schema::dropIfExists('t_crawl_task_setting');
    }
}
