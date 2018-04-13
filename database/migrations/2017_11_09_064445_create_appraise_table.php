<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppraiseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 评价表
        Schema::create('appraise',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('mer_id')->nullable()->comment( '所属商家id' );
            $table->unsignedInteger('mem_id')->nullable()->comment( '评论人id' );
            $table->unsignedTinyInteger('appraise')->nullable()->comment( '评价' );//共十个星级
            //增加评论内容            //增加评论图片(多图)
            $table->string('img_url',255)->nullable()->comment( '图片' );
            $table->text('content',255)->nullable()->comment( '内容' );
            $table->timestamps();//评论时间
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
        Schema::dropIfExists('appraise');
    }
}