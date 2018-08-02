<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTheDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 积分商品
        Schema::create('theDelivery',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '订单ID' );
            //兑换时间,发货时间,兑换的人,被兑换的积分商品,(所选物流,快递单号)可以为空
            $table->string('order_sn',255)->unique()->comment( '订单号,唯一' );
            $table->unsignedInteger('intmall_id')->comment( '积分商品id' );
            $table->unsignedInteger('actmall_id')->comment( '活动商品id' );
            $table->string('goods_amount',255)->comment( '商品花费' );
            $table->unsignedInteger('member_id')->comment( '收货人id' );
            $table->unsignedInteger('phone')->comment( '收货人电话' );
            $table->string('address',200)->comment( '收货人地址' );
            $table->unsignedInteger('zipcode')->comment( '收货人邮编' );
            $table->timestamp('delivery_time')->nullable()->comment('发货时间');
            $table->string('logistics',255)->nullable()->comment( '物流(顺丰/申通等)' );
            $table->string('logistics_sn',255)->nullable()->comment( '快递单号' );
            $table->char('td_status',1)->default('0')->comment( '订单状态：0.未完成;1.已完成;' );
            $table->string('postscript',255)->nullable()->comment( '订单附言,由用户提交订单前填写' );
            $table->timestamps();//兑换发起时间
            $table->softDeletes();
            // $table->unsignedInteger('admin_id')->nullable()->comment( '处理人' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thedelivery');
    }
}
