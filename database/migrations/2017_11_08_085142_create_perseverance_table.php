<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreatePerseveranceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 附属表 毅力使者
        Schema::create('perseverance',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('member_id')->unique()->nullable()->comment( '报名人id' );
            $table->unsignedInteger('punch_d')->nullable()->comment( '打卡天数' );
            //修改为 day1:xx步,录入步数的时候,录入day1:的打卡情况,
            $table->text('status')->nullable()->comment( '打卡状态[x年x月x日]' );
            $table->unsignedTinyInteger('status2')->nullable()->default(2)->comment( '状态:1,成功,2,未达成,3,已领奖(如果不等2,就判断是1还是3)' );

            $table->text('address')->nullable()->comment('收货地址');
            $table->timestamp('award')->nullable()->comment('是否领奖');//当不为Null的时候,就是已经领奖
            $table->timestamp('delivery')->nullable()->comment('发货状态');//当不为Null的时候,就是已经发货

            $table->unsignedBigInteger('total_steps')->nullable()->comment( '总步数' );
            $table->unsignedInteger('activity_id')->nullable()->comment( '所属活动id' );
            $table->timestamps();
            $table->softDeletes();

            //            $table->text('steps')->nullable()->comment( '步数[x年x月x日:10,day2:20...]' );
//            $table->unsignedTinyInteger('status')->nullable()->comment( '打卡状态(1:未打卡,2:已打卡)' );
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perseverance');
    }
}