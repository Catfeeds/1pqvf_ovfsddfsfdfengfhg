<?php

namespace App\Http\Controllers\Admin;

use App\Models\IntMall;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\TheDelivery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
//use Redis;
use Illuminate\Support\Facades\Redis;
use Storage;//图片上传
class IntMallController extends Controller
{
    public function index()
    {
        return view('admin.intmall.index');
    }

    public function create(Request $request)
    {
        return view('admin.intmall.create');
    }

    public function store(Request $request, IntMall $intmall)
    {

        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('trade_name', 'img_url', 'trade_num', 'integral_price', 'rmb_price');

        $role = [
            'trade_name' => 'required',
            'img_url' => 'required|image|max:2048',//2m 单位kb  2048  200
            'trade_num' => 'required|integer',
            'integral_price' => 'required',
            'rmb_price' => 'nullable',
        ];
        $message = [
            'trade_name.required' => '商品名不能为空！',
            'img_url.required' => '商品图片不能为空！',
            'img_url.image' => '商品图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'img_url.max' => '图片大小不能超过2m！',
            'trade_num.required' => '商品数量不能为空！',
            'trade_num.integer' => '商品数量必须为数值！',
            'integral_price.required' => '积分价格不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        if ($data['trade_num'] < 1) {
            $data['trade_num'] = 1;
        }
        if ($data['integral_price'] < 1) {
            $data['integral_price'] = 1;
        }
        //入库
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
        $res = $intmall->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, IntMall $intmall)
    {

        if ($request->ajax()) {
            $data = $intmall->select('id', 'trade_name', 'img_url', 'trade_num', 'integral_price', 'rmb_price')
                ->get();
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

    public function edit(IntMall $intMall)
    {
        $data['intmallInfo'] = $intMall;
        return view('admin.intmall.edit', $data);
    }

    public function update(Request $request, IntMall $intMall)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('trade_name', 'img_url', 'trade_num', 'integral_price', 'rmb_price');
        $role = [
            'img_url' => 'nullable|image|max:2048',//2m 单位kb  2048  200
            'trade_num' => 'nullable|integer',
        ];
        $message = [
            'img_url.image' => '商品图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'img_url.max' => '图片大小不能超过2m！',
            'trade_num.integer' => '商品数量必须为数值！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
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
            $ress = $intMall->img_url;
            if (!empty($ress)) {
                unlink($ress);
            }
        } else {
            unset($data['img_url']);
        }
        if (!empty($data['trade_num']) && $data['trade_num'] < 0) {
            $data['trade_num'] = 1;
        }
        if (!empty($data['integral_price']) && $data['integral_price'] < 0) {
            $data['integral_price'] = 1;
        }
        // 更新数据
        //false 失败
        $res = $intMall->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '更新成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'msg' => '更新失败！'];
        }
    }

