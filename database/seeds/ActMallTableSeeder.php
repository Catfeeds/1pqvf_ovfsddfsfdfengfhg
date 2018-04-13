<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\ActMall;
class ActMallTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  活动商城表
     * @return void
     */
    public function run(ActMall $actMall)
    {
        $actMall -> truncate();//重置
        //实例化factory类
        $actMall->insert([
            'note'=> '吸汗毛巾',
            'price'=> 10,
            'img_url'=> 'url',
            'act_num'=> 99999,
        ]);
    }
}
