<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Ification;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\couponcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Redis;
use Storage;//图片上传
use JPush\Client as JPush; //极光
class MerchantController extends Controller
{
    public function index()
    {
        return view('admin.merchant.index');
    }

    public function create(Ification $ification)
    {
        $data['ification'] =$ification->select('id', 'cate_name')->get();
        return view('admin.merchant.create', $data);
    }

    public function store(Request $request, Merchant $merchant)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'msg' => '非法的请求类型'];
        }
        $data = $request->only('nickname', 'ification_id', 'address', 'img_url', 'avatar', 'labelling', 'store_image');

        $role = [
            'nickname' => 'required',
            'ification_id' => 'required',
            'address' => 'required',
            'img_url' => 'required|image|max:2048',
            'avatar' => 'required|image|max:2048',
            'store_image' => 'required|image|max:2048',
            'labelling' => 'required',
        ];
        $message = [
            'nickname.required' => '商家名不能为空！',
            'ification_id.required' => '分类不能为空！',
            'address.required' => '地址不能为空！',
            'img_url.required' => '封面图片不能为空！',
            'img_url.image' => '封面图片格式只能是jpeg,bmp,jpg,gif,gpeg,png格式！',
            'img_url.max' => '封面图片大小不能超过2m！',
            'avatar.required' => '头像不能为空！',
            'avatar.image' => '头像格式只能是jpeg,bmp,jpg,gif,gpeg,png格式！',
            'avatar.max' => '头像大小不能超过2m！',
            'store_image.required' => '店铺图片不能为空！',
            'store_image.image' => '店铺图片格式只能是jpeg,bmp,jpg,gif,gpeg,png格式！',
            'store_image.max' => '店铺图片大小不能超过2m！',
            'labelling.required' => '标签不能为空！'
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //保存头像
        $res = uploadpic('avatar', 'uploads/avatar');//
        switch ($res) {
            case 1:
                return ['status' => 'fail', 'msg' => '头像上传失败'];
            case 2:
                return ['status' => 'fail', 'msg' => '头像不合法'];
            case 3:
                return ['status' => 'fail', 'msg' => '头像后缀不对'];
            case 4:
                return ['status' => 'fail', 'msg' => '头像储存失败'];
        }
        $data['avatar'] = $res; //把得到的地址给picname存到数据库
        //保存首页
        $res2 = uploadpic('img_url', 'uploads/img_url');//
        switch ($res2) {
            case 1:
                return ['status' => 'fail', 'msg' => '封面图片上传失败'];
            case 2:
                return ['status' => 'fail', 'msg' => '封面图片不合法'];
            case 3:
                return ['status' => 'fail', 'msg' => '封面图片后缀不对'];
            case 4:
                return ['status' => 'fail', 'msg' => '封面图片储存失败'];
        }
        $data['img_url'] = $res2; //封面
        //店铺图
        $res3 = uploadpic('store_image', 'uploads/store_image');
        switch ($res) {
            case 1:
                return ['status' => 'fail', 'msg' => '店铺图上传失败'];
            case 2:
                return ['status' => 'fail', 'msg' => '店铺图不合法'];
            case 3:
                return ['status' => 'fail', 'msg' => '店铺图后缀不对'];
            case 4:
                return ['status' => 'fail', 'msg' => '店铺图储存失败'];
        }
        $data['store_image'] = $res3;
        $data['appraise_n'] = 5;
        //调整纬度
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        $u = "http://api.map.baidu.com/geocoder/v2/?address={$data['address']}&output=json&ak={$ak}";
        $address_data = file_get_contents($u);
        $json_data = json_decode($address_data);
        if ($json_data->status == 1) {
            unlink($data['avatar']);
            unlink($data['img_url']);
            unlink($data['store_image']);
            return ['status' => 'fail', 'msg' => '添加失败,该地址无法获得经纬度'];
        }
        $arr['lng'] = $json_data->result->location->lng;
        $arr['lat'] = $json_data->result->location->lat;
        $data['latitude'] = $arr;
        $res = $merchant->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Merchant $merchant)
    {
        if ($request->ajax()) {
            $data = $merchant
                ->leftJoin('ification','ification.id','=','merchant.ification_id')
                ->select('merchant.id', 'merchant.labelling', 'merchant.nickname', 'merchant.ification_id', 'merchant.appraise_n', 'merchant.address', 'merchant.latitude', 'merchant.img_url', 'merchant.avatar', 'merchant.disabled_at', 'merchant.store_image','merchant.deleted_at','ification.cate_name')
                ->withTrashed()->get();//包含软删除的
//            dump($data);die();
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

    public function edit(Merchant $merchant,Ification $ification)
    {
        $data['ification'] =$ification->select('id', 'cate_name')->get();
        $data['merchantInfo'] = $merchant;
        return view('admin.merchant.edit', $data);
    }

    public function update(Request $request, Merchant $merchant)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'msg' => '非法的请求类型'];
        }
        $data = $request->only('nickname', 'ification_id', 'address', 'img_url', 'avatar', 'labelling', 'disabled_at', 'store_image');
        $data['disabled_at'] = $data['disabled_at'] == 1 ? null : date('Y-m-d H:i:s');
        //如果字段为空,则删除 排除禁用的情况
        foreach ($data as $k => $v) {
            if (empty($v) && $k != 'disabled_at') {
                unset($data[$k]);
            }
        }
        $role = [
            'ification_id' => 'nullable|between:1,2',
            'address' => 'nullable|between:1,150',
            'avatar' => 'nullable|image|max:2048',
            'store_image' => 'nullable|image|max:2048',
            'img_url' => 'nullable|image|max:2048',
        ];
        $message = [
            'ification_id.between' => '分类不合法！',
            'address.between' => '地址位数为1-150位！',
            'avatar.image' => '头像格式只能是jpeg,bmp,jpg,gif,gpeg,png格式！',
            'avatar.max' => '头像大小不能超过2m！',
            'img_url.image' => '封面图片格式只能是jpeg,bmp,jpg,gif,gpeg,png格式！',
            'img_url.max' => '封面图片大小不能超过2m！',
            'store_image.image' => '店铺图片格式只能是jpeg,bmp,jpg,gif,gpeg,png格式！',
            'store_image.max' => '店铺图片大小不能超过2m！'
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //保存图片
        if (!empty($data['avatar'])) {
            $res = uploadpic('avatar', 'uploads/avatar');//
            switch ($res) {
                case 1:
                    return ['status' => 'fail', 'msg' => '头像上传失败'];
                case 2:
                    return ['status' => 'fail', 'msg' => '头像不合法'];
                case 3:
                    return ['status' => 'fail', 'msg' => '头像后缀不对'];
                case 4:
                    return ['status' => 'fail', 'msg' => '头像储存失败'];
            }
            $data['avatar'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $merchant->avatar;
            if (!empty($ress)) {
                unlink($ress);
            }
        }
        if (!empty($data['img_url'])) {
            $res2 = uploadpic('img_url', 'uploads/img_url');//
            switch ($res2) {
                case 1:
                    return ['status' => 'fail', 'msg' => '封面图片上传失败'];
                case 2:
                    return ['status' => 'fail', 'msg' => '封面图片不合法'];
                case 3:
                    return ['status' => 'fail', 'msg' => '封面图片后缀不对'];
                case 4:
                    return ['status' => 'fail', 'msg' => '封面图片储存失败'];
            }
            $data['img_url'] = $res2; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $merchant->img_url;
            if (!empty($ress)) {
                unlink($ress);
            }
        }
        if (!empty($data['store_image'])) {
            $res2 = uploadpic('store_image', 'uploads/store_image');//
            switch ($res2) {
                case 1:
                    return ['status' => 'fail', 'msg' => '店铺图片上传失败'];
                case 2:
                    return ['status' => 'fail', 'msg' => '店铺图片不合法'];
                case 3:
                    return ['status' => 'fail', 'msg' => '店铺图片后缀不对'];
                case 4:
                    return ['status' => 'fail', 'msg' => '店铺图片储存失败'];
            }
            $data['store_image'] = $res2; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $merchant->store_image;
            if (!empty($ress)) {
                unlink($ress);
            }
        }
        if (!empty($data['address'])) {
            $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
            $u = "http://api.map.baidu.com/geocoder/v2/?address={$data['address']}&output=json&ak={$ak}";
            $address_data = file_get_contents($u);
            $json_data = json_decode($address_data);
            if ($json_data->status == 1) {
                if (!empty($data['img_url'])) {
                    unlink($data['img_url']);
                }
                if (!empty($data['avatar'])) {
                    unlink($data['avatar']);
                }
                if (!empty($data['store_image'])) {
                    unlink($data['store_image']);
                }
                return ['status' => 'fail', 'msg' => '添加失败,该地址无法获得经纬度'];
            }
            $arr['lng'] = $json_data->result->location->lng;
            $arr['lat'] = $json_data->result->location->lat;
            $data['latitude'] = $arr;
        }
        // 更新数据
        $res = $merchant->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '更新成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'msg' => '更新失败！'];
        }
    }

    /**
     * 软删除
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function mer_disable(Request $request)
    {
        $id = $request['id'];
        #该商家是否有已发布的优惠券
        $coupon_category = new CouponCategory();
        $has_tf = $coupon_category->where('merchant_id',$id)->limit(1)->first();

        if(!empty($has_tf)){
            return ['status' => 'fail', 'msg' => '失败！请先删除该商家已发布的优惠券'];
        }
        $merchant = new Merchant();
        $del = $merchant->where('id',$id)->delete();
        if ($del) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'msg' => '删除失败！'];
        }
    }

    /**
     * 彻底删除
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function destroy($id)
    {
        #该商家是否有已发布的优惠券
        $coupon_category = new couponcategory();
        $has_tf = $coupon_category->where('merchant_id',$id)->limit(1)->first();
        if(!empty($has_tf)){
            return ['status' => 'fail', 'msg' => '删除失败！请先删除该商家已发布的优惠券'];
        }

        $merchant = new Merchant();
        $merchant = $merchant->find($id);
        //删除该条记录
        $res = $merchant->forceDelete();
        if ($res) {
            //删除商家下的所有图片
            $pic_arr = [$merchant['img_url'],$merchant['avatar'],$merchant['store_image']];
            array_walk(
                $pic_arr,
                function (&$s, $k, $prefix='./') {
                    $s = str_pad($s, strlen($prefix) + strlen($s), $prefix, STR_PAD_LEFT);
                }
            );
            //批量删除
            delPics($pic_arr);
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'msg' => '删除失败！'];
        }
    }

    /**
     * 收藏商家->收藏
     */
    public function collection_merchants(Request $request, Member $member)
    {
        $data = $request->only('member_id', 'merchant_id');
        $role = [
            'member_id' => 'required|exists:member,id',
            'merchant_id' => 'required|exists:merchant,id',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'member_id.exists' => '用户id非法！',
            'merchant_id.required' => '商家id不能为空！',
            'merchant_id.exists' => '您关注的商家id非法！',
        ];
        // 验证商家ID是否存在
        // 
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //直接将该id存入用户的收藏字段中
        $res = $member->select('merchant_id')->find($data['member_id']);
        if (empty($res['merchant_id'])) {
            //为空,直接插入
            $arr[] = $data['merchant_id'];
        } else {
            //不为空,
            $arr = json_decode($res['merchant_id'], true);
            if (in_array($data['merchant_id'], $arr)) {
                res(null, '已收藏过了', 'success', 202);
            }
            array_push($arr, $data['merchant_id']);
        }
        $arr = json_encode($arr);
        $res = $member->where('id', $data['member_id'])->update(['merchant_id' => $arr]);
        if ($res) {
            res(null, '收藏成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 取消收藏
     * $merchant_id 本收藏店铺的id集  $data['merchant_id'] 要取消的店铺id
     */
    public function cancel_collection_merchants(Request $request, Member $member)
    {
        $data = $request->only("member_id", 'merchant_id');
        $role = [
            'member_id' => 'required',
            'merchant_id' => 'required | exists:merchant,id',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'merchant_id.required' => '商家id不能为空！',
            'merchant_id.exists' => '商家id非法！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $member->select('merchant_id')->find($data['member_id']);
        $merchant_id = json_decode($res1['merchant_id'], true);
        if (empty($merchant_id) || !in_array($data['merchant_id'], $merchant_id)) {
            res(null, '失败', 'fail', 100);
        }
        // 删除指定单个店铺
        $merchants1 = delByValue($merchant_id,$data['merchant_id']);//删除指定值（调用公共方法）
        $merchants2 = array_values($merchants1);
        $merchants3 = json_encode($merchants2);
        $res3 = $member->select()->where('id', $data['member_id'])->update(['merchant_id' => $merchants3]);
        if ($res3) {
            res(null, '成功');

        }
        res(null, '失败', 'fail', 100);
    }

    /*
     * 批量取消收藏
     * $merchant_id 本收藏店铺的id集  $del 要取消的店铺id数组集
     */
    public function cancels_collection_merchants(Request $request, Member $member, Merchant $merchant)
    {
        $data = $request->only("member_id", 'merchant_id');
        $role = [
            'member_id' => 'required',
            'merchant_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'merchant_id.required' => '商家id不能为空！',//[1,2,3]
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $member->select('merchant_id')->find($data['member_id']);
        $merchant_id = json_decode($res1['merchant_id'], true);//收藏的Id
        if (empty($merchant_id)) {
            res(null, '失败', 'fail', 100);
        }
        $del = json_decode($data['merchant_id'], true);
        if (empty($del[0])) {
            res(null, '商家id集错误,必须是[1,2,3]的格式', 'fail', 100);
        }
        //删除指定数组集
        $merchants1 = array_diff($merchant_id,$del);
        $merchants2 = array_values($merchants1);
        $merchants3 = json_encode($merchants2);
        $res3 = $member->select()->where('id', $data['member_id'])->update(['merchant_id' => $merchants3]);

        if ($res3) {
            res(null, '成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 收藏商家 首页
     */
    public function show_merchant(Request $request, Member $member, Merchant $merchant)
    {
        $data = $request->only("member_id", 'lat', 'lng');
        $role = [
            'member_id' => 'required | exists:member,id',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'member_id.exists' => '请求非法！',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $member->select('merchant_id')->find($data['member_id']);
        $merchant_id = json_decode($res1['merchant_id'], true);
        if (empty($res1['merchant_id']) || $merchant_id == null) {
            res(null, '成功,没有数据', 'fail', 201);
        }
        $res2 = $merchant->select('img_url', 'nickname', 'appraise_n', 'id', 'labelling', 'latitude', 'ification_id', 'avatar', 'store_image')->whereIn('id', $merchant_id)->get();
        //用户的经纬度
        $latitude = bd_encrypt($data['lat'], $data['lng']);
        foreach ($res2 as $k => $v) {
            $distance = getDistance($latitude['lat'], $latitude['lng'], $v['latitude']['lat'], $v['latitude']['lng']);
            if ($v['ification_id'] == 1) {
                $arr['food'][] = [
                    'merchant_id' => $v['id'],//id
                    'img_url' => $request->server('HTTP_HOST') . '/' . $v['store_image'],//封面
                    'nickname' => $v['nickname'],//店名
                    'appraise_n' => $v['appraise_n'],//星级
                    'labelling' => $v['labelling'],//标签
                    'distance' => $distance,//距离(米)
                ];
            } else {
                $arr['entertainment'][] = [
                    'merchant_id' => $v['id'],//id
                    'img_url' => $request->server('HTTP_HOST') . '/' . $v['store_image'],//封面
                    'nickname' => $v['nickname'],//店名
                    'appraise_n' => $v['appraise_n'],//星级
                    'labelling' => $v['labelling'],//标签
                    'distance' => $distance,//距离(米)
                ];
            }
        }
        res($arr);
    }

    /**
     *  商家详情
     */
    public function show_merchant_content(Request $request, Merchant $merchant, Coupon $coupon)
    {
        $data = $request->only('merchant_id', 'lat', 'lng');
        $role = [
            'merchant_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'merchant_id.required' => '商家id不能为空！',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $merchant->select('avatar', 'latitude', 'address', 'img_url')->find($data['merchant_id']);
        $arr = [
            'avatar' => $request->server('HTTP_HOST') . '/' . $res1['avatar'],//头像
            'img_url' => $request->server('HTTP_HOST') . '/' . $res1['img_url'],//封面图
            'lat' => $res1['latitude']['lat'],//北纬
            'lng' => $res1['latitude']['lng'],//东经
            'address' => $res1['address'],//地址
        ];
        $latitude = bd_encrypt($data['lat'], $data['lng']);
        $distance = getDistance($latitude['lat'], $latitude['lng'], $res1['latitude']['lat'], $res1['latitude']['lng']);
        $arr['distance'] = $distance;
        $res2 = $coupon->select('coupon_type', 'coupon_money', 'note', 'start_at', 'end_at')
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->where('member_id', null)->where('merchant_id', $data['merchant_id'])->limit(1)->first()->toArray();
        if (empty($res2['coupon_type'])) {
            res(null, '该商家的优惠券被抢完了', 'success', 201);
        }
        $arr['coupon'][] = [
            'action' => $res2['coupon_type'],//折扣类型
            'price' => number_format($res2['coupon_money'],1),//格式化优惠券面额
            'note' => $res2['note'],
            'start_at' => $res2['start_at'],
            'end_at' => $res2['end_at'],
        ];
        res($arr);
    }

}
