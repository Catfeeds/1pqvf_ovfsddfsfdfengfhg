<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Perseverance;
class PerseveranceTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  附属表 毅力使者
     * @return void
     */
    public function run(Perseverance $perseverance)
    {
        $perseverance -> truncate();//重置
        //实例化factory类
        $perseverance -> insert([
            'member_id'=>1,
            'status'=>1,
            'punch_d'=>10,
            'steps'=>8877,
            'total_steps'=>168877,
            'activity_id'=>1,
        ]);
    }
}
