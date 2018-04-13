<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Coupon;
class CouponTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *  优惠券表
     * @return void
     */
    public function run(Coupon $coupon)
    {
        $coupon -> truncate();//重置
        //实例化factory类
        $coupon->insert([
            'cp_id'=> time() . 00000001,//优惠券编号 唯一
            'merchant_id'=> 1,//所属商家的id
            'start_at'=>'2017-11-11 00:00:00',
            'end_at'=>'2017-11-13 00:00:00',
            'create_at'=>'2017-11-10 00:00:00',
            'action'=>'2',
            'price'=>9.5,//折扣
            'status'=>'1',
            'member_id'=>1,//获得此券的用户
            'get_addr'=>'深大',//获得此券时的地址
        ]);
    }

    }
