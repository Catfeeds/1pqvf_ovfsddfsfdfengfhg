<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntMallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 积分商城
        Schema::create('intMall',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('trade_name',150)->nullable()->comment( '商品名' );
            $table->string('img_url',255)->nullable()->comment( '商品图片' );
            $table->unsignedBigInteger('trade_num')->comment( '商品数量/注意超购');
            $table->unsignedBigInteger('integral_price')->nullable()->comment( '积分价格');
            $table->unsignedBigInteger('rmb_price')->nullable()->comment( 'rmb价格');
//            $table->unsignedTinyInteger('limit_num')->nullable()->comment( '限兑数量' );
//            $table->unsignedTinyInteger('member_id')->nullable()->comment( '兑换人的id' );
//            $table->unsignedTinyInteger('member_limit_num')->nullable()->comment( '兑换人已兑的数量' );
//            $table->timestamp('exchange')->nullable()->comment( '兑换人兑换的时间' );
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
        Schema::dropIfExists('intMall');
    }
}