    public function destroy($id)
    {
        $intmall = new IntMall();
        $intmall = $intmall->find($id);
        $res = $intmall->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /*
     * 我的->积分商城
     */
    public function convertible(Request $request, Member $member, IntMall $intMall)
    {
        $data = $request->only("member_id");
        $role = [
            'member_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $member->select('integral', 'tesco')->find($data['member_id']);
        $integral = empty($res1['integral']) ? 0 : $res1['integral'];
        //找出可以兑换的(不再用户的已兑换中)
        if (empty($res1['tesco'])) {
            //为空,直接找出所有商品
            $res2 = $intMall->select('id', 'integral_price', 'trade_name', 'img_url')->get();
        } else {
            $tesco = json_decode($res1['tesco'], true);
            $res2 = $intMall->select('id', 'integral_price', 'trade_name', 'img_url')->whereNotIn('id', $tesco)->get();
        }
        $arr = [];
        foreach ($res2 as $k => $v) {
            $arr[$k]['intmall_id'] = $v['id'];
            $arr[$k]['integral_price'] = $v['integral_price'];
            $arr[$k]['trade_name'] = $v['trade_name'];
            $arr[$k]['img_url'] = $request->server('HTTP_HOST') . '/' . $v['img_url'];
        }
        $arr = empty($arr) ? null :$arr;
        $data = [
            'integral' => $integral,//我的积分
            'list' => $arr,
        ];
        res($data);
    }

    /*
     * 兑换
     */
    public function exchange(Request $request, Member $member, IntMall $intMall, TheDelivery $theDelivery)
    {
        $data = $request->only("member_id", 'intmall_id');
        $role = [
            'member_id' => 'required',
            'intmall_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'intmall_id.required' => '商品id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询商品需要的积分,查询用户拥有的积分
        $res1 = $member->select('integral', 'address', 'tesco')->find($data['member_id']);
        $integral = empty($res1['integral']) ? 0 : $res1['integral'];
        $res2 = $intMall->select('integral_price')->find($data['intmall_id']);
        $integral_price = empty($res2['integral_price']) ? 0 : $res2['integral_price'];
        if ($integral >= $integral_price) {
            //用户的积分大于等于商品需要的积分,先判断用户的收货地址是否完善,如果不完善,直接中断
            if (!empty($res1['address'])) {
                $add = json_decode($res1['address'], true);
                $add = $add['address'];
                //判断收货地址是否完善,如果存在null,则需要完善
                $falg = false;
                if (array_key_exists('nickname', $add) && array_key_exists('phone', $add) && array_key_exists('province', $add) && array_key_exists('address', $add) && array_key_exists('zip_code', $add)) {
                    $falg = true;
                }
                if ($falg == false) {
                    res(null, '收货地址信息不完善', 'fail', 105);
                }
                //地址完善,先将用户当前的积分,减掉,然后将商品id存入'已购商品'字段,同时积分更新
                $num = $integral - $integral_price;
                if (empty($res1['tesco'])) {
                    $tesco[] = $data['intmall_id'];
                } else {
                    $tesco = json_decode($res1['tesco'], true);
                    array_push($tesco, $data['intmall_id']);
                }
                $tesco = json_encode($tesco);
                $re = $member->where('id', $data['member_id'])->update(['integral' => $num, 'tesco' => $tesco]);
                if ($re) {
                    //兑换成功,将信息加入发货表
                    $data = [
                        'member_id' => $data['member_id'],
                        'intmall_id' => $data['intmall_id'],
                    ];
                    $theDelivery->create($data);
                    res(null, '兑换成功');
                }
                res(null, '兑换失败', 'fail', 100);
                //后面,需要增加一个发货表,将用户兑换的商品放进去,好方便管理员发货
            } else {
                res(null, '收货地址为空', 'fail', 105);
            }
        } else {
            res(null, '积分不足', 'fail', 105);
        }
    }

    /**
     *  我的->兑换记录
     */
    public function record(Request $request, TheDelivery $theDelivery, IntMall $intMall, Member $member)
    {
        $data = $request->only("member_id");
        $role = [
            'member_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //返回用户的信息
        $res = $member->select('avatar', 'nickname')->find($data['member_id']);
        $arr['avatar'] = $request->server('HTTP_HOST') . '/' . $res['avatar'];
        $arr['nickname'] = $res['nickname'];
        $res1 = $theDelivery->where('member_id', $data['member_id'])->select('created_at', 'intmall_id')->get();
        if (empty($res1[0])) {
            res(null, '记录为空', 'success', 201);
        }
        foreach ($res1 as $k => $v) {
            $res2 = $intMall->select('trade_name', 'integral_price')->find($v['intmall_id']);
            $arr['record'][] = date('Y年m月d日', strtotime($v['created_at'])) . "花费" . $res2['integral_price'] . "积分兑换了" . $res2['trade_name'];
        }
        res($arr);
    }

    /*
     * 已兑换
     */
    public function hasChange(Request $request, Member $member, IntMall $intMall)
    {
        $data = $request->only("member_id");
        $role = [
            'member_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $member->select('integral', 'tesco')->find($data['member_id']);
        //找出可以兑换的(不再用户的已兑换中)
        if (empty($res1['tesco'])) {
            //为空,没有商品
            $arr = null;
        } else {
            $tesco = json_decode($res1['tesco'], true);
            $res2 = $intMall->select('id', 'integral_price', 'trade_name', 'img_url')->whereIn('id', $tesco)->get();
            foreach ($res2 as $k => $v) {
                $arr[$k]['intmall_id'] = $v['id'];
                $arr[$k]['integral_price'] = $v['integral_price'];
                $arr[$k]['trade_name'] = $v['trade_name'];
                $arr[$k]['img_url'] = $request->server('HTTP_HOST') . '/' . $v['img_url'];
            }
        }
        $data = [
            'list' => $arr,
        ];
        res($data);
    }

}
