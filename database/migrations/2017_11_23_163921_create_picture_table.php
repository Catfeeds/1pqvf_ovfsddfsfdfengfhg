<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePictureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 优惠券图片表
        Schema::create('picture',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('merchant_id')->nullable()->comment( '商家id' );
            $table->string('price',255)->nullable()->comment( '该券的金额' );
            $table->string('picture_url',255)->nullable()->comment( '该券的图片' );
            $table->string('deduction_url',255)->nullable()->comment( '该券的抵扣图片' );
            $table->unsignedTinyInteger('action')->nullable()->comment( '优惠方式:1=金额.2=折扣.3=扫码(未开通)' );
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
        Schema::dropIfExists('picture');
    }
}
