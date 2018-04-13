<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class AdminController extends Controller
{

    public function index()
    {
        return view('admin.admin.index');
    }

    public function create()
    {
        return view('admin.admin.create');
    }

    public function store(Request $request, Admin $admin)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('username', 'password', 'avatar', 'note', 'pwd', 'email');

        $role = [
            'username' => 'required|unique:admin',
            'password' => 'required|between:3,20|same:pwd',
            'note' => 'required',
            'email' => 'required|unique:admin|email'
        ];

        $message = [
            'username.required' => '用户名不能为空！',
            'username.unique' => '用户名已存在！',
            'password.required' => '密码不能为空！',
            'password.between' => '密码长度为3-20！',
            'password.same' => '密码和确认密码不一致！',
            'note.required' => '身份不能为空！',
            'email.required' => '邮箱不能为空！',
            'email.unique' => '当前邮箱已存在！',
            'email.email' => '当前邮箱格式不正确！',
        ];
        $validator = Validator::make($data, $role, $message);

        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //入库
        $data['password'] = bcrypt($data['password']);
        if (!empty($data['avatar'])) {
            $res = uploadpic('avatar', 'uploads/avatar');//
            switch ($res) {
                case 1:
                    return ['status' => 'fail', 'msg' => '图片上传失败'];
                case 2:
                    return ['status' => 'fail', 'msg' => '图片不合法'];
                case 3:
                    return ['status' => 'fail', 'msg' => '图片后缀不对'];
                case 4:
                    return ['status' => 'fail', 'msg' => '图片储存失败'];
            }
            $data['avatar'] = $res; //把得到的地址给picname存到数据库
        } else {
            $data['avatar'] = 'uploads/avatar/morentouxiang.png';
        }
        $res = $admin->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Admin $admin)
    {

        if ($request->ajax()) {
            $data = $admin->select('id', 'avatar', 'username', 'note', 'created_at', 'disabled_at')->get();
            $cnt = count($data);
            $info = [
                'draw' => $request->get('draw'),
                'recordsTotal' => $cnt,
                'recordsFiltered' => $cnt,
                'data' => $data,
            ];

            return $info;
        }
    }

    public function edit(Admin $admin)
    {
        $data['adminInfo'] = $admin;
        return view('admin.admin.edit', $data);
    }

    public function update(Request $request, Admin $admin)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('username', 'avatar', 'note', 'email', 'disabled_at');
        // 校验数据
        $role = [
            'username' => 'required|unique:admin,username,' . $admin->id,
            'note' => 'required',
            'email' => 'required'
        ];
        $message = [
            'username.required' => '用户名不能为空！',
            'username.unique' => '用户名已存在！',
            'note.required' => '用户描述不能为空！',
            'email.required' => '邮箱不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        $old_email = $admin->email;
        if ($old_email != $data['email']) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => '邮箱错误'];
        } else {
            unset($data['eamil']);
        }
        if (!empty($data['avatar'])) {
            $res = uploadpic('avatar', 'uploads/avatar');//
            switch ($res) {
                case 1:
                    return ['status' => 'fail', 'msg' => '图片上传失败'];
                case 2:
                    return ['status' => 'fail', 'msg' => '图片不合法'];
                case 3:
                    return ['status' => 'fail', 'msg' => '图片后缀不对'];
                case 4:
                    return ['status' => 'fail', 'msg' => '图片储存失败'];
            }
            $data['avatar'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $admin->avatar;
            if (!empty($ress) && $ress !== 'uploads/avatar/morentouxiang.png') {
                unlink($ress);
            }
        } else {
            unset($data['avatar']);
        }
        // 数据调整
        $data['disabled_at'] = $data['disabled_at'] == 1 ? null : date('Y-m-d H:i:s');
        // 更新数据
        $res = $admin->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $admin = new Admin;
        $admin = $admin->find($id);
        $res = $admin->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

}
