<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\TakeFlag;
class TakeFlagTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  用户表
     * @return void
     */
    public function run(TakeFlag $takeFlag)
    {
        $takeFlag -> truncate();//重置
        //实例化factory类
        $takeFlag->insert([
            'member_id'=> 1,
            'latitude'=> '旗子位置的经纬度',
            'flag_num'=> 0,
            'activity_id'=> 2,
        ]);
    }
}
