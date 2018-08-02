
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCnCityLatLngModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //省市区三级联动
        Schema::create('cn_pro_city',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment('当前id');
            $table->unsignedInteger('pid')->nullable()->comment('父级id');
            $table->string('name',60)->nullable()->comment('名称');
            $table->string('pinyin',60)->nullable()->comment('拼音');
            $table->string('adcode',60)->nullable()->comment('所属区县编码（百度）：http://lbsyun.baidu.com/index.php?title=open/dev-res');
            $table->string('full_name',60)->nullable()->comment('地区全名');
            $table->text('note')->nullable()->comment('备注');
            $table->timestamps();
        });
        // 坐标库 注意：为了避免某个区被清空，本库只用作市级坐标库
        Schema::create('cn_lat_lng_bag',function(Blueprint $table){
            // 声明表结构
            $table->engine = 'InnoDB';
            $table->increments('id')->comment('当前id');
            $table->string('uuid',25)->comment('优惠券预编码对应coupon表中uuid');
            $table->string('lng',60)->comment( '所在纬度' );
            $table->string('lat',60)->comment( '所在经度' );
            $table->string('formatted_address',60)->nullable()->comment('地址全称');
            $table->string('cr_adr_code',200)->nullable()->comment('按该区生成的(百度代号adcode)');
            $table->string('province',60)->nullable()->comment('实际所在省');
            $table->string('city',60)->nullable()->comment('实际所在城市');
            $table->string('district',60)->nullable()->comment('实际所在区');
            $table->unsignedInteger('adcode')->nullable()->comment('实际所属区县对应china_pro_city下的adcode');
            $table->string('town',60)->nullable()->comment('实际所在镇');
            $table->text('note')->nullable()->comment('备注');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cn_pro_city');
        Schema::dropIfExists('cn_lat_lng_bag');
    }
}
