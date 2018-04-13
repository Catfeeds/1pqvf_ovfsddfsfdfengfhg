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
            $admin->insert([
                'username'=> '方灿桩',
                'password'=> bcrypt('fangcanzhuang11'), //注意使用固定密码,hash加密
                'note'   => '超级管理员',
                'email'    => '1611787327@qq.com',
            ]);
    }
}
