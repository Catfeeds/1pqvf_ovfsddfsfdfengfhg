<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Merchant;
use App\Models\couponcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class CouponCategoryController extends Controller
{

    public function index()
    {
        return view('admin.coupon_category.index');
    }

    public function create(Request $request, Merchant $merchant)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        return view('admin.coupon_category.create', $data);
    }

    public function store(Request $request, CouponCategory $coupon_category,Merchant $merchant)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('coupon_name','merchant_id','coupon_type','coupon_money','spend_money','send_start_at','send_end_at','deduction_url','picture_url', 'coupon_type','content');
        $role = [
            'coupon_name' => 'required|string|between:2,12',
            'merchant_id' => 'exists:merchant,id',
            'coupon_type' => 'required|integer|between:1,5',
            'coupon_money' => 'required|numeric',
            'spend_money' => 'nullable|sometimes|numeric',
            'send_start_at' => 'required|date',
            'send_end_at' => 'required|after_or_equal:start_at',
            'picture_url' => 'required|image',
            'deduction_url' => 'required|image',
        ];
        $message = [
            'coupon_name.required' => '必须填写优惠券名称',
            'coupon_name.string' => '优惠券名称不合法',
            'coupon_name.between' => '优惠券名称字节长度为2到12位',
            'merchant_id.exists' => '商家不存在！',
            'coupon_money.required' => '优惠券面额不能为空！',
            'coupon_money.numeric' => '优惠券面额不正确！',
            'coupon_type.required' => '优惠券类型不能为空！',
            'coupon_type.integer' => '优惠券类型不正确',
            'coupon_type.between' => '优惠券类型不正确',
            'spend_money.numeric' => '最低消费额不正确',
            'send_start_at.date' => '开始时间类型错误！',
            'send_start_at.required'=>'开始时间不能为空',
            'send_end_at.required'=>'结束时间不能为空',
            'send_end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'picture_url.required' => '优惠券图片不能为空！',
            'picture_url.image' => '优惠券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
            'deduction_url.required' => '抵扣券图片不能为空！',
            'deduction_url.image' => '抵扣券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
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
        $merchant_name = $merchant->select('nickname')->where('id',$data['merchant_id'])->first()->toArray();
        $data['merchant_name'] = $merchant_name['nickname'];
        $res = $coupon_category->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, couponcategory $couponcategory)
    {
        if ($request->ajax()) {
            $data = $couponcategory->with('merchant')->select('id', 'merchant_id','coupon_name', 'coupon_money', 'deduction_url', 'picture_url', 'coupon_type')->get();
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

    public function edit(CouponCategory $coupon_category, Merchant $merchant)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        $data['pictureInfo'] = $coupon_category;
        return view('admin.coupon_category.edit', $data);
    }

    public function update(Request $request, CouponCategory $coupon_category)
    {
        if (!$request->ajax()) {
            return ['status' => "fail", 'error' => "非法的请求类型"];
        }
        $data = $request->only('coupon_name','merchant_id','coupon_type','coupon_money','spend_money','send_start_at','send_end_at','deduction_url','picture_url', 'coupon_type','content');
        $role = [
            'coupon_name' => 'string|between:2,12',
            'merchant_id' => 'exists:merchant,id',
            'coupon_type' => 'integer|between:1,5',
            'coupon_money' => 'numeric',
            'spend_money' => 'nullable|sometimes|numeric',
            'send_start_at' => 'nullable|date',
            'send_end_at' => 'nullable|after_or_equal:start_at',
            'picture_url' => 'nullable|image',
            'deduction_url' => 'nullable|image',
        ];
        $message = [
            'coupon_name.string' => '优惠券名称不合法',
            'coupon_name.between' => '优惠券名称字节长度为2到12位',
            'merchant_id.exists' => '商家不存在！',
            'coupon_money.numeric' => '优惠券面额不正确！',
            'coupon_type.integer' => '优惠券类型不正确',
            'coupon_type.between' => '优惠券类型不正确',
            'spend_money.numeric' => '最低消费额不正确',
            'send_start_at.date' => '开始时间类型错误！',
            'send_start_at.required'=>'开始时间不能为空',
            'send_end_at.required'=>'结束时间不能为空',
            'send_end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'picture_url.image' => '优惠券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
            'deduction_url.image' => '抵扣券图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        if ($data['coupon_money'] < 1) {
            return ['status' => 'fail', 'msg' => "优惠券面额不能为小于1"];
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
        $data = array_filter($data);//为空的是不更新的部分
        // 更新数据
        $res = $coupon_category->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => "修改成功"];
        } else {
            return ['status' => 'fail', 'code' => 3, 'msg' => "修改失败！"];
        }

    }
    /**
     * 删除
     */
    public function destroy($id,Coupon $coupon)
    {
        #判断是否存在已发行的优惠券
        $tf = $coupon->select('id')->where('cp_cate_id',$id)->first();
        if(!empty($tf['id'])){
            return ['status' => 'fail', 'msg' => "删除失败，请先删除已发行的优惠券！"];
        }
        #删除图片
        $coupon_category = new CouponCategory();
        $pic = $coupon_category->select('picture_url','deduction_url')->find($id);
        $dep = $pic['deduction_url'];
        $pic = './'.$pic['picture_url'];//拼接完整路径
        $dep = './'.$dep;
        if(file_exists($pic)){
            unlink($pic);
        }
        if(file_exists($dep)){
            unlink($dep);
        }
        $res = $coupon_category->where('id',$id)->delete();
        if ($res) {
            return ['status' => "success"];
        } else {
            return ['status' => 'fail', 'msg' => "删除失败！"];
        }
    }

}
