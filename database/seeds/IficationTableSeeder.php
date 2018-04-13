<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Ification;
class IficationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *  分类
     * @return void
     */
    public function run(Ification $ification)
    {
        $ification -> truncate();//重置
        //实例化factory类
        $ification->insert([
            'cate_name'=> '美食',
        ]);
        $ification->insert([
            'cate_name'=> '娱乐',
        ]);
    }
}
