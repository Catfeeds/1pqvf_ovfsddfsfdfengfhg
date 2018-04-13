<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\Picture;
use App\Models\Swap;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\DB;

class SwapController extends Controller
{

    public function index()
    {
        return view('admin.swap.index');
    }

    public function create(Request $request, Member $member)
    {
        $data['memberInfo'] = $member->select('id', 'nickname')->get();
        return view('admin.swap.create', $data);
    }

    public function ajax_coupon(Request $request, Coupon $coupon)
    {
        $data['couponInfo'] = $coupon->where('member_id', $request->only('member_id'))->where('status', 1)->with('merchant')->get();
        return $data;
    }

    public function store(Request $request, Swap $swap, Coupon $coupon)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('member_id', 'coupon_id', 'integral');
        $role = [
            'integral' => 'required|numeric',
        ];
        $message = [
            'integral.required' => '兑换积分不能为空！',
            'integral.numeric' => '兑换所需积分只能是数量！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        } elseif ($data['integral'] < 1) {
            return ['status' => 'fail', 'msg' => '兑换所需积分必须大于1积分'];
        } elseif ($data['coupon_id'] == null) {
            return ['status' => 'fail', 'msg' => '该用户没有可以互换的优惠券!'];
        }
        //数据调整
        $data['status'] = 2;//'状态1:已被兑换,2:还没人兑换'
        //入库
        $res = $swap->create($data);
        //入库之后,将该用户的持有人取消
        $res2 = $coupon->where(['id' => $data['coupon_id']])->select()->update(['status' => 3]);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Swap $swap, Member $member)
    {
        if ($request->ajax()) {
            $data = $swap->with('member')->with('coupon')->select('id', 'coupon_id', 'member_id', 'status', 'integral')->get();
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

    public function edit(Swap $swap)
    {
        $data['swapInfo'] = $swap;
        return view('admin.swap.edit', $data);

    }

    public function update(Request $request, Swap $swap)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('integral', 'status');
        // 接收数据
        // 校验数据
        $role = [
            'integral' => 'required|numeric',
            'status' => 'required',
        ];
        $message = [
            'integral.required' => '兑换积分不能为空！',
            'integral.numeric' => '兑换积分只能为数值！',
            'status.required' => '状态非法！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        } elseif ($data['integral'] < 1) {
            return ['status' => 'fail', 'msg' => '兑换所需积分必须大于1积分'];
        } elseif ($data['status'] != 2) {
            return ['status' => 'fail', 'msg' => '当前兑换不能编辑'];
        }
        unset($data['status']);
        // 更新数据
        $res = $swap->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }

    }

    public function destroy($id)
    {
        $swap = new Swap;
        $swap = $swap->find($id);
        $res = $swap->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     *  互换首页
     */
    public function show_list(Request $request, Swap $swap, Member $member, Merchant $merchant, Coupon $coupon, Picture $picture)
    {
        //所有互换,兑换所需的积分
        $res = $swap->select('id', 'member_id', 'coupon_id', 'integral')->where('status', 2)->orderBy('created_at', 'DESC')->get();
        if (empty($res[0])) {
            res(null, '查询成功', 'success', 201);
        }
        $arr = [];
        foreach ($res as $k => $v) {
            //找出发布人的id,根据id找出发布人的头像
            $row1 = $member->select('avatar')->find($v['member_id']);
            //优惠券图片id,商家id
            $row2 = $coupon->select('picture_id', 'merchant_id', 'status', 'end_at')->find($v['coupon_id']);//action ,'price'
            //如果不再等于可以使用,删除这条记录并修改优惠券状态 ,跳过本次循环
            if ($row2['status'] == 2 &&  $row2['status'] !== 3) {
                //删除记录后,跳过本次循环
                $a = $swap->where('id', $v['id'])->delete();
                continue;
            } else if (time() > strtotime($row2['end_at'])) { //结束时间:3.7  今天3.5 如果今天大于3.7 说明已经过期.更新状态
                //更新优惠券的状态,同时删除记录,然后跳过本次循环
                $re = $coupon->where('id', $v['coupon_id'])->update(['status' => 3]);
                $a = $swap->where('id', $v['id'])->delete();
                continue;
            }
            //找出抵扣券图片
            $row3 = $picture->select('deduction_url')->find($row2['picture_id']);
            //根据商家id,找出商家所在的经纬度,店名
            $row4 = $merchant->select('nickname')->find($row2['merchant_id']);//,'latitude'
            $arr[] = [
                'swap_id' => $v['id'],//记录的id
                'avatar' => $request->server('HTTP_HOST') . '/' . $row1['avatar'],//发布人的头像
                'coupon_img' => $request->server('HTTP_HOST') . '/' . $row3['deduction_url'],//优惠券的图片
                'integral' => $v['integral'],//所需积分
                'nickname' => $row4['nickname'],//店名
            ];
        }
        if (empty($arr)) {
            res(null, '查询成功', 'success', 201);
        }
        res($arr);
    }

    /*
     * 查询发布互换
     */
    public function select_release_swap(Request $request, Member $member, Coupon $coupon, Merchant $merchant)
    {
        $data = $request->only('member_id');
        $role = [
            'member_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res = $member->select('coupon_id')->find($data['member_id']);
        if (empty($res['coupon_id'])) {
            res(null, '用户没有可用的优惠券', 'success', 201);
        }
        //用户可以兑换的优惠券
        $time = date('Y-m-d H:i:s', time());//排除掉过期的->where('end_at','>',$time) 过期时间大于今天,说明还没过期
        $res2 = $coupon->select('merchant_id', 'action', 'price', 'id')->where('end_at', '>', $time)->whereIn('id', $res['coupon_id'])->where('status', 1)->groupBy('merchant_id')->get();
        if (empty($res2[0])) {
            res(null, '用户没有可用的优惠券', 'success', 201);
        }
        foreach ($res2 as $k => $v) {
            //找出店名
            $row = $merchant->select('nickname')->find($v['merchant_id']);
            //拼接
            $nickname = $row['nickname'];
            $action = $v['action'] == 1 ? '元' : '折';
            $note = $nickname . $v['price'] . $action . "优惠券"; //五十岚6元优惠券
            $arr['list'][] = [
                'coupon_id' => $v['id'],//该优惠券id
                'note' => $note,//右侧描述
            ];
        }
        res($arr);
    }

    /*
     * 发布互换
     */
    public function release_swap(Request $request, Swap $swap,Coupon $coupon)
    {
        $data = $request->only('member_id', 'coupon_id', 'integral');
        $role = [
            'member_id' => 'required',
            'coupon_id' => 'required',
            'integral' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'coupon_id.required' => '优惠券id不能为空！',
            'integral.required' => '兑换所需的积分不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $data['status'] = 2;
        //将其发布上去

        //发布后,将此优惠券从用户的优惠券字段删除
        $re = $coupon->where('id',$data['coupon_id'])->update(['status'=>4]);
        $res = $swap->create($data);
        if ($res->id && $re) {
            res(null, '成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     *   互换详情
     */
    public function details(Request $request, Swap $swap, Merchant $merchant, Coupon $coupon, Member $member)
    {
        $data = $request->only('swap_id', 'member_id', 'lat', 'lng');
        $role = [
            'swap_id' => 'required',
            'member_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'swap_id.required' => '互换id不能为空！',
            'member_id.required' => '互换id不能为空！',
            'lat.required' => '纬度不能为空！',
            'lng.required' => '经度不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //发布人的id $res['member_id']
        $res = $swap->select('coupon_id', 'member_id')->find($data['swap_id']);
        if( $data['member_id']  == $res['member_id'] ){
            $falg = 2;
        }else{
            $falg = 1;
        }
        //优惠券的说明
        $res2 = $coupon->select('content', 'merchant_id')->find($res['coupon_id']);
        //用户是否关注了这个商家
        $res3 = $member->select('merchant_id')->find($data['member_id']);
        $is_collection = 2;//没有收藏
        if (!empty($res3['merchant_id'])) {
            //不为空
            $merchant_id = json_decode($res3['merchant_id'], true);
            //并且当前查看的商家不在集合中
            $is_collection = in_array($res2['merchant_id'], $merchant_id) ? 1 : 2;
        }
        //找出商家的信息 -- 封面图,地址,经纬度,星级,距离
        $res4 = $merchant->select('img_url', 'address', 'latitude', 'appraise_n')->find($res2['merchant_id']);
        $distance = GetDistance2($data['lat'], $data['lng'], $res4['latitude']['lat'], $res4['latitude']['lng']);
        $content = json_decode($res2['content'], true);
        $arr = [
            'content' => $content,//优惠券使用说明
            'is_collection' => $is_collection,//是否收藏了商家, 1= 已收藏,2=未收藏
            'merchant_id' => $res2['merchant_id'],//商家的id,用于收藏用
            'img_url' => $request->server('HTTP_HOST') . '/' . $res4['img_url'],//封面图
            'address' => $res4['address'],//地址
            'lat' => $res4['latitude']['lat'],//经纬度
            'lng' => $res4['latitude']['lng'],//经纬度
            'appraise_n' => $res4['appraise_n'],//星级
            'distance' => sprintf("%.1f", $distance),//距离 只保留一位小数
            'flag' => $falg,//标识符 1=可以兑换, 2=不可以
        ];
        res($arr);
    }

    /**
     *  立即兑换
     */
    public function exchange(Request $request, Swap $swap, Member $member, Coupon $coupon)
    {
        //接受:要记录的id,要用户的id,
        $data = $request->only('swap_id', 'member_id');
        $role = [
            'swap_id' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'swap_id.required' => '互换id不能为空！',
            'member_id.required' => '互换id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查看此记录,是否处于未兑换  ->select('integral','status','coupon_id','')
        $res1 = $swap->find($data['swap_id']);
        if (empty($res1) || $res1['status'] != 2) {
            res(null, '已被兑换或此券已过期', 'fail', 202);
        }
        //开始兑换:
        //1:要兑换人的积分是否足够
        $res2 = $member->select('integral')->find($data['member_id']);
        if (empty($res2['integral']) || $res2['integral'] < $res1['integral']) { //如果用户没有积分,或者积分小于需要兑换的积分
            res(null, '积分不足', 'fail', 105);
        }
        //积分足够,扣掉用户积分,并将优惠券的id改为该用户,将记录状态改为已兑换,将发布人的卷换分加入
        DB::transaction(function () use ($res1, $res2, $data, $member, $coupon, $swap) {
            $integral = $res2['integral'] - $res1['integral'];
            $row1 = $member->where('id', $data['member_id'])->update(['integral' => $integral]);//扣掉用户积分
            $row2 = $coupon->where('id', $res1['coupon_id'])->update(['member_id' => $data['member_id'],'status'=>1]);//将优惠券的持有人改为当前用户,同时把状态修改
            $row3 = $swap->where('id', $data['swap_id'])->update(['status' => 1]);//将记录修改为已兑换
            //找出发布人的积分
            $res3 = $member->select('integral', 'integral_swap')->find($res1['member_id']);
            $integral = $res3['integral'] + $res1['integral'];
            $integral_swap = $res3['integral_swap'] + $res1['integral'];
            $row4 = $member->where('id', $res1['member_id'])->update(['integral' => $integral, 'integral_swap' => $integral_swap]);//将用户的积分加上去,同时券换分也加上
            if ($row1 && $row2 && $row3 && $row4) {
                DB::commit();
                res(null, '兑换成功');
            }
            DB::rollback();//事务回滚
            res(null, '兑换失败', 'fail', 100);
        });
    }
}
