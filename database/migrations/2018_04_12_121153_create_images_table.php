<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_image', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable()->comment('名称');
            $table->string('ext', 50)->nullable()->comment('扩展名');
            $table->string('mime_type', 50)->nullable()->comment('MIME类型');
            $table->unsignedInteger('size')->nullable()->comment('文件大小');
            $table->unsignedInteger('width')->nullable()->comment('宽度');
            $table->unsignedInteger('height')->nullable()->comment('高度');
            $table->string('md5_content', 50)->nullable()->comment('md5');
            $table->text('oss_url')->nullable()->comment('oss地址');
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
        Schema::dropIfExists('t_image');
    }
}
