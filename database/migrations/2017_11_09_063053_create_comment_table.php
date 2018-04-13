<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 评论
        Schema::create('comment',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedInteger('dy_id')->nullable()->comment( '所属动态id' );
            $table->unsignedInteger('to_id')->nullable()->comment( '所属话题id' );
            $table->unsignedInteger('member_id')->nullable()->comment( '评论人id' );
//            $table->string('content',255)->nullable()->comment( '评论内容' );
            $table->text('content')->nullable()->comment('评论内容');

            $table->timestamps();//评论时间
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
        Schema::dropIfExists('comment');
    }
}