<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 商家表
        Schema::create('merchant',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('nickname',150)->nullable()->comment( '商家名称' );
            $table->unsignedTinyInteger('ification_id')->default(1)->comment( '商家分类' );
            $table->string('labelling',255)->nullable()->comment( '标签' );
            $table->string('latitude',255)->nullable()->comment( '纬度' );
            $table->timestamp('disabled_at')->nullable()->comment('禁用时间');
            $table->text('address')->nullable()->comment('商家地址');
            $table->string('img_url',255)->nullable()->comment( '图片' );
            $table->string('avatar',255)->default('uploads/sys_img/morentouxiang.png')->comment( '商家头像' );
            $table->unsignedTinyInteger('appraise_n')->comment( '评价星级' );
            $table->string('store_image',255)->nullable()->comment( '店铺图片' );
            $table->text('note',255)->nullable()->comment('商家备注');
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
        Schema::dropIfExists('merchant');
    }
}