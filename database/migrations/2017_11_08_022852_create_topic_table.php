<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 话题表
        Schema::create('topic',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('admin_id')->nullable()->comment( '发布人(管理员)id' );
            $table->unsignedInteger('member_id')->nullable()->comment( '发布人(用户)id' );
            $table->unsignedTinyInteger('subject_id')->nullable()->comment( '分类id(归属哪个话题分类)' );
            $table->text('subjec_catename')->nullable()->comment( '#毅力使者是怎样炼成的#' );
            $table->string('nice_num',255)->nullable()->comment( '点赞数' );
            $table->text('content')->comment( '内容' );
            $table->string('img_url',255)->nullable()->comment( '图片' );
//            $table->unsignedBigInteger('read_num')->nullable()->comment( '阅读数' );
            $table->string('addres',255)->default('深圳')->comment( '发布地点' );
            $table->timestamps();//创建时间既为发布时间
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
        Schema::dropIfExists('topic');
    }
}