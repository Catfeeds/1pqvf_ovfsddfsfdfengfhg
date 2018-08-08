<?php

namespace App\Http\Controllers\Admin;

use App\Models\CnLatLngBag;
use App\Models\Coupon;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\couponcategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    /**
     * 优惠券展示页
     */
    public function index()
    {
        return view('admin.coupon.index');
    }

    /**
     * 优惠券新增页
     */
    public function create(Request $request, Merchant $merchant, CouponCategory $coupon_category)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();//商家信息
        $data['pictureInfo'] = $coupon_category->select('id', 'coupon_type','coupon_name','coupon_money', 'merchant_id', 'send_start_at','send_end_at')->get();//商家拥有的优惠券
        return view('admin.coupon.create', $data);
    }

    /**
     * 新增优惠券
     * type是否有效期（0有；）  cr_type生产优惠券方式（0区县范围；1定点周边；）
     */
    public function store(Request $request, Coupon $coupon, CouponCategory $coupon_category)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'msg' => '非法的请求类型'];
        }
        $data = $request->only('cp_cate_id','cr_type','address1','address2','start_at','end_at','note');
        $role = [
            'cp_cate_id' => 'exists:coupon_category,id',
            'cr_type' => 'required',
            'address1' => 'required_if:cr_type,2',
            'address2' => 'required_if:cr_type,1',
            'start_at' => 'required|date',
            'end_at' => 'required|after_or_equal:start_at',
            'note' => 'required',
        ];
        $message = [
            'cp_cate_id.exists' => '优惠券类别错误！',
            'cr_type.required' => '生成方式不能为空！',
            'address1.required_if' => '区/县地址不能为空',
            'address2.required_if' => '定点地址不能为空',
            'start_at.required'=>'开始时间不能为空',
            'start_at.date' => '开始时间类型错误！',
            'end_at.required'=>'结束时间不能为空',
            'end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'note.required' => '描述不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        # 查找该优惠券
        $coupon_cate = $coupon_category->select('send_start_at','send_end_at','send_num','picture_url','deduction_url')
            ->where('category_status',1)
            ->where('id',$data['cp_cate_id'])->first();
        # 优惠券效期strtotime($data['start_at'])时间戳
        $max_at = strtotime( $coupon_cate['send_end_at']);//最大效期
        $min_at = strtotime( $coupon_cate['send_start_at']);//最小效期
        if(is_in_range($min_at,strtotime($data['start_at']),$max_at) && is_in_range($min_at,strtotime($data['end_at']),$max_at)){
            $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));//开始时间
            $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));//结束时间
        }else{
            return ['status' => 'fail', 'msg' => '开始或者结束时间不正确'];
        }
        # 生成坐标
        if ($data['cr_type'] == 1) {//按商家,删除按区域的地址
            $type = 1;
            $data['address'] = $data['address2'];
        } else {
            $type = 2;
            $data['address'] = $data['address1'];
        }
        $latitude = get_latitude($type, $data['address'], 1);
        switch ($latitude) {
            case 2:
                return ['status' => 'fail', 'msg' => '该地址不存在或网络异常,请稍后再试'];
                break;
            case 3:
                return ['status' => 'fail', 'msg' => '该地区所标注为区县,请选择以区域生成类型'];
                break;
            case 4:
                return ['status' => 'fail', 'msg' => '该地址不存在'];
                break;
        }
        //如果唯一,则格式化
        $data['lat'] = $latitude[0]['lat'];
        $data['lng'] = $latitude[0]['lng'];
        unset($data['address1']);
        unset($data['address2']);
        //根据最后的经纬度,将所在地区重新调整
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        $url = "http://api.map.baidu.com/geocoder/v2/?location={$data['lat']},{$data['lng']}&output=json&pois=0&ak={$ak}";
        $addr = file_get_contents($url);
        $addr = json_decode($addr, true);
