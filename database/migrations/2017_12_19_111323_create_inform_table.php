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
            $table->text('to_member')->nullable()->comment('被被通知人id，通知全部为则null');//
            $table->smallInteger('inf_path')->nullable()->comment('消息分类:1系统发给所有人；2有新评论');//消息类别
            //消息内容,发布时间,标题图片
            $table->timestamp('push_time')->nullable()->comment('消息发送时间，系统通知才有值');
            $table->string('title',255)->comment( '标题' );
            $table->text('content')->comment('消息内容');
//            $table->string('inform_url',255)->nullable()->comment( '图片' );
            $table->text('read')->nullable()->comment('已阅读人id');//将阅读了的用户id存入
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
