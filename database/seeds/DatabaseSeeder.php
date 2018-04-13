<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Admin;
class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
    //     */
    public function run(Admin $admin)
    {
        $admin -> truncate();//重置
        //实例化factory类
        /*有问题  php artisan db:seed --class=AdminTableSeeder*/
        $faker = Factory::create('zh_CN');//设置生成中文的数据
        for($i = 0;$i<=100;$i++){
            $admin->insert([
                'role_id' => mt_rand(1,5),
                'username'=> $faker->name,
                'nickname'=> $faker->name,
                'password'=> bcrypt('123456'), //注意使用固定密码,以为会直接生成他一个加密的
                'email'   => $faker->email,
                'phone'   => $faker->PhoneNumber,
                'sex'     => mt_rand(1,2),
                'login_ip'=> $faker->ipv4
            ]);
        }
    }
}
