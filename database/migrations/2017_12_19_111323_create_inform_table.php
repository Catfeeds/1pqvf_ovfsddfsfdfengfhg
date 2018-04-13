<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInformTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 通知表
        Schema::create('inform',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            //消息内容,发布时间,标题图片
            $table->text('content')->comment('消息内容');
            $table->string('title',255)->comment( '标题' );
            $table->timestamp('push_time')->nullable()->comment('消息发送时间');
//            $table->string('inform_url',255)->nullable()->comment( '图片???不确定要不要' );
            $table->text('read')->nullable()->comment('阅读人');//[1,2] 将阅读了的用户id存入
            $table->timestamps();//发布时间
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
        Schema::dropIfExists('inform');
    }
}
