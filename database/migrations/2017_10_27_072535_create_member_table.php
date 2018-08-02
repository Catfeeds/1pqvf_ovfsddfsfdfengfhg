<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户表
        Schema::create('member',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('phone',15)->unique()->comment( '手机' );
            $table->string('password',255)->comment( '密码' );
            $table->string('nickname',150)->nullable()->comment( '昵称' );
            $table->string('avatar',255)->default('uploads/avatar/morentouxiang.png')->nullable()->comment( '头像' );
            $table->string('city',40)->nullable()->default('深圳')->comment( '城市' );
            $table->unsignedTinyInteger('sex')->nullable()->default(1)->comment( '性别(1:女,2:男,3:保密)' );
            $table->unsignedTinyInteger('age')->nullable()->default(18)->comment( '年龄' );
            //修改为SMALLINT ,允许体重和身高
            $table->unsignedSmallInteger('height')->nullable()->comment( '身高' );
            $table->unsignedSmallInteger('weight')->nullable()->comment( '体重' );
            $table->text('address')->nullable()->comment('收货地址');
            //步数表和用户的关系  步数表存储用户id关联
//            $table->string('steps',255)->nullable()->default('0')->comment( '步数[day1:10,day2:20...]' );
            $table->unsignedTinyInteger('is_admin')->nullable()->default(2)->comment( '是否是管理员，1官方、2普通用户' );


            $table->unsignedInteger('integral')->nullable()->comment( '积分' );
            $table->string('qr_code',255)->nullable()->comment( '二维码' );
            $table->string('medal',255)->nullable()->comment( '获得奖章' );
//            $table->rememberToken()->comment('记住登录');
            $table->string('api_token', 60)->unique()->comment( '登录验证' );
            $table->unsignedInteger('integral_swap')->nullable()->comment( '券换积分累积' );
            $table->text('steps')->nullable()->comment( '步数[x年x月x日:10,day2:20...]' );

            $table->text('friends_id')->nullable()->comment( '好友id,["1","2","3"]' );
            $table->text('fans_id')->nullable()->comment( '粉丝id,["1","2","3"]' );
            $table->text('attention_id')->nullable()->comment( '关注的商家id,["1","2","3"]' );
            $table->text('merchant_id')->nullable()->comment( '收藏的商家id,["1","2","3"]' );
            $table->text('collection_coupon_id')->nullable()->comment( '收藏的优惠券id,["1","2","3"]' );
            //持有优惠券的数量
            $table->text('coupon_id')->nullable()->comment( '拥有的优惠券id,["1","2","3"]' );
            $table->text('tesco')->nullable()->comment( '已购商品id,["1","2","3"]' );
            $table->timestamp('disabled_at')->nullable()->comment('禁用时间');

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
        Schema::dropIfExists('member');
    }
}