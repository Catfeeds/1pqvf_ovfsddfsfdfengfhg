<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class SubjectController extends Controller
{

    public function index()
    {
        return view('admin.subject.index');
    }

    public function create()
    {
        return view('admin.subject.create');
    }

    public function store(Request $request, Subject $subject)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('cate_name', 'img_url', 'cate_note');
        $role = [
            'cate_name' => 'required|unique:subject',
            'img_url' => 'required|image',
            'cate_note' => 'required',
        ];
        $message = [
            'cate_name.required' => '分类名称不能为空！',
            'cate_note.required' => '分类描述不能为空！',
            'img_url.required' => '封面图片不能为空！',
            'img_url.image' => '上传的封面图格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调用公共文件上传
        if (!empty($data['img_url'])) {
            $res = uploadpic('img_url', 'uploads/img_url');//
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
            $data['img_url'] = $res; //把得到的地址给picname存到数据库
        }
        //入库
        $res = $subject->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Subject $subject)
    {
        if ($request->ajax()) {
            $data = $subject->select('id', 'cate_name', 'img_url', 'cate_note')->get();
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

    public function edit(Subject $subject)
    {
        $data['subjectInfo'] = $subject;
        return view('admin.subject.edit', $data);
    }

    public function update(Request $request, Subject $subject)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('cate_name', 'img_url', 'cate_note');
        // 校验数据
        $role = [
            'cate_name' => 'required|unique:subject,cate_name,' . $subject->id,
            'img_url' => 'nullable|image',
        ];
        $message = [
            'cate_name.required' => '分类名称不能为空！',
            'cate_name.unique' => '分类已存在！',
            'img_url.image' => '上传的图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调用公共文件上传
        if (!empty($data['img_url'])) {
            $res = uploadpic('img_url', 'uploads/img_url');//
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
            $data['img_url'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $subject->img_url;
            if (!empty($ress)) {
                unlink($ress);
            }
        } else {
            unset($data['img_url']);
        }
        if (empty($data['cate_note'])) {
            unset($data['cate_note']);
        }
        // 更新数据
        $res = $subject->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $subject = new Subject();
        $subject = $subject->find($id);
        $res = $subject->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     *  话题首页
     */
    public function show_list(Request $request, Subject $subject)
    {
        $res = $subject->select('id', 'img_url', 'cate_name', 'cate_note')->get()->toArray();
        if (empty($res[0])) {
            res(null, '没有数据', 'success', 201);
        }
        foreach ($res as $k => $v) {
            $res[$k]['subject_id'] = $v['id'];
            $res[$k]['img_url'] = $request->server('HTTP_HOST') . '/' . $v['img_url'];
            unset($res[$k]['id']);
        }
        res($res);
    }
}
