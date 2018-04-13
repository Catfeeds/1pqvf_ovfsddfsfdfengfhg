<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Appraise;
class AppraiseTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  评价表
     * @return void
     */
    public function run(Appraise $appraise)
    {
        $appraise -> truncate();//重置
        //实例化factory类
        $appraise->insert([
            'mer_id'=> 1,
            'mem_id'=> 1,
            'appraise'=> 1,//1-10星级评价
            'created_at'=>date('Y-m-d H:i:s'),   //评论时间
        ]);
    }
}
