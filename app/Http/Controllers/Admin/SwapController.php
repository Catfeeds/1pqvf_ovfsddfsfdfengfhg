<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\couponcategory;
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

    public function create(Member $member)
    {
        $data['memberInfo'] = $member->select('id', 'nickname')->get();
        return view('admin.swap.create', $data);
    }

    /**
     * 查看用户拥有的未使用的优惠券
     * @param Request $request
     * @param Coupon $coupon
     * @return mixed
     */
    public function ajax_coupon(Request $request, Coupon $coupon)
    {
        $data['couponInfo'] = $coupon
            ->select('coupon.id','coupon.start_at','coupon.end_at','coupon.note','coupon_category.coupon_name','coupon_category.coupon_type','coupon_category.coupon_money','merchant.nickname')
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->leftjoin('merchant','merchant.id','=','coupon_category.merchant_id')
            ->where('member_id', $request->only('member_id'))->where('status', 1)->get();
//        dump($data);
        return $data;
    }

    /**
     * 发布互换
     * @param Request $request
     * @param Swap $swap
     * @param Coupon $coupon
     * @return array
     */
    public function store(Request $request, Swap $swap, Coupon $coupon)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('member_id', 'coupon_id', 'integral');
        $role = [
            'integral' => 'required|integer|min:2',
            'coupon_id' => 'unique:swap,coupon_id|exists:coupon,id',
            'member_id' => 'exists:member,id',
        ];
        $message = [
            'integral.required' => '兑换积分不能为空！',
            'integral.integer' => '兑换所需积分只能是整数！',
            'integral.min' => '兑换所需积分必须大于1积分!',
            'coupon_id.unique' => '每张优惠券只能使用一次兑换功能！',
            'coupon_id.exists' => '优惠券不合法',
            'member_id.exists' => '用户不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //验证用户与优惠券是否匹配
        $coupon_tf = $coupon->select('id')->where('id',$data['coupon_id'])->where('member_id',$data['member_id'])->get();
        if (empty($coupon_tf)){
            return ['status' => 'fail', 'msg' => '优惠券与用户不匹配'];
        }
        //数据调整
        $data['status'] = 2;//'状态1:已被兑换,2:未兑换'
        DB::beginTransaction();
        try{
            $swap->create($data);//入库
            $coupon->where(['id' => $data['coupon_id']])->update(['status' => 4]);//入库之后,修改优惠券状态 ['status' => 4]冻结期
            DB::commit();
        }catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();//事务回滚
            return ['status' => 'fail', 'msg' => '添加失败'];
        }
        return ['status' => 'success', 'msg' => '添加成功'];

    }

    public function ajax_list(Request $request, Swap $swap, Member $member)
    {
        if ($request->ajax()) {
            $data = $swap
                ->select('swap.id','swap.coupon_id','swap.integral','swap.status','coupon.member_id','member.nickname','coupon.start_at','coupon.end_at','coupon_category.coupon_name','coupon_category.coupon_type','coupon_category.coupon_money','merchant.nickname as merchant_nickname','coupon_category.merchant_id')
                ->leftjoin('coupon','coupon.id','=','swap.coupon_id')
                ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
                ->leftjoin('merchant','merchant.id','=','coupon_category.merchant_id')
                ->leftjoin('member','member.id','=','coupon.member_id')
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
        $data = $request->only('integral','id');
        $role = [
            'integral' => 'required|integer|min:2',
            'id' => 'required',
        ];
        $message = [
            'integral.required' => '兑换积分不能为空！',
            'integral.integer' => '兑换所需积分只能是整数！',
            'integral.min' => '兑换所需积分必须大于1积分!',
            'id.required' => '请求不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        #只有当发布互换了才能被编辑
        $tf = $swap->select('id')->where('status',2)->where('id',$data['id'])->first();
        if(empty($tf)){
            return ['status' => 'fail', 'code' => 3, 'error' => '已兑换或数据有误'];
        }
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
        $swap = new Swap();
        $coupon = new Coupon();
        $swap = $swap->find($id);
        DB::beginTransaction();
        try{
            $coupon->where('id',$swap['coupon_id'])->update(['status' => 1]);//入库之后,修改优惠券状态 ['status' => 4]冻结期
            $swap->where('id',$id)->update(['status' =>0,'deleted_at'=> date('Y-m-d H:i:s', time())]);//;0失效
            DB::commit();
        }catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();//事务回滚
            return ['status' => 'fail', 'msg' => '删除失败'];
        }
        return ['status' => 'success', 'msg' => '删除成功'];
    }

    /**
     *  互换首页
     */
    public function show_list(Request $request, Swap $swap, Coupon $coupon)
    {
        # 获取所有的互换
        # 剔除过期的

        $res = $swap->select('swap.id','swap.member_id','swap.coupon_id','swap.integral', 'member.nickname as member_nickname','member.avatar',
            'merchant.nickname as merchant_nickname','merchant.address','merchant.latitude','merchant.img_url','merchant.appraise_n','coupon_category.merchant_id',
            'coupon_category.deduction_url', 'coupon.note','coupon.cp_cate_id','coupon.status','coupon.end_at','coupon.start_at')
            ->where('coupon.status',4)->where('swap.status', 2)//已发布且在冻结期的优惠券
            ->orderBy('swap.created_at', 'DESC')
            ->leftJoin('member','member.id','=','swap.member_id' )
            ->leftJoin('coupon','coupon.id','=','swap.coupon_id')
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->leftJoin('merchant','merchant.id','=','coupon_category.merchant_id')
            ->get();
//        dump($res);die();
        if (empty($res[0])) {
            res(null, '查询成功', 'success', 201);
        }
        $arr = [];
        foreach ($res as $k => $v) {
            //如果过期
            if (time() > strtotime($v['end_at'])) { //已经过期，更新状态，同时删除记录
                $swap->where('id', $v['id'])->update(['status' =>0,'deleted_at'=> date('Y-m-d H:i:s', time())]);
                $coupon->where('id', $v['coupon_id'])->update(['status' => 3]);
                continue;
            }
            $arr[] = [
                'swap_id' => $v['id'],//记录的id
                'avatar' => $request->server('HTTP_HOST') . '/' . $v['avatar'],//发布人的头像
                'coupon_img' => $request->server('HTTP_HOST') . '/' . $v['deduction_url'],//优惠券的图片
                'integral' => $v['integral'],//所需积分
                'nickname' => $v['merchant_nickname'],//店名
            ];
        }
        res($arr);
    }

    /**
     * 查询可以发布的优惠券
     */
    public function select_release_swap(Request $request, Coupon $coupon)
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
        //查询用户可以兑换的优惠券
        $res = $coupon
            ->select('coupon.id','coupon.start_at','coupon.end_at','coupon.note','coupon_category.coupon_name',
                'coupon_category.coupon_type','coupon_category.coupon_money','merchant.nickname')
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->leftjoin('merchant','merchant.id','=','coupon_category.merchant_id')
            ->where('member_id', $request->only('member_id'))->where('status', 1)
            ->where('end_at', '>', date('Y-m-d H:i:s', time()))
            ->groupBy('merchant_id')
            ->get();
        dump($res);
        if (empty($res[0])) {
            res(null, '用户没有可用的优惠券', 'success', 201);
        }

        foreach ($res as $k => $v) {
            //拼接
            $action = $v['coupon_type'] == 1 ? '元' : '折';//优惠券类型
            $arr['list'][] = [
                'coupon_id' => $v['id'],//该优惠券id
                'note' => $v['nickname'].$v['coupon_money'].$action."优惠券",//右侧描述:店名+优惠券面额+优惠券类型
            ];
        }
        res($arr);
    }

    /**
     * 发布互换
     */
    public function release_swap(Request $request, Swap $swap,Coupon $coupon)
    {
        $data = $request->only('member_id', 'coupon_id', 'integral');
        $role = [
            'member_id' => 'required',
            'coupon_id' => 'required|unique:swap,coupon_id',
            'integral' => 'required|integer|min:2',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'coupon_id.required' => '优惠券id不能为空！',
            'coupon_id.unique' => '每张优惠券只能使用一次兑换功能！',
            'integral.required' => '兑换所需的积分不能为空！',
            'integral.integer' => '兑换所需的积分须为整数！',
            'integral.min' => '最小兑换积分为2！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //验证是否存在该优惠券并且未使用且在有效期
        $cp_tf = $coupon->where('id',$data['coupon_id'])
            ->where('member_id',$data['member_id'])->where('status',1)
            ->where('end_at', '>', date('Y-m-d H:i:s', time()))->first();
        if(empty($cp_tf)){
            res(null, '优惠券失效或不存在', 'fail', 101);
        }
        //将其发布上去，并冻结优惠券status=4
        $re = $coupon->where('id',$data['coupon_id'])->update(['status'=>4]);
        $data['status'] = 2;
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
            'swap_id' => 'exists:swap,id',
            'member_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'swap_id.exists' => '您来玩一步啦！',
            'member_id.required' => '互换id不能为空！',
            'lat.required' => '纬度不能为空！',
            'lng.required' => '经度不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res = $swap->select('coupon_id', 'member_id')->find($data['swap_id']);
        #　判断当前优惠券是否是自己发布的，如果是则禁止互换，反之亦然
        $falg = ($data['member_id']  == $res['member_id']) ? 2 : 1;
        # 优惠券详情
        $res2 = $coupon
            ->select('coupon.content', 'merchant_id')
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->find($res['coupon_id']);
        # 用户是否关注了这个商家
        $res3 = $member->select('merchant_id')->find($data['member_id']);
        $is_collection = 2;//初始化没有收藏
        if (!empty($res3['merchant_id'])) {//不为空
            $merchant_id = json_decode($res3['merchant_id'], true);
            //查看当前商家在不在收藏中
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
        $data = $request->only('swap_id', 'member_id','api_token');
        $role = [
            'swap_id' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'swap_id.required' => '互换id不存在！',
            'member_id.required' => '用户id不存在！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }

        //验证当前用户
        $tf = $member->where('id',$data['member_id'])->where('api_token',$data['api_token'])->first();
        if(empty($tf)){
            res(null, '非法操作', 'fail', 101);
        }

        //查看此记录,是否处于未兑换,是否处于冻结期
        $res1 = $swap->with('coupon')->find($data['swap_id'])->toArray();
        if (empty($res1) || $res1['status'] != 2 || $res1['coupon']['status'] != 4 ) {
            res(null, '已被兑换或此券已过期', 'fail', 101);
        }
        //开始兑换:
        //1:要兑换人的积分是否足够
        $res2 = $member->select('integral')->find($data['member_id']);//兑换人的积分761
        if (empty($res2['integral']) || $res2['integral'] < $res1['integral']) { //如果用户没有积分,或者积分小于需要兑换的积分
            res(null, '积分不足', 'fail', 105);
        }
        /**
         * 积分足够,扣掉用户积分,并将优惠券的id改为该用户,将记录状态改为已兑换,将发布人的卷换分加入
         * $res1['member_id'] 优惠券原拥有者   $data['member_id'] 发起兑换者的id    $res1['coupon_id'] 优惠券id
         * $row1:扣掉用户积分
         * $row2:将优惠券的持有人改为当前用户,同时把优惠券状态修改
         * $row3:标记为已兑换
         * $row4:将用户的积分加上去,同时券换分也加上
         * $row5:删除原用户中拥有的优惠券id
         * $row6:在兑换者中加入该优惠券id
         * $data['status'] = 2;//'状态1:已被兑换,2:未兑换'
         */
        DB::beginTransaction();
        try{
            //扣掉兑奖的用户的积分
            $integral = $res2['integral'] - $res1['integral'];//减去积分
            $row1 = $member->where('id', $data['member_id'])->update(['integral' => $integral]);
            //将优惠券的持有人改为当前用户,同时把优惠券状态修改
            $row2 = $coupon->where('id', $res1['coupon_id'])->update(['member_id' => $data['member_id'],'status'=>1]);
            //将互换记录软删除
            $row3 = $swap->where('id', $data['swap_id'])->where('status',2)->update(['status' =>1,'exc_mem_id'=>$data['member_id'],'deleted_at'=> date('Y-m-d H:i:s', time())]);//1已经兑换
            //将用户的积分加上去,同时券换分也加上
            $res3 = $member->select('integral', 'integral_swap')->find($res1['member_id']);
            $integral = $res3['integral'] + $res1['integral'];
            $integral_swap = $res3['integral_swap'] + $res1['integral'];
            $row4 = $member->where('id', $res1['member_id'])->update(['integral' => $integral, 'integral_swap' => $integral_swap]);
            // 删除原用户中拥有的优惠券id
            $coupons1 = $member->select('coupon_id')->where('id', $res1['member_id'])->first();//member类型数据 $coupons1['coupon_id']为该coupon
            $coupons2 = delByValue($coupons1['coupon_id'],$res1['coupon_id']);//删除指定值（调用公共方法）
            $coupons3 = array_values($coupons2);//重新排序
            $coupons4 = json_encode($coupons3);
            $row5 = $member->select()->where('id', $res1['member_id'])->update(['coupon_id' => $coupons4]);
            // 在兑换者中加入该优惠券id
            $res = $member->select('coupon_id')->where('id', $data['member_id'])->first();
            if (empty($res['coupon_id'])) {
                //如果为空,则直接加入
                $coupon_id = [0 => $res1['coupon_id'] ] ;
                $coupon_id = json_encode($coupon_id);
            } else {
                //不为空,找出来,然后加入
                $arr = $res['coupon_id'];
                array_push($arr,  $res1['coupon_id']);
                $coupon_id = json_encode($arr);
            }
            $row6 = $member->select()->where('id', $data['member_id'])->update(['coupon_id' => $coupon_id]);
            if ($row1 && $row2 && $row3 && $row4 && $row5 && $row6) {
                DB::commit();
            }
        }catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();//事务回滚
            res(null, '兑换失败', 'fail', 100);
        }
        res(null, '兑换成功');

    }
}
