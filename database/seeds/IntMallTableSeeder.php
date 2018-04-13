<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\IntMall;
class IntMallTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  积分商城表
     * @return void
     */
    public function run(IntMall $intMall)
    {
        $intMall -> truncate();//重置
        //实例化factory类
        $intMall->insert([
            'trade_name'=> '运动智能手表',
            'img_url'=> 'url',
            'trade_num'=> 30,
            'integral_price'=> 5000,
            'rmb_price'=> 200,
        ]);
    }
}
