<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Subject;
class SubjectTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  用户表
     * @return void
     */
    public function run(Subject $subject)
    {
        $subject -> truncate();//重置
        //实例化factory类
        $subject->insert([
            'cate_name'=> '毅力使者',
            'img_url'=> 'url',
        ]);
    }

}
