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
            $table->unsignedInteger('member_id')->nullable()->comment( '当前评论人id' );
            $table->unsignedInteger('dy_id')->nullable()->comment( '当前所属动态id' );
            $table->unsignedInteger('to_id')->nullable()->comment( '当前所属话题id' );
            $table->unsignedInteger('parent_id')->nullable()->comment( '被评论comment_id' );
            $table->unsignedInteger('p_mid')->nullable()->comment( '被评论人member_id' );
            $table->text('content')->nullable()->comment('评论内容');
            $table->unsignedInteger('first_branch_id')->nullable()->comment( '属于哪个一级评论下的，一级评论的first_branch是他本身' );
            $table->timestamps();//评论时间
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