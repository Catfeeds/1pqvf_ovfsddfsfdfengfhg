<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Rankings;
class RankingsTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  排行表
     * @return void
     */
    public function run(Rankings $rankings)
    {
        $rankings -> truncate();//重置
        //实例化factory类
        $rankings->insert([
            'create_at'=> date('Y-m-d',time()) ,//记录的时间
            'ranking'=>  '[{"member_id":1,"nickname":"张三","steps":300},{"member_id":2,"nickname":"李四","steps":400}]',
            'type'=>'2',
        ]);
    }
}
