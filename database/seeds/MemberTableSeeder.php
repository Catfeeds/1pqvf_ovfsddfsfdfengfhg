<?php


use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Member;
class MemberTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  用户表
     * @return void
     */
    public function run(Member $member)
    {
        $member -> truncate();//重置
        //实例化factory类
        $member->insert([
            'nickname'=> '方灿桩',
            'phone'=> '15994910183',
            'password'=> bcrypt('123456'), //注意使用固定密码,hash加密
            'avatar'   => '头像的地址',//头像
            'city'   => '深圳',
            'sex'   => '1',
            'age'   => '22',
            'height'   => '170',
            'weight'   => '120',
            'address'   => '固戍一路',//收货地址
            'integral'   => '9999',//积分
            'qr_code'   => '二维码地址',//二维码的地址
            'medal'   => '青铜三段',//奖章
            'integral_swap'   => 0,//累积用券兑换积分的总和
            'steps'   => '[{"2017-11-30":100},{"2017-12-1":200}]',//步数

            'friends_id'    => '[1,2,3]',//好友的id
            'fans_id'    => '[1,2,3]',//粉丝的id
            'merchant_id'    => '[1,2,3]',//收藏的商家id
            'collection_coupon_id'    => '[1,2,3]',//收藏的优惠券id
            'coupon_id'    => '[1,2,3]',//拥有的优惠券id
            'attention_id'    => '[1,2,3]',//关注的商家id
            'tesco'    => '[1,2,3]',//已购商品id,["1","2","3"]'

            'api_token' => str_random(60),//api_=token
        ]);
    }
}
