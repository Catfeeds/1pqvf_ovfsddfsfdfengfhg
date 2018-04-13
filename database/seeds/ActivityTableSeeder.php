<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Activity;
class ActivityTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  活动表
     * @return void
     */
    public function run(Activity $activity)
    {
        $activity -> truncate();//重置
        //实例化factory类
        $activity->insert([
            'note'=> '每月累计步数达到60万步',
            'title'=>'健康达人',
            'img_url'=>'url',
            'content'=>'报名该赛事需要消耗三百积分,xxxxx',
            'actMall_id'=>1,
            'start_at'=>date( 'Y-m-d H:i:s',time() ),
            'end_at'=> '2017-11-8 16:42:45',
            'man_num'=> 10,
        ]);
    }
}
