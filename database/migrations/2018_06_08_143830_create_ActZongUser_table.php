<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActZongUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 记录活动表
        Schema::create('act_zone_user',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('member_id')->unique()->nullable()->comment( '报名人id' );
            $table->text('has_flag')->nullable()->comment( '拥有旗子的id' );
            $table->text('has_reward')->nullable()->comment( '已领奖记录json格式' );
            $table->unsignedTinyInteger('status')->nullable()->default(2)->comment( '状态:1,成功,2,未达成,3,已领奖(如果不等2,就判断是1还是3)' );
            //增加一个text的收货地址,时间戳的是否领奖,时间戳的发货状态
            $table->text('address')->nullable()->comment('收货地址');
            $table->timestamp('award')->nullable()->comment('是否领奖');//当不为Null的时候,就是已经领奖
            $table->timestamp('delivery')->nullable()->comment('发货状态');//当不为Null的时候,就是已经发货
            $table->unsignedSmallInteger('cost')->nullable()->comment( '报名所需积分' );
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
        Schema::dropIfExists('act_zone_user');
    }
}
