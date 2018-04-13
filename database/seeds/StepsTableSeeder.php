<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Steps;
class StepsTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  用户表
     * @return void
     */
    public function run(Steps $steps)
    {
        $steps -> truncate();//重置
        //实例化factory类
        $steps->insert([
            'mem_id'=> 1 ,
            'steps'=>  600,
            'sports_t'=>  date('Ymd',time()),//运动时间
        ]);
    }
}
