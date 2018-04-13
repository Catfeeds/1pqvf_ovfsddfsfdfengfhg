<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHealthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 健康达人表
        Schema::create('health',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('member_id')->unique()->nullable()->comment( '报名人id' );
            $table->unsignedInteger('target')->nullable()->default(600000)->comment( '目标步数' );
            $table->unsignedInteger('steps')->nullable()->comment( '我的步数(今日)' );
            $table->unsignedInteger('total')->nullable()->comment( '我的总步数' );
            $table->unsignedSmallInteger('cost')->nullable()->comment( '报名积分' );
            $table->unsignedTinyInteger('status')->nullable()->default(2)->comment( '状态:1,成功,2,未达成,3,已领奖(如果不等2,就判断是1还是3)' );
            $table->unsignedInteger('activity_id')->nullable()->default(1)->comment( '所属活动id' );
            //目标步数,状态,所属活动Id,生成的时候先给出默认值,当用户报名的时候,要扣掉300积分,活动每月清零
            $table->text('address')->nullable()->comment('收货地址');
            $table->timestamp('award')->nullable()->comment('是否领奖');//当不为Null的时候,就是已经领奖
            $table->timestamp('delivery')->nullable()->comment('发货状态');//当不为Null的时候,就是已经发货

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
        Schema::dropIfExists('health');
    }
}