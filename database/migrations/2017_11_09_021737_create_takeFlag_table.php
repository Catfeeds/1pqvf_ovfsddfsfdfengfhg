<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTakeFlagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 夺旗先锋
        Schema::create('takeFlag',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('member_id')->unique()->nullable()->comment( '报名人id' );
//            $table->string('latitude',255)->comment( '纬度,旗子所在位置' );
            $table->unsignedTinyInteger('status')->nullable()->default(2)->comment( '状态:1,成功,2,未达成,3,已领奖(如果不等2,就判断是1还是3)' );
            //增加一个text的收货地址,时间戳的是否领奖,时间戳的发货状态
            $table->text('address')->nullable()->comment('收货地址');
            $table->timestamp('award')->nullable()->comment('是否领奖');//当不为Null的时候,就是已经领奖
            $table->timestamp('delivery')->nullable()->comment('发货状态');//当不为Null的时候,就是已经发货
            $table->unsignedSmallInteger('cost')->nullable()->comment( '报名积分' );
            $table->unsignedInteger('flag_num')->nullable()->comment( '已获得旗子数量' );
            $table->unsignedInteger('activity_id')->nullable()->comment( '所属活动id' );
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
        Schema::dropIfExists('takeFlag');
    }
}