//        dump($addr['result']['addressComponent']['adcode']);die();
        $data['address'] = $addr['result']['addressComponent']['district'];//所属区域
        $data['adcode'] = $addr['result']['addressComponent']['adcode'];//百度所属代号
        $data['province'] = $addr['result']['addressComponent']['province'];
        $data['city'] = $addr['result']['addressComponent']['city'];
        # 生成唯一排序编码
        $data['uuid'] = create_unique_max_8bit_int(1)[0];//9位数，10亿量级
        # 生成优惠券编号  时间+排序编码
        $data['cp_number'] = date('Ymd').$data['adcode'].sprintf("%09d", $data['uuid'][0]);
        $res = $coupon->create($data);
        # 计入发行总量
        $coupon_category->where('category_status',1)->where('id',$data['cp_cate_id'])->update(['send_num'=>$coupon_cate['send_num']+1]);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    /**
     *  批量添加优惠券
     */
    public function creates(Request $request, Merchant $merchant, couponcategory $coupon_category , CnLatLngBag $cn_lat_lng_bag)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();//商家信息
        $data['pictureInfo'] = $coupon_category->select('id', 'coupon_type','coupon_name','coupon_money', 'merchant_id', 'send_start_at','send_end_at')->get();//商家拥有的优惠券
        $data['CnLatLngBagInfo'] = $cn_lat_lng_bag->where('city','深圳市')->count();//坐标总数
        return view('admin.coupon.creates', $data);
    }

    /**
     * 批量优惠券入库
     * type是否有效期（0有；）  cr_type生产优惠券方式（0区县范围；1定点周边；）
     */
    public function stores(Request $request, Coupon $coupon, CouponCategory $coupon_category,CnLatLngBag $cn_lat_lng_bag)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'msg' => '非法的请求类型'];
        }
        $data = $request->only('cp_cate_id','cr_type','address1','address2','start_at','end_at','note','create_num');

        $role = [
            'cp_cate_id' => 'exists:coupon_category,id',
            'cr_type' => 'required',
            'address1' => 'required_if:cr_type,2',
            'address2' => 'required_if:cr_type,1',
            'start_at' => 'required|date',
            'end_at' => 'required|after_or_equal:start_at',
            'note' => 'required',
            'create_num' => 'required|integer|between:1,10001',
        ];
        $message = [
            'cp_cate_id.exists' => '优惠券类别错误！',
            'cr_type.required' => '生成方式不能为空！',
            'address1.required_if' => '区/县地址不能为空',
            'address2.required_if' => '定点地址不能为空',
            'start_at.required'=>'开始时间不能为空',
            'start_at.date' => '开始时间类型错误！',
            'end_at.required'=>'结束时间不能为空',
            'end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'note.required' => '描述不能为空！',
            'create_num.required' => '生成数量必填',
            'create_num.integer' => '生成数量必须是整数',
            'create_num.between' => '一次最多生成1万条数据',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        # 查找该优惠券
        $coupon_cate = $coupon_category->select('send_start_at','send_end_at','send_num','picture_url','deduction_url')
            ->where('category_status',1)
            ->where('id',$data['cp_cate_id'])->first();
        # 优惠券效期strtotime($data['start_at'])时间戳
        $max_at = strtotime( $coupon_cate['send_end_at']);//最大效期
        $min_at = strtotime( $coupon_cate['send_start_at']);//最小效期
        if(is_in_range($min_at,strtotime($data['start_at']),$max_at) && is_in_range($min_at,strtotime($data['end_at']),$max_at)){
            $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));//开始时间
            $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));//结束时间
        }else{
            return ['status' => 'fail', 'msg' => '开始或者结束时间不正确'];
        }

        # 批量生成坐标及唯一编码 获取方式：调用方法还是取数据库
        if($data['cr_type'] == 2) {//按市级 —— 取数据库
            //判断坐标仓库是否足够，取出对应的坐标集合，并删除（避免重用的多次）
            $num_loc =  $cn_lat_lng_bag->count();//仓库现有的坐标数量
            if ($data['create_num']>$num_loc){
                return ['status' => 'fail', 'msg' => '坐标池数量不足，请先生成足量的坐标'];
            }
            //取数据
            $latitude = $cn_lat_lng_bag->select('lat','lng','uuid','district','adcode','district','province','city')
//                ->where('city','深圳市')
                ->limit($data['create_num'])
                ->get();
        }else{//定点或按区县 —— 调用方法
            $ads = $data['cr_type'] == 0 ? ['type' => 1,'adr' => $data['address2']] : ['type' => 2,'adr' => $data['address1']];
            $lat_lng = get_latitude($ads['type'], $ads['adr'], $data['create_num']);//调用方法生成坐标
            switch ($lat_lng) {
                case 2:
                    return ['status' => 'fail', 'msg' => '该地址不存在或网络异常,请稍后再试'];
                    break;
                case 3:
                    return ['status' => 'fail', 'msg' => '该地区所标注为区县,请选择以区域生成类型'];
                    break;
                case 4:
                    return ['status' => 'fail', 'msg' => '该地址不存在'];
                    break;
            }
            # 生成$create_num个优惠券编号
            $arr_uuid = create_unique_max_8bit_int($data['create_num']);//唯一编码数组
            $latitude = [];
            $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
            foreach ($lat_lng as $k => $v){
                $arr_formatted = get_address_component($ak,$v['lng'],$v['lat']);

                $latitude[] = array_merge(
                    $v,
                    ['uuid' => $arr_uuid[$k]],
//                    ['formatted_address' => $arr_formatted['result']['formatted_address']],//详细地址
                    ['district' => $arr_formatted['result']['addressComponent']['district']],
                    ['adcode' => $arr_formatted['result']['addressComponent']['adcode']],
                    ['province' => $arr_formatted['result']['addressComponent']['province']],
                    ['city' => $arr_formatted['result']['addressComponent']['city']]
                );
            }

        }
        unset($data['address1']);
        unset($data['address2']);
        DB::beginTransaction();
        try{
            foreach ($latitude as $k => $v){
                $arr_tf[] = $coupon->create(array_merge(
                    $v,
                    ['cp_number' => date('Ymd').$v['adcode'].sprintf("%09d", $v['uuid']) ],//生成优惠券编号  时间+排序编码
                    ['start_at' =>  date('Y-m-d H:i:s', strtotime($data['start_at']))],
                    ['end_at' => date('Y-m-d H:i:s', strtotime($data['end_at']))],
                    ['cp_cate_id' => $data['cp_cate_id']],
                    ['note' => $data['note']]
                ));
            }
            $cn_lat_lng_bag->limit($data['create_num'])->delete();//删除掉数据库取出来的数据，避免多次使用
            $coupon_category->where('category_status',1)->where('id',$data['cp_cate_id'])->update(['send_num'=>$coupon_cate['send_num']+$data['create_num']]);# 计入发行总量
            DB::commit();
        }catch(\Illuminate\Database\QueryException $ex){
            DB::rollback();//事务回滚
            return ['status' => 'fail', 'msg' => '删除失败'];
        }
        return ['status' => 'success', 'msg' => '删除成功'];
    }

    /**
     *优惠券首页ajax数据传送
     */
    public function ajax_list(Request $request, Coupon $coupon,Merchant $merchant)
    {
        if ($request->ajax()) {
            //分页
            $page = $request->get('start') / $request->get('length') + 1;//页码
            $search = $request->input('search');//like
            $data = $coupon
                ->with(['coupon_category' => function ($query) {
                    $query->leftjoin('merchant','merchant.id','=','coupon_category.merchant_id')->select('coupon_category.id', 'coupon_category.picture_url','coupon_category.merchant_id','coupon_category.coupon_name','coupon_category.coupon_type','coupon_category.coupon_money','coupon_category.spend_money','merchant.nickname AS merchant_name');
                }])
                ->with(['member' => function ($query) {
                    $query->select('id', 'nickname');
                }])
                ->when($search, function ($query) use($search , $coupon) {
                    $coupon->whereRaw("concat(note,coupon_money,district) like '%{$search['value']}%'");
                })
                ->select('coupon.id','cp_cate_id','start_at','end_at','uuid','status','content','create_at','member_id','lng','lat','note','district','cp_number','adcode')
                ->paginate($request->get('length'), null, null, $page);
            $data = obj_arr($data);
            $cnt = $data['total'];//总记录
            $info = [
                'draw' => $request->get('draw'),
                'recordsTotal' => $cnt,
                'recordsFiltered' => $cnt,
                'data' => $data['data'],
            ];
            return $info;
        }
    }

    /**
     * 优惠券编辑页
     */
    public function edit(Coupon $coupon, Merchant $merchant, CouponCategory $coupon_category)
    {
        $data['couponInfo'] = $coupon;//当前优惠券信息
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();//商家信息
        $data['pictureInfo'] = $coupon_category->select('id', 'coupon_type','coupon_name','coupon_money', 'merchant_id', 'send_start_at','send_end_at')->get();//商家拥有的优惠券
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        return view('admin.coupon.edit', $data);
    }

    /**
     * 更新优惠券
     * type是否有效期（0有；）  cr_type生产优惠券方式（0区县范围；1定点周边；）
     */
    public function update(Request $request, Coupon $coupon,CouponCategory $coupon_category)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'msg' => '非法的请求类型'];
        }
        $data = $request->only('cp_cate_id','start_at','end_at','note');

        $role = [
            'cp_cate_id' => 'nullable|exists:coupon_category,id',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|after_or_equal:start_at',
            'note' => 'nullable|string|between:3,20'
        ];
        $message = [
            'cp_cate_id.exists' => '优惠券类别错误！',
            'start_at.date' => '开始时间类型错误！',
            'end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'note.string' => '非法输入',
            'note.between' => '输入的字节数为3到20位'
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        # 查找该优惠券
        $coupon_category = $coupon_category->select('send_start_at','send_end_at','send_num','picture_url','deduction_url')
            ->where('category_status',1)
            ->where('id',$data['cp_cate_id'])->first();
        # 优惠券效期
        $max_at = strtotime( $coupon_category['send_end_at']);//最大效期
        $min_at = strtotime( $coupon_category['send_start_at']);//最小效期
        if( !empty($data['start_at']) ){
            if((!is_in_range($min_at,strtotime($data['start_at']),$max_at))){
                return ['status' => 'fail', 'msg' => '开始时间不正确'];
            }
        }
        if( !empty($data['end_at']) ){
            if(!is_in_range($min_at,strtotime($data['end_at']),$max_at)){
                return ['status' => 'fail', 'msg' => '结束时间不正确'];
            }

        }
        $data = array_filter($data);//为空的是不更新的部分
        // 更新数据
        $res = $coupon->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    /**
     * 删除单一优惠券
     * 已领取且未过期
     */
    public function destroy($id)
    {
        $coupon = new Coupon();
        $coupon = $coupon->find($id);
        if(!empty($coupon['member_id']) || $coupon['status']==1){
            return ['status' => 'fail', 'error' => '用户持有该优惠券，不能删除！'];
        }else{
            $res = $coupon->delete();
            if ($res) {
                return ['status' => 'success'];
            } else {
                return ['status' => 'fail', 'error' => '删除失败！'];
            }
        }
    }

    /**
     * 查看周边优惠券请求接口
     */
    public function select_coupon(Request $request, Coupon $coupon, Merchant $merchant)
    {
        $role = [
            'lat' => 'required',
            'lng' => 'required',
            'member_id' => 'exists:member,id',
        ];
        $message = [
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
            'member_id.exists' => '用户id不合法！',
        ];
        $data = $request->only('lat', 'lng', 'member_id');
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询当前所在区县
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        $url = "http://api.map.baidu.com/geocoder/v2/?location={$data['lat']},{$data['lng']}&output=json&pois=0&ak={$ak}";
        $addr = file_get_contents($url);
        $addr = json_decode($addr, true);
        $adcode = $addr['result']['addressComponent']['adcode'];
        $district = $addr['result']['addressComponent']['district'];
        //获取当前区县的所有优惠券
        $res = $coupon
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->leftjoin('merchant','merchant.id','=','coupon_category.merchant_id')
            ->select('coupon.id AS coupon_id','coupon.cp_cate_id','coupon.lat','coupon.lng','coupon.note','coupon.start_at','coupon.end_at','coupon.cp_number', 'coupon_category.picture_url','coupon_category.merchant_id', 'merchant.nickname AS merchant_name','merchant.avatar AS merchant_logo','merchant.appraise_n','merchant.img_url AS merchant_background_img','coupon_category.coupon_name','coupon_category.coupon_explain','coupon_category.coupon_type','coupon_category.coupon_money','coupon_category.spend_money','coupon.status')
            ->where('adcode', $adcode)//当前区/县内
            ->where('status',0)//已生效未领取的优惠券
            ->whereNull('member_id')//排除已领取
            ->groupBy('coupon.cp_cate_id')//排除相同优惠券
            ->orderBy('uuid')->get();
        if (empty($res[0])) {
            res(null, '该地区[' . $district . ']周围没有优惠券,去其他地方看看吧', 'success', 201);
        }
//        dump(obj_arr($res));
        $distance = 500;//要找多少米内的
        #找出距离满足且在有效期内的有效数据
        $arr = [];
        foreach ($res as $k => $v) {
            $juli = GetDistance2($data['lat'], $data['lng'], $v['lat'], $v['lng']);
            if ($juli <= $distance && is_in_range(strtotime($v['start_at']),time(),strtotime( $v['end_at']))) {//如果距离满足且在有效期内
                $arr[$k] = $v;
                $arr[$k]['picture_url'] = config('app.url').$v['picture_url'];
                $arr[$k]['merchant_logo'] = config('app.url').$v['merchant_logo'];
                $arr[$k]['merchant_background_img'] = config('app.url').$v['merchant_background_img'];
                $arr[$k]['coupon_explain'] = json_decode($v['coupon_explain'],true);
            }
        }
//        dump( obj_arr($arr) );die();
        if (empty($arr)) {
            res(null, '周围' . $distance . '米内没有优惠券', 'success', 201);
        }
        res($arr);
    }

    /**
     * 查看优惠券详情页面
     */
    public function show_coupon(Request $request, Member $member, Coupon $coupon, Merchant $merchant)
    {
        $data = $request->only('coupon_id', 'lat', 'lng', 'member_id');
        $role = [
            'lat' => 'required',
            'coupon_id' => 'exists:coupon,id',
            'lng' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'lat.required' => '纬度不能为空！',
            'coupon_id.exists' => '优惠券id非法！',
            'lng.required' => '经度不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 根据优惠券的id,查找优惠券的详情
        $res1 = $coupon
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->leftjoin('merchant','merchant.id','=','coupon_category.merchant_id')
            ->select('coupon.id AS coupon_id','coupon.cp_cate_id', 'coupon_category.picture_url','coupon_category.merchant_id','coupon_category.coupon_name','coupon_category.coupon_type','coupon_category.coupon_money','coupon_category.spend_money','coupon_category.coupon_explain','coupon.cp_cate_id', 'coupon.note AS coupon_note','coupon.start_at','coupon.end_at','coupon.cp_number','merchant.nickname AS merchant_name','merchant.address AS merchant_address','merchant.latitude AS merchant_local','merchant.note AS merchant_note')
            ->find($data['coupon_id']);
        # 判断该商家是否已经收藏
        $res2 = $member->select('merchant_id')->find($data['member_id']);
        $res1['is_collection'] = 1;//初始化为可以收藏
        if (!empty($res2['merchant_id'])) {//商家不为空就去判断
            $res1['is_collection'] = in_array($res1['coupon_category']['merchant_id'], json_decode($res2['merchant_id'], true)) ? 0 : 1;
        }
        # 商家距离
        $merchant_local = json_decode($res1['merchant_local'],true);
        $latitude = bd_encrypt($data['lat'], $data['lng']);
        $res1['distance'] = getDistance($latitude['lat'], $latitude['lng'], $merchant_local['lat'], $merchant_local['lng']);

        $res1['member_id'] = $data['member_id'];
//        $content = json_decode($res1['note'], true);
        # 数据调整
        $res1['picture_url'] = config('app.url').$res1['picture_url'];
        $res1['coupon_explain'] = json_decode($res1['coupon_explain'],true);
        $res1['coupon_note'] = json_decode($res1['coupon_note'],true);
        $res1['merchant_note'] = json_decode($res1['merchant_note'],true);

        res($res1);
    }
    /**
     * 领取优惠券
     */
    public function get_coupon(Request $request, Coupon $coupon)
    {
        $data = $request->only('member_id', 'coupon_id','lat','lng');
        $role = [
            'member_id' => 'exists:member,id',
            'coupon_id' => 'exists:coupon,id',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'member_id.exists' => '用户id不合法！',
            'coupon_id.exists' => '优惠券不合法！',
            'lat.required' => '纬度不能为空！',
            'lng.required' => '经都不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 先判断这张券是否有被人领取及有没有在效期内
        $res = $coupon->select('member_id', 'start_at', 'end_at')->where('status',0)->find($data['coupon_id']);
        if (empty($res['member_id']) && is_in_range(strtotime($res['start_at']),time(),strtotime($res['end_at'])) ) {//没人领取且在有效期内
            // 调整效期
            $start_at = date('Y-m-d H:i:s');//开始时间为当前时间
            $end_at = (strtotime($start_at . '+1month')>strtotime($res['end_at'])) ? $res['end_at'] : date('Y-m-d H:i:s',strtotime($start_at . '+1month')) ;//最长效期为1个月
            // 更新优惠券表
            $res1 = $coupon->where('id', $data['coupon_id'])
                ->update([
                    'member_id' => $data['member_id'] ,
                    'start_at' => $start_at,
                    'end_at' => $end_at,
                    'create_at' => $start_at,
                    'status' => 1,
                    'rewarded_lng' => $data['lng'],
                    'rewarded_lat' => $data['lat'],
                ]);
            if ($res1) {
                res(null, '成功');
            }else{
                res(null, '失败', 'fail', 100);
            }
//            DB::transaction(function () use ($coupon, $member, $data, $start_at,$end_at) {
            //优惠券表更新 'status' => 1已领取但未使用
//            $res1 = $coupon->where('id', $data['coupon_id'])
//                ->update([
//                    'member_id' => $data['member_id'] ,
//                    'start_at' => $start_at,
//                    'end_at' => $end_at,
//                    'create_at' => $start_at,
//                    'status' => 1,
//                    'rewarded_lng' => $data['lng'],
//                    'rewarded_lat' => $data['lat'],
//                ]);
//                //用户表入库
//                $res = $member->select('coupon_id')->where('id', $data['member_id'])->first();
//                if (empty($res['coupon_id'])) {
//                    //如果为空,则直接加入
//                    $coupon_id = json_encode( [0 => $data['coupon_id']] );
//                } else {
//                    //不为空,找出来,然后加入
//                    $arr = $res['coupon_id'];
//                    array_push($arr, $data['coupon_id']);
//                    $coupon_id = json_encode($arr);
//                }
//                $res2 = $member->select()->where('id', $data['member_id'])->update(['coupon_id' => $coupon_id]);
//                if ($res1 && $res2) {
//                    DB::commit();
//                    res(null, '成功');
//                }
//                DB::rollback();//事务回滚
//                res(null, '失败', 'fail', 100);
//            });
        }
        res(null, '有缘无分，您来晚一步啦！', 'fail', 105);
    }

    /**
     * 使用优惠券
     * status=1才能使用  使用时修改status=2
     */
    public function consume(Request $request, Coupon $coupon)
    {
        $data = $request->only('coupon_id', 'member_id');
        $role = [
            'coupon_id' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'coupon_id.required' => '优惠券不能为空！',
            'member_id.required' => '用户不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 验证优惠券是否存在且用户是否拥有此优惠券
        $res = $coupon->select('id','cp_cate_id', 'status', 'start_at', 'end_at')
            ->where('id', $data['coupon_id'])
            ->where('member_id', $data['member_id'])
            ->where('status', 1)//已领取未使用
            ->limit(1)->first();

        if (empty($res['id'])) {
            res(null, '非法操作,优惠券与用户不匹配', 'fail', 106);
        }
        # 在效期内
        if( is_in_range(strtotime($res['start_at']),time(),strtotime($res['end_at'])) )
        {
            $res2 = $coupon->where('id', $data['coupon_id'])->update(['status' => 2]);
            if ($res2) {
                $data = ['code' => $res['cp_cate_id']];
                res($data, '成功');
            }else{
                res(null, '网络故障，请联系管理员', 'fail', 100);
            }
        }else{
            $coupon->where('id', $data['coupon_id'])->update(['status' => 3]);//过期啦
            res(null, '优惠券不在使用期限内', 'fail', 104);
        }
    }

    /*
     * 查看我的优惠券默认项————未使用
     * status===1未过期
     *  $coupon_ids['coupon_id']用户的所有优惠券id
     */
    public function available(Request $request, Coupon $coupon, CouponCategory $coupon_category)
    {
        //返回一张图片,有效期,id
        $data = $request->only('member_id');
        $role = [
            'member_id' => 'exists:coupon,member_id',
        ];
        $message = [
            'member_id.exists' => '用户还没有获得优惠券！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }

        # 先找出用户拥有的未使用的优惠券
        $res1 = $coupon
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->select('coupon.id', 'coupon.cp_cate_id', 'coupon.start_at', 'coupon.end_at','coupon_category.coupon_name','coupon_category.coupon_explain','coupon_category.coupon_type','coupon_category.coupon_money','coupon_category.spend_money','coupon_category.picture_url')
            ->where('member_id',$data['member_id'])
            ->where('status', 1)->get();//未使用（有可能过期）


        //如果空表示没有未使用优惠券
        if ($res1 == null) {
            res(null, '用户没有未使用的优惠券', 'success', 201);
        }

        $res2 = [];//接取有效期未使用的优惠券
        //如果有先判断并更新状态
        foreach ($res1 as $k => $v) {//领取的时候已经判断了最小时间，这里只需要判断最晚效期
            if (time() >= strtotime($v['end_at'])) {//已过期,更新状态'status' => 3过期
                $coupon->where('id', $v['id'])->update(['status' => 3]);
                continue;//不在效期不记录，跳出
            }else{
                $res2[] = $v;
            }
        }
        if($res2 == null){
            res(null, '用户没有未使用的优惠券', 'success', 201);
        }
        foreach ($res2 as $k => $v){
            $res2[$k]['picture_url'] = config('app.url').$v['picture_url'];
        }
        res($res2);
    }

    /*
     * 查看我的优惠券(已过期)
     * status===3已过期
     */
    public function expired(Request $request, Coupon $coupon)
    {
        //返回一张图片,有效期,id
        $data = $request->only('member_id');
        $role = [
            'member_id' => 'exists:coupon,member_id',
        ];
        $message = [
            'member_id.exists' => '用户还没有获得优惠券！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 查询status===1中有没有过期的，并修改status
        $coupon_ids = $coupon->select('id','start_at', 'end_at')->where('member_id', $data['member_id'])->where('status',1)->get();//查询拥有的未使用的优惠券（有可能过期了）
//        dump($coupon_ids);die();
        if(!empty($coupon_ids)){
            foreach ($coupon_ids as $k => $v) {//领取的时候已经判断了最小时间，这里只需要判断最晚效期
                if (time() >= strtotime($v['end_at'])) {//已过期,更新状态'status' => 3过期
                    $coupon->where('id', $v['id'])->update(['status' => 3]);
                }
            }
        }
        // 查找所有优惠券过期status==3的优惠券
        $res2 = $coupon
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->select('coupon.id', 'coupon.cp_cate_id', 'coupon.start_at', 'coupon.end_at','coupon_category.coupon_name','coupon_category.coupon_explain','coupon_category.coupon_type','coupon_category.coupon_money','coupon_category.spend_money','coupon_category.picture_url')
            ->where('member_id',$data['member_id'])
            ->where('status', 3)->get();
        if(empty($res2)){
            res(null, '用户没有已过期的优惠券', 'success', 201);
        }
        foreach ($res2 as $k => $v){
            $res2[$k]['picture_url'] = config('app.url').$v['picture_url'];
        }
        res($res2);
    }

    /**
     * 查看我的优惠券(已使用)
     * status==2
     */
    public function has_been_used(Request $request, Coupon $coupon, CouponCategory $coupon_category)
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

        //用户已使用的优惠券
        $res = $coupon
            ->leftJoin('coupon_category','coupon_category.id','=','coupon.cp_cate_id')
            ->select('coupon.id', 'coupon.cp_cate_id', 'coupon.start_at', 'coupon.end_at','coupon.updated_at','coupon_category.coupon_name','coupon_category.coupon_explain','coupon_category.coupon_type','coupon_category.coupon_money','coupon_category.spend_money','coupon_category.picture_url')
            ->where('member_id',$data['member_id'])
            ->where('status', 2)->get();
        if (empty($res[0])) {
            res(null, '用户没有已使用的优惠券了', 'success', 201);
        }
        foreach ($res as $k => $v) {
            $res[$k]['picture_url'] = config('app.url').$v['picture_url'];
        }
        res($res);
    }



    /**
     * 生成3500个坐标并入库
     * @param CnLatLngBag $cn_lat_lng_bag
     * @return array
     */
    public function stores_location(Request $request,CnLatLngBag $cn_lat_lng_bag){
        $cn_lat_lng_bag->creates_sz_loc(10);//生成3500条坐标
//        $cn_lat_lng_bag->creates_sz_loc(1);//测试用
        return ['status' => 'success', 'msg' => '新增成功'];
    }

}
