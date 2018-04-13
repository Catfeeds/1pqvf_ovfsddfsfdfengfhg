<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRankingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 排行表
        Schema::create('rankings',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->unsignedBigInteger('create_at')->unique()->comment('记录时间');//20180105
            $table->text('ranking')->nullable()->comment('每日排名第一(周排行需要连续七天第一)');
            $table->unsignedTinyInteger('type')->nullable()->comment('如果type==null,则是普通,如果有值,则是赛事的id" )' );

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
        Schema::dropIfExists('rankings');
    }
}
