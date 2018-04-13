<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Member;
use App\Models\Swap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
class TestController extends Controller
{
    public function test(){
      res($_SERVER);
    }
    //测试
    public function tt(){
        $arr =  bd_encrypt(22.5443700000,113.9446000000);
    }
    public function t2(Request $request){
        $filepath = 'uploads/images';
        if($request->hasFile('img_url')){
            foreach($request->file('img_url') as $file) {
                if(in_array( strtolower($file->extension()),['jpeg','bmp','jpg','gif','gpeg','png'])){
                    //          4.将文件取一个新的名字
                    $name = '.'.$file->extension();
                    $newName = 'img'.time().rand(100000, 999999).$name;//$file->getClientOriginalName();
                    //           5.移动文件,并修改名字
                    if($file->move($filepath,$newName)){
                        $data['img_url'][] =$filepath.'/'.$newName;
//                        return ;   //返回一个地址
                    }else{
                        return 4;
                    }
//                $file->move(base_path().'/public/uploads/images/', $file->getClientOriginalName());
            }else{
                return '图片不合法';
            }
        }
    }else{
            return '没有图片                                                                                                                                                                                                                                                                                ';
        }
    dump($data);
}
    public function t3( Request $request ){
        dump($_POST);
        dump($_GET);
        die;
        $a = 'medal/x1';
        $b = 'medal/x2';
        $data = [
            'create'=>'medal/x1',
            'select'=>'medal/x2',
        ];
        echo json_encode($data);
    }
}
