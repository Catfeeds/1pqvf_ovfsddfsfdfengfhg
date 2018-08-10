<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 奖章表
        Schema::create('medal',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('medal_url',255)->nullable()->comment( '奖章的图片' );
            $table->string('note',255)->nullable()->comment( '奖章的简介(迈出第一步等)');
            $table->text('content')->nullable()->comment( '奖章详情');
            $table->unsignedTinyInteger('type')->nullable()->comment( '奖章获取的类型(1:运动,2:优惠券,3:活动)' );
            $table->unsignedTinyInteger('pid')->nullable()->comment( '归属,如果增加的是点亮的,则为0,如果是未点亮的,则是点亮的记录的id)' );
            $table->unsignedInteger('rewards')->nullable()->comment( '奖励积分' );
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
        Schema::dropIfExists('medal');
    }
}
