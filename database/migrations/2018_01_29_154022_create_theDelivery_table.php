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
            $table->increments('id')->comment( '主键ID' );
            //兑换时间,发货时间,兑换的人,被兑换的积分商品,(所选物流,快递单号)可以为空
            $table->unsignedInteger('member_id')->comment( '用户id' );
            $table->unsignedInteger('intmall_id')->comment( '商品id' );
            $table->timestamp('delivery_time')->nullable()->comment('发货时间');
            $table->string('logistics',255)->nullable()->comment( '物流(顺丰/申通等)' );
            $table->string('Order',255)->nullable()->comment( '订单单号' );
            $table->timestamps();//兑换时间
            $table->softDeletes();
            //            $table->unsignedInteger('admin_id')->nullable()->comment( '处理人' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback');
    }
}
