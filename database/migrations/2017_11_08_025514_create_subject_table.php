<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 话题分类表
        Schema::create('subject',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('cate_name',50)->unique()->comment('分类名称');
            $table->string('cate_note',255)->nullable()->comment('分类描述');
            $table->string('img_url',255)->nullable()->comment( '封面图片' );
            $table->unsignedInteger('read_num')->default(0)->nullable()->comment( '阅读数' );
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
        Schema::dropIfExists('subject');
    }
}