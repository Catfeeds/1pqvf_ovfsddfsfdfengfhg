<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Dynamic;
class DynamicTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  动态表
     * @return void
     */
    public function run(Dynamic $dynamic)
    {
        $dynamic -> truncate();//重置
        //实例化factory类
        $dynamic->insert([
            'member_id'=> '1',
            'nice_num'=> '0',
            'img_url'=> 'url',
            'content'=> '第一条动态',
            'addres'=> '发布于x市x区x街道x',
            'created_at'=> date('Y-m-d H:i:s',time()),//发布时间
        ]);
    }

}

