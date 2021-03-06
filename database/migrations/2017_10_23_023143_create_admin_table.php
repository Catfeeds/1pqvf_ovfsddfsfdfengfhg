<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 超级管理员表
        Schema::create('admin',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment( '主键ID' );
            $table->string('username',150)->unique()->comment( '登录帐号' );
            $table->string('password',255)->comment( '密码' );
            $table->smallInteger('admin_type')->nullable()->comment('管理员类型:0超级管理员（不能被删除），1全局后台管理员，2普通管理员');
            $table->smallInteger('status')->nullable()->comment('禁用状态：0禁用，1生效');
            $table->text('note')->nullable()->comment('备注');
            $table->rememberToken()->comment('记住登录');
            $table->string('email',150)->unique()->nullable()->comment( '邮箱' );
            $table->text('friends_id')->nullable()->comment( '好友id,["1","2","3"]' );
            $table->text('fans_id')->nullable()->comment( '粉丝id,["1","2","3"]' );
            $table->string('avatar',255)->default('uploads/sys_img/morentouxiang.png')->nullable()->comment( '头像' );
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
        // 删除权限模块相关的数据表
        Schema::dropIfExists('admin');
    }
}
