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
            $table->unsignedInteger('coupon_id')->unique()->comment( 'coupon表中的id' );
            $table->unsignedInteger('member_id')->comment( '会员(发布人)id' );
            $table->unsignedTinyInteger('status')->comment( '状态0失效，1已被兑换，2还没人兑换' );
            $table->unsignedBigInteger('integral')->comment('需要的积分数量');
            $table->unsignedInteger('exc_mem_id')->comment( '兑换人的member表id' );
            $table->timestamps();
            $table->foreign('coupon_id')->references('id')->on('coupon') ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('member') ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('exc_mem_id')->references('id')->on('member') ->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('coupon');
        Schema::dropIfExists('member');
    }
}
