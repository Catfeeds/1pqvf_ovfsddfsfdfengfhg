<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 优惠券种类（含图片）
        Schema::create('coupon_category',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment('主键');
            $table->unsignedInteger('merchant_id')->comment('所属商家');
            $table->string('coupon_name',60)->comment('优惠券名称');
            $table->string('coupon_explain',255)->comment('使用说明');
            $table->unsignedTinyInteger('coupon_type')->comment('优惠券类型:0代金券,1满减,2折扣,3其他');
            $table->string('coupon_money',255)->nullable()->comment('优惠券面额或折扣额');
            $table->unsignedInteger('spend_money')->nullable()->comment('最低消费金额');
            $table->timestamp('send_start_at')->comment('发放开始时间，根据合同');
            $table->timestamp('send_end_at')->comment('发放结束时间，根据合同');
            $table->unsignedTinyInteger('category_status')->default(1)->comment('状态:0失效,1生效');
            $table->unsignedInteger('send_num')->nullable()->comment('该券已发放的总量');
            $table->string('picture_url',255)->nullable()->comment('该券的图片');
            $table->string('deduction_url',255)->nullable()->comment('抵扣后的图片');
            $table->text('cp_cate_note')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('merchant_id')->references('id')->on('merchant') ->onUpdate('cascade')->onDelete('cascade');
        });
        // 优惠券列表
        Schema::create('coupon',function(Blueprint $table){
            # 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment('主键');
            $table->unsignedInteger('cp_cate_id')->comment('所属优惠券,对应coupon_category中的id');
            $table->timestamp('start_at')->comment('该券效期的开始时间');
            $table->timestamp('end_at')->comment('该券效期的结束时间');
            $table->string('cp_number',25)->unique()->comment('优惠券编码');
            $table->unsignedTinyInteger('status')->default(0)->comment('使用状态:0未领取,1已领取但未使用,2已使用,3过期,4冻结期（互换期等）');
            $table->string('rewarded_lat',30)->nullable()->comment('记录获取的经度');
            $table->string('rewarded_lng',30)->nullable()->comment('记录获取的纬度');
            $table->timestamp('create_at')->nullable()->comment('获取时间');
            $table->string('cr_adr_code',200)->nullable()->comment('按当前区/县生成的坐标(百度编码adcode)');
            $table->unsignedInteger('member_id')->nullable()->comment('优惠券所属用户');
            $table->string('province',60)->nullable()->comment('优惠券所在省');
            $table->string('city',60)->nullable()->comment('优惠券所在城市');
            $table->string('district',60)->nullable()->comment('优惠券所在区');
            $table->string('adcode',8)->nullable()->comment('优惠券所在区域的百度地区编码http://lbsyun.baidu.com/index.php?title=open/dev-res');
            $table->string('lat',30)->comment( '所在经度' );
            $table->string('lng',30)->comment( '所在纬度' );
            $table->string('note')->nullable()->comment('优惠券备注');
            $table->string('uuid',36)->comment('优惠券编号/排序字段');
            $table->timestamps();
            $table->index('uuid');
            # 外键约束 如果coupon_category更新该数据库cp_cate_id也更新，如果删除同样也删除
            $table->foreign('cp_cate_id')->references('id')->on('coupon_category') ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('member') ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('merchant');
        Schema::dropIfExists('member');
    }
}
