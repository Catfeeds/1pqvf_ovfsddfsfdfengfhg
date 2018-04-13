<?php


use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Topic;
class TopicTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  用户表
     * @return void
     */
    public function run(Topic $topic)
    {
        $topic -> truncate();//重置
        //实例化factory类
        $topic->insert([
            'admin_id'=> 1,
            'subject_id'=> 1,
            'nice_num'=>1,
            'content'=>'毅力使者是怎样炼成的',
            'img_url'=>'url',
            'read_num'=> 998,
            'created_at'=>date('Y-m-d H:i:s',time()),
        ]);
    }
}
