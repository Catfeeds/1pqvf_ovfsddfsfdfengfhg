<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class IndexController extends Controller
{

    public function index()

    {

        return view('admin.index.index');
    }


    /*
     * 重定向到后台登录页面
     */
    public function redirect(Request $request)
    {
        return $request->photo->store('img_url', 'public');
    }

    /**
     * 后台登录页面
     */
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {

            $data = $request->only('username', 'password', 'captcha', 'online');
            $role = [
                'username' => 'required',
                'password' => 'required',
                'captcha' => 'required|captcha',
            ];

            $message = [
                'username.required' => '帐号不能为空！',
                'password.required' => '密码不能为空！',
                'captcha.required' => '验证码不能为空！',
                'captcha.captcha' => '验证码错误！',
            ];
            $validator = Validator::make($data, $role, $message);
            //如果验证失败,返回错误信息
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }
            // 记住登录
            $remember = $request->input('online');
            // 帐号进行登录
            $res = Auth::guard('admin')->attempt(['password' => $data['password'], 'username' => $data['username'], 'disabled_at' => null], $remember);
            if ($res) {
                // 登录成功!
                return redirect()->to('admin/index');
            } else {
                // 登录失败！
                return redirect()->back()->withErrors(['登录失败,用户名或密码错误']);
            }
        }
        return view('admin.index.login');
    }

    /**
     * 退出登录
     */
    public function loginout()
    {
        Auth::guard('admin')->logout();
        return redirect()->to('admin/login')->withErrors(['退出登录成功！']);
    }

    /**
     * 用户协议
     */
    public function reg_protocol()
    {
        return view('home.index.reg_protocol');
    }

    /**
     * 隐私政策
     */
    public function privacy()
    {
        return view('home.index.privacy');
    }

}
