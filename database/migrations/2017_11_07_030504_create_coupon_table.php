<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 优惠券表
        Schema::create('coupon',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('cp_id',40)->unique()->comment( '优惠券编号' );
            $table->string('get_addr',255)->nullable()->comment( '优惠券获取地址' );
            $table->unsignedInteger('merchant_id')->comment( '所属商家' );
            $table->timestamp('start_at')->nullable()->comment('优惠券开始时间(如果存在,则说明是有期限的,如必须在11-13号内使用.如果为空,则以获取时间+x天->end_at结束)');
            $table->timestamp('end_at')->nullable()->comment('到期时间/结束时间');
            $table->timestamp('create_at')->nullable()->comment('获取时间');
            $table->float('price', 6, 2)->comment('优惠价格/折扣');
            $table->unsignedTinyInteger('status')->comment( '优惠券使用状态(1:未使用,2:已使用,3:过期,4,被发布到互换了)' );
            $table->unsignedInteger('member_id')->nullable()->comment( '所属用户id' );
            $table->unsignedTinyInteger('action')->nullable()->comment( '优惠方式:1=金额.2=折扣.3=扫码(未开通),4=提交到兑换' );
            //优惠券所在区域(南山区) 优惠券关联图片 获取所在详细纬度
            $table->unsignedInteger('picture_id')->comment('所属图片');
            $table->string('latitude',255)->nullable()->comment( '优惠券所在纬度' );
            $table->string('address',255)->nullable()->comment( '优惠券所在区域' );
            $table->string('note')->nullable()->comment( '描述' );
            $table->text('content',255)->nullable()->comment( '使用说明' );

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
        Schema::dropIfExists('coupon');
    }
}
