<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Medal;
class MedalTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  奖章
     * @return void
     */
    public function run(Medal $medal)
    {
        $medal -> truncate();//重置
        //实例化factory类
        $medal->insert([
            'medal_url'=> 'uploads/picture_url/img1511492635574801.jpeg',
            'note'=> '卖出第一步',
            'content'=> '把自己卖了',
            'type'=> 1, //1:运动,2:优惠券,3:活动
            'rewards'=> 300, //获得奖章,奖励的积分
//            'bright'=> 1, //(1:点亮的类型,2未点亮的类型,未点亮的类型需要和点亮的类型对应 )
            'pid'=> 0, //(归属,如果增加的是点亮的,则为0,如果是未点亮的,则是点亮的记录的id)
            'action'   => '{"create":"medal\/x1","select":"medal\/x2"}',//方法1(达成条件),方法2(查询){接口} 统一采用get方式

        ]);
    }
}
