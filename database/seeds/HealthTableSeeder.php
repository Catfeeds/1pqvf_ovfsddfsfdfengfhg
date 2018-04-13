<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Health;
class HealthTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  用户表
     * @return void
     */
    public function run(Health $health)
    {
        $health -> truncate();//重置
        //实例化factory类
        $health->insert([
            'member_id'=> 1,
            'target'=> 600000,
            'steps'=> 5000,
            'total'=> 12000,
            'cost'=> 300,
            'status'=> 2,
            'activity_id'=> 2,
        ]);
    }
}
