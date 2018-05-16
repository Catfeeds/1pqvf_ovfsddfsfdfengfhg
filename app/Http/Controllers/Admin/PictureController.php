<?php

namespace App\Http\Controllers\Admin;

use App\Models\Merchant;
use App\Models\Picture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class PictureController extends Controller
{

    public function index()
    {
        return view('admin.picture.index');
    }

    public function create(Request $request, Merchant $merchant)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        return view('admin.picture.create', $data);
    }

    public function store(Request $request, Picture $picture)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('merchant_id', 'deduction_url', 'price', 'picture_url', 'action');
        $role = [
            'merchant_id' => 'required',
            'price' => 'required|numeric',
            'picture_url' => 'required|image',
            'deduction_url' => 'required|image',
            'action' => 'required|numeric',
        ];
        $message = [
            'merchant_id.required' => '商家不能为空！',
            'price.required' => '优惠额度不能为空！',
            'price.numeric' => '优惠额度不正确！',
            'picture_url.required' => '优惠券图片不能为空！',
            'picture_url.image' => '优惠券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
            'deduction_url.required' => '抵扣券图片不能为空！',
            'deduction_url.image' => '抵扣券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
            'action.required' => '优惠方式不能为空！',
            'action.numeric' => '优惠方式不正确',
        ];
        $validator = Validator::make($data, $role, $message);

        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        $res = uploadpic('picture_url', 'uploads/picture_url');//
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
        $data['picture_url'] = $res;
        $res = uploadpic('deduction_url', 'uploads/img_url');//
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
        $data['deduction_url'] = $res;
        $res = $picture->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Picture $picture)
    {
        if ($request->ajax()) {
            $data = $picture->with('merchant')->select('id', 'merchant_id', 'price', 'deduction_url', 'picture_url', 'action')->get();
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

    public function edit(Picture $picture, Merchant $merchant)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        $data['pictureInfo'] = $picture;
        return view('admin.picture.edit', $data);
    }

    public function update(Request $request, Picture $picture)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('merchant_id', 'price', 'picture_url', 'deduction_url', 'action', 'old_img', 'old_img2');
        // 接收数据
        $role = [
            'merchant_id' => 'required',
            'price' => 'nullable|numeric',
            'picture_url' => 'nullable|image',
            'deduction_url' => 'nullable|image',
            'action' => 'required|numeric',
        ];
        $message = [
            'merchant_id.required' => '商家不能为空！',
            'price.numeric' => '优惠额度不正确！',
            'picture_url.image' => '优惠券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
            'deduction_url.image' => '抵扣券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
            'action.required' => '优惠方式不能为空！',
            'action.numeric' => '优惠方式不正确！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //排除0和负数
        if ($data['price'] < 1) {
            $data['price'] = 1;
        }
        //调用公共文件上传
        if (!empty($data['picture_url'])) {
            $res = uploadpic('picture_url', 'uploads/picture_url');//
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
            $data['picture_url'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            if (!empty($data['old_img'])) {
                unlink($data['old_img']);
            }
        } else {
            unset($data['picture_url']);
        }
        if (!empty($data['deduction_url'])) {
            $res = uploadpic('deduction_url', 'uploads/img_url');//
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
            $data['deduction_url'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            if (!empty($data['old_img2'])) {
                unlink($data['old_img2']);
            }
        } else {
            unset($data['deduction_url']);
        }
        // 更新数据
        $res = $picture->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }

    }

    public function destroy($id)
    {
        $picture = new Picture();
        $picture = $picture->find($id);
        $res = $picture->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

}
