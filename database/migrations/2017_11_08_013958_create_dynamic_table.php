<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDynamicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 动态表
        Schema::create('dynamic',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('member_id')->comment( '发布人id' );
            $table->string('nice_num',255)->nullable()->comment( '点赞数' );
            $table->string('img_url',255)->nullable()->comment( '图片' );
            $table->text('content',255)->comment( '内容' );
            $table->string('addres',255)->comment( '发布地点' );
            $table->timestamps();//创建时间既为发布时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dynamic');
    }
}
