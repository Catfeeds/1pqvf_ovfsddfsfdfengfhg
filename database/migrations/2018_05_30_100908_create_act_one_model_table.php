<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActOneModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 记录活动表
        Schema::create('act_one',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('member_id')->unique()->nullable()->comment( '报名人id' );
            $table->text('has_flag')->nullable()->comment( '拥有旗子的id' );
            $table->unsignedTinyInteger('status')->nullable()->default(2)->comment( '状态:1,成功,2,未达成,3,已领奖(如果不等2,就判断是1还是3)' );
            //增加一个text的收货地址,时间戳的是否领奖,时间戳的发货状态
            $table->text('address')->nullable()->comment('收货地址');
            $table->timestamp('award')->nullable()->comment('是否领奖');//当不为Null的时候,就是已经领奖
            $table->timestamp('delivery')->nullable()->comment('发货状态');//当不为Null的时候,就是已经发货
            $table->unsignedSmallInteger('cost')->nullable()->comment( '报名所需积分' );
            $table->unsignedInteger('flag_num')->nullable()->comment( '已获得旗子数量' );
            $table->unsignedInteger('activity_id')->nullable()->comment( '所属活动id' );
            $table->timestamps();
            $table->softDeletes();
        });
        // 活动旗子坐标
        Schema::create('act_one_flag',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('lng',60)->comment( '活动旗子所在纬度' );
            $table->string('lat',60)->comment( '活动旗子所在经度' );
            $table->unsignedInteger('act_id')->comment( '活动id：4深大活动，5端午节活动' );
            $table->unsignedTinyInteger('status')->default(1)->comment( '旗子状态:0失效（已领取）；1生效' );
            $table->unsignedInteger('member_id')->nullable()->comment( '领取人id' );
            $table->text('pic_id',255)->nullable()->comment( '旗子图标id' );
            $table->string('flag_name',255)->nullable()->comment( '旗子别名' );
            $table->unsignedsmallInteger('flag_lv')->nullable()->comment( '旗子难度等级' );
            $table->text('content',255)->nullable()->comment( '旗子说明' );
            $table->text('note',255)->nullable()->comment( '旗子备注' );
            $table->index(['lng', 'lat']);
            //增加一个text的收货地址,时间戳的是否领奖,时间戳的发货状态
            $table->timestamps();
            $table->softDeletes();
        });
        //旗子属性
        Schema::create('flag_pic',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('flag_adr',255)->nullable()->comment( '旗子图标地址' );
            $table->text('note')->nullable()->comment( '备注' );
            //增加一个text的收货地址,时间戳的是否领奖,时间戳的发货状态
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('act_one');
        Schema::dropIfExists('act_one_flag');
        Schema::dropIfExists('flag_pic');

    }
}
