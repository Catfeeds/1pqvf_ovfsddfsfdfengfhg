<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActMallTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 活动商城(只做奖品用途)
        Schema::create('actMall',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('goods_name',150)->nullable()->comment('奖品名称');
            $table->string('note',150)->nullable()->comment('奖品简介');
            $table->unsignedBigInteger('price')->comment('奖品价格');
            $table->string('img_url',255)->nullable()->comment('奖品图片');
            $table->unsignedBigInteger('act_num')->default(0)->comment('奖品数量');
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
        Schema::dropIfExists('actMall');
    }
}