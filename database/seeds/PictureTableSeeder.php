<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\couponcategory;
class PictureTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *  优惠券图片表
     * @return void
     */
    public function run(couponcategory $picture)
    {
        $picture -> truncate();//重置
        //实例化factory类    protected $fillable = ['id','merchant_id','price','picture_url','action'];
        $picture->insert([
            'merchant_id'=> 1,
            'price'=> 10,
            'picture_url'=> 'uploads/avatar/img1511161432943832.png',
            'action'=> 2,//优惠方式:1=金额.2=折扣.3=扫码(未开通)
        ]);
    }
}
