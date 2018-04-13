<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 意见反馈表
        Schema::create('feedback',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            //反馈类型(功能建议,bug提交,商家问题),反馈描述,图片(多图),联系方式,发布时间,处理结果,反馈人
            $table->string('inform_url',255)->nullable()->comment( '图片' );
            $table->unsignedTinyInteger('cation')->comment( '反馈类型(1:功能建议2bug提交3商家问题)' );
            $table->unsignedInteger('member_id')->nullable()->comment( '反馈用户' );
            $table->text('content')->comment('反馈描述');
            $table->string('contact',255)->nullable()->comment( '联系方式' );
            $table->unsignedTinyInteger('handling')->comment( '处理结果1=已处理,2=未处理' );
            $table->timestamps();//发布时间
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
