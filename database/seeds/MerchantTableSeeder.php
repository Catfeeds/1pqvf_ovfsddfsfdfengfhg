<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Merchant;
class MerchantTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *  商家表
     * @return void
     */
    public function run(Merchant $merchant)
    {
        $merchant -> truncate();//重置
        //实例化factory类
        $merchant->insert([
            'nickname'=> '海至尊',
            'ification_id'=> 1,
            'address'=>'南山科技园',
            'latitude'=>'所在的纬度',
            'img_url'=>'封面图片的地址',
            'avatar'=>'店家头像',
            'appraise_n'=>5,
        ]);
    }

}
