<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 互换表
        Schema::create('swap',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('coupon_id')->comment( '券id' );
            $table->unsignedInteger('member_id')->comment( '会员(发布人)id' );
            $table->unsignedTinyInteger('status')->comment( '状态1:已被兑换,2:还没人兑换' );
            $table->unsignedBigInteger('integral')->comment('需要的积分数量');
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
        Schema::dropIfExists('swap');
    }
}
