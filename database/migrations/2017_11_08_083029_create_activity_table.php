<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 活动表
        Schema::create('activity',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('title',150)->nullable()->comment('活动标题');
            $table->string('note',150)->nullable()->comment('活动简介');
            $table->unsignedTinyInteger('status')->default(1)->comment('活动状态:赛事状态：0失效；1有效；2有效但在客户端隐藏');
            $table->string('img_url',255)->nullable()->comment('封面图片,轮播');
            $table->string('top_img_url',255)->nullable()->comment('详情页面顶部图片');
            $table->text('content')->nullable()->comment('赛事介绍');
            $table->unsignedInteger('actMall_id')->nullable()->comment('奖品id');
            $table->timestamp('start_at')->nullable()->comment('活动开始时间');
            $table->timestamp('end_at')->nullable()->comment('结束时间');
            $table->unsignedInteger('man_num')->nullable()->comment('参赛人数');
            $table->unsignedInteger('actMall_num')->nullable()->comment('本活动奖品最大数');
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
        Schema::dropIfExists('activity');
    }
}