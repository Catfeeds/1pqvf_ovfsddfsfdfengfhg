<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Swap;
class SwapTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *  互换表
     * @return void
     */
    public function run(Swap $swap)
    {
        $swap -> truncate();//重置
        //实例化factory类
        $swap->insert([
            'coupon_id'=> 1,
            'member_id'=> 1,
            'status'=> 1,
            'integral'=> 999,
        ]);
    }
}
