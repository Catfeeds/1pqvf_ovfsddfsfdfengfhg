<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class IficationController extends Controller
{

    public function index()
    {
        return view('admin.ification.index');
    }

    public function create(Request $request)
    {
        return view('admin.ification.create');
    }

    public function store(Request $request, Ification $ification)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('cate_name');
        $role = [
            'cate_name' => 'required|unique:ification',
        ];
        $message = [
            'cate_name.required' => '分类名称不能为空！',
            'cate_name.unique' => '分类名称已存在！',
        ];
        $validator = Validator::make($data, $role, $message);

        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        $res = $ification->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Ification $ification)
    {
        if ($request->ajax()) {
            $data = $ification->select('id', 'cate_name')->get();
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

    public function edit(Ification $ification)
    {
        $data['ificationInfo'] = $ification;
        return view('admin.ification.edit', $data);

    }

    public function update(Request $request, Ification $ification)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('cate_name');
        // 接收数据
        $role = [
            'cate_name' => 'required|unique:ification,cate_name,' . $ification->id,
        ];
        $message = [
            'cate_name.required' => '分类名称不能为空！',
            'cate_name.unique' => '分类名称已存在！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        // 更新数据
        $res = $ification->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }

    }

    public function destroy($id)
    {
        $ification = new Ification;
        $ification = $ification->find($id);
        $res = $ification->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

}
