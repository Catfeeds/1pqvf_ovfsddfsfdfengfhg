<?php

namespace App\Http\Controllers\Api;

use App\Models\ActZongUser;
use App\Models\ActOneFlag;
use App\Models\Member;
use App\Models\TheDelivery;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\DB;

class ActOneZongController extends Controller
{
    /**
     * 查找所有粽子
     * act_id = 5
     */
    public function slc_zong(Request $request,ActOneFlag $act_one_flag)
    {
        $data = $request->only('act_mem_id','lat', 'lng');
        $role = [
            'act_mem_id' => 'exists:member,id',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'act_mem_id.exists' => '请求不合法',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 当前高德坐标转百度
        $adr_ll = bd_encrypt( $data['lat'], $data['lng'] );
        $lat = $adr_ll['lat'];
        $lng = $adr_ll['lng'];
        # 求出最大最小的经纬度
        $distance = 2.5;//多少米范围内
        $LatLng_arr = MaxMin_to_LatLng($lng, $lat,$distance);
        # 查找当前6千米范围内的所有粽子
        $lng_lat = $act_one_flag->select('id','lng','lat','flag_name','content','flag_lv','pic_id')
            ->where('lat' , '>' , $LatLng_arr['min_lat'])
            ->where('lat' , '<' , $LatLng_arr['max_lat'])
            ->where('lng' , '>' , $LatLng_arr['min_lng'])
            ->where('lng' , '<' , $LatLng_arr['max_lng'])
            ->limit('18')
            ->get();
        $lng_lat = obj_arr($lng_lat);
        if (empty($lng_lat)) {
            res(null, '周围' . $distance . '米内没有粽子', 'success', 201);
        }else{//取18条数据
            res($lng_lat);
        }
        # 随机获取周边18个粽子
        // if (empty($lng_lat)) {
        //     res(null, '周围' . $distance . '米内没有粽子', 'success', 201);
        // }else{//取18条数据
        // 	if(count($lng_lat) <= 18){
        // 		res($lng_lat);
        // 	}else{
        // 		$key = array_rand($lng_lat,18);
        // 		$new_ll = [];
        // 		foreach ($key as $k => $v) {
        // 			$new_ll[] = $lng_lat[$v];
        // 		}
        // 		res($new_ll);
        // 	};
        // }
    }
    /**
     * 点击旗子查看粽子详情详情
     */
    public function cl_zong(Request $request,ActZongUser $act_zong_user,ActOneFlag $act_one_flag){
        $data = $request->only('act_mem_id','lat', 'lng','flag_id');
        $role = [
            'act_mem_id' => 'exists:member,id',
            'lat' => 'required',
            'lng' => 'required',
            'flag_id' => 'exists:act_one_flag,id',
        ];
        $message = [
            'act_mem_id.exists' => '用户请求不合法',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
            'flag_id.exists' => '操作不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询该粽子详情
        $flg_info = $act_one_flag->select('id','lng','lat','flag_name','content','flag_lv','pic_id')
            ->where('flag_id',$data['flag_id'])->get();
        res($flg_info);
    }

    /**
     * 获取粽子到数据库
     * 自动报名
     * 前端判断距离，大于约定距离，不弹出
     */
    public function lt_zong(Request $request,ActZongUser $act_zong_user,ActOneFlag $actOneFlag){
        $data = $request->only('act_mem_id','lat', 'lng','flag_id');
        $role = [
            'act_mem_id' => 'exists:member,id',
            'lat' => 'required',
            'lng' => 'required',
            'flag_id' => 'exists:act_one_flag,id',
        ];
        $message = [
            'act_mem_id.exists' => '请求不合法',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
            'flag_id.exists' => '操作不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 获取该粽子的类型$flag_lv['flag_lv']
        $flag_lv = $actOneFlag->select('flag_lv')->where('id',$data['flag_id'])->first();
        $all_lv = ['1','2','3','4','5','6'];//粽子的种类
        if(!in_array($flag_lv['flag_lv'],$all_lv)){
            res(null, '失败', 'fail', 100);
        }
        # 获取该用户拥有的旗子的一维数组
        $check_mem = $act_zong_user->select('id','has_flag')->where('member_id',$data['act_mem_id'])->first();
        //如果该用户没有参与,需要给他报名dump(empty($check_mem['id']));exit();
        if(empty($check_mem['id'])){
            $act_zong_user->create(['member_id'=>$data['act_mem_id']]);
        }
        # 获取该旗子
        $flg_ids = $act_zong_user->check_has_flg($data['act_mem_id']);
        //存粽子类型到该用户账号下
        if(empty($flg_ids)){//如果为空直接加入
            $arr[] = $flag_lv['flag_lv'];
        }else{//不为空
            array_push($flg_ids, $flag_lv['flag_lv']);
            $arr = $flg_ids;
        }
        $arr = json_encode($arr);
        $res = $act_zong_user->where('member_id', $data['act_mem_id'])->update(['has_flag' => $arr]);
        # 标准粽子已被领取 'status'=>0 'member_id'=>$data['act_mem_id'] delete
        $res2 = $actOneFlag->where('id',$data['flag_id'])->update(['status'=>0,'member_id'=>$data['act_mem_id']]);//记录
        $res3 = $actOneFlag->where('id',$data['flag_id'])->delete();//删除
        if ($res) {
            res(null, '恭喜，获得成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 查询获取的粽子情况
     * @param Request $request
     * 返回地点状态
     */
    public function has_zong(Request $request,ActZongUser $act_zong_user){
        $data = $request->only('act_mem_id','lat', 'lng');
        $role = [
            'act_mem_id' => 'exists:member,id',
        ];
        $message = [
            'act_mem_id.exists' => '请求不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 查询已获取的粽子类型的一维数组,去除空值
        $flg_lvs = $act_zong_user->check_has_flg($data['act_mem_id']);
        $flg_lvs = empty($flg_lvs) ? null :  array_filter($flg_lvs);//,去除空值，拥有的粽子列表
        $count_lv = empty($flg_lvs) ? [] : array_count_values($flg_lvs);//总计每种粽子拥有的粽子
        $reset_lvs =[//初始化粽子个数
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
        ];
        $has_lvs = $count_lv + $reset_lvs;//叠加
        //转化为前端需要的数据类型
        if(!ksort($has_lvs)){
            res(null, '失败', 'fail', 100);
        }
        res($has_lvs);
    }
    public function has_reward(Request $request,ActZongUser $act_zong_user)
    {
        $data = $request->only('act_mem_id');
        $role = [
            'act_mem_id' => 'exists:member,id',
        ];
        $message = [
            'act_mem_id.exists' => '请求不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $has_reward = $act_zong_user->select('has_reward')->where('member_id',$data['act_mem_id'])->first();
        $has_reward = empty($has_reward['has_reward']) ? null : json_decode($has_reward['has_reward'],true);
        res($has_reward);
    }

    /**
     * 点击领取奖励
     * @param Request $request
     * 返回地点状态
     */
    public function reward_zon(Request $request,ActZongUser $act_zong_user,Member $member,TheDelivery $theDelivery){
        $data = $request->only('act_mem_id','reward_id');
        $role = [
            'act_mem_id' => 'exists:member,id',
            'reward_id' => 'integer',
        ];
        $message = [
            'act_mem_id.exists' => '请求不合法',
            'reward_id.integer' => '请求不合法2',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 查询已获取的粽子及已领取的奖项
        $flg_lvs =$act_zong_user->check_has_flg($data['act_mem_id']);
        $flg_lvs = empty($flg_lvs) ? null :  array_filter($flg_lvs);//拥有的粽子列表
        $count_lvs = empty($flg_lvs) ? null : array_count_values($flg_lvs);//总计每种粽子拥有的粽子 含特殊粽子6
        unset($count_lvs['6']);//除去特殊粽子6以外 总计每种粽子拥有的粽子
        $count_lv = empty($count_lvs) ? 0 : count($count_lvs);//除去特殊粽子6以外,拥有粽子的种类
        # 查询用户兑奖记录has_reward
        $has_reward = $act_zong_user->select('has_reward')->where('member_id',$data['act_mem_id'])->first();
        $has_reward = empty($has_reward['has_reward']) ? null : json_decode($has_reward['has_reward'],true);
        if($data['reward_id'] == 1) {//集齐4种粽子的活动
            $tf = empty($has_reward) ? true : (! in_array($data['reward_id'],$has_reward));//为true没有参与
            if($count_lv == 4 && $tf ){//条件达成，且没有兑换记录
                //每种粽子减一个元素    并记录兑奖信息
                $sub_coll = [1,2,3,4,5];//要删除的值，其实只能减掉其中4个元素
                //每个指定元素删除一个
                $new_has_flag = json_encode(array_values(arr_dlt_val_once($flg_lvs,$sub_coll)));//每个种类各删除一个粽子 重新排序 转json
                //添加兑奖记录
                $new_has_reward = empty($has_reward) ? json_encode([0 => $data['reward_id']]) : json_encode(array_merge($has_reward,[$data['reward_id']]));
                DB::transaction(function () use ($act_zong_user, $new_has_flag,$new_has_reward,$data, $member) {
                    # 更新领奖记录到数据库
                    $res1 = $act_zong_user->where('member_id',$data['act_mem_id'])->update([
                        'has_flag' => $new_has_flag,
                        'has_reward'=> $new_has_reward,
                    ]);
                    # 发放奖励200积分
                    $integral = $member->select('integral')->where('id',$data['act_mem_id'])->first(); //查看我的积分
                    $integral = empty($integral['integral']) ? 0 : $integral['integral'];
                    $new_igr = intval($integral + 200) ;
                    $res2 = $member->where('id',$data['act_mem_id'])->update(['integral'=>$new_igr]);
                    if ($res1 && $res2 ){
                        DB::commit();
                        res(null, '恭喜，获得奖励');
                    }else{
                        DB::rollback();//事务回滚
                        res(null, '兑换失败，请联系客服', 'fail', 100);
                    }
                });
            }else{
                res(null, '已兑换或未达成', 'fail', 105);//条件不符合105
            }
        }elseif ($data['reward_id'] == 2){//集齐五种粽子的活动 礼品act_mall_id=4
            $tf = empty($has_reward) ? true : (! in_array($data['reward_id'],$has_reward));//为true没有参与
            if($count_lv == 5 && $tf ){//条件达成，且没有兑换记录
                //查看该用户是否完善了个人地址 没有地址的话直接跳出
                $adr = $member->select('address','tesco')->find($data['act_mem_id']);
                $m_tesco = json_decode($adr['tesco'],true) ;
                if (empty($adr['address'])) {
                    res(null, '收货地址信息不完善，请准确填写后再提交', 'fail', 105);
                }
                //每种粽子减一个元素    并记录兑奖信息
                $sub_coll = [1,2,3,4,5];//要删除的值
                //每个指定元素删除一个
                $new_has_flag = json_encode(array_values(arr_dlt_val_once($flg_lvs,$sub_coll)));//每个种类各删除一个粽子 重新排序 转json
                //添加兑奖记录
                $new_has_reward = empty($has_reward) ? json_encode([0 => $data['reward_id']]) : json_encode(array_merge($has_reward,[$data['reward_id']]));
                DB::transaction(function () use ($act_zong_user, $new_has_flag,$new_has_reward,$data, $member,$theDelivery,$m_tesco) {
                    # 更新领奖记录到数据库
                    $res1 = $act_zong_user->where('member_id',$data['act_mem_id'])->update([
                        'has_flag' => $new_has_flag,
                        'has_reward'=> $new_has_reward,
                    ]);
                    # 兑换五芳斋粽子 1.member中记录兑奖信息；2.兑换成功,将信息加入发货表
//                    $new_tesco = empty($m_tesco) ? json_encode(['4']) : json_encode(array_push($m_tesco,['4']));
//                    $res2 = $member->where('id',$data['act_mem_id'])->update(['tesco' => $new_tesco]);
                    $res3 = $theDelivery->create(['member_id'=> $data['act_mem_id'],'intmall_id' =>'4','Order'=>'端午活动小米手环intmall_id = actmall_id']);
                    if ($res1 && $res3 ){
                        DB::commit();
                        res(null, '恭喜，获得奖励，请您在用户信息中核对您的收货地址');
                    }else{
                        DB::rollback();//事务回滚
                        res(null, '兑换失败，请联系客服', 'fail', 100);
                    }
                });
            }else{
                res(null, '已兑换或未达成', 'fail', 105);//条件不符合105
            }
        }elseif ($data['reward_id'] == 3){//送粽鸡的活动 兑换小米手环id=2
            $tf = empty($has_reward) ? true : (! in_array($data['reward_id'],$has_reward));//为true没有参与
            $contains_tf =  empty($flg_lvs) ? false :  in_array('6',$flg_lvs);//是否领取了6号粽子
            if( $contains_tf && $tf ){//条件达成，且没有兑换记录
                //查看该用户是否完善了个人地址 没有地址的话直接跳出
                $adr = $member->select('address','tesco')->find($data['act_mem_id']);
                $m_tesco = json_decode($adr['tesco'],true) ;
                if (empty($adr['address'])) {
                    res(null, '收货地址信息不完善，请准确填写后再提交', 'fail', 105);
                }
                //删除一个6号粽子 并记录兑奖信息
                unset($flg_lvs[array_search('6',$flg_lvs)]);
                $new_has_flag = json_encode(array_values($flg_lvs));//每个种类各删除一个粽子 重新排序 转json
                //添加兑奖记录
                $new_has_reward = empty($has_reward) ? json_encode([0 => $data['reward_id']]) : json_encode(array_merge($has_reward,[$data['reward_id']]));
                DB::transaction(function () use ($act_zong_user, $new_has_flag,$new_has_reward,$data, $member,$theDelivery,$m_tesco) {
                    # 更新领奖记录到数据库
                    $res1 = $act_zong_user->where('member_id',$data['act_mem_id'])->update([
                        'has_flag' => $new_has_flag,
                        'has_reward'=> $new_has_reward,
                    ]);
                    # 兑换五芳斋粽子 1.member中记录兑奖信息 2=小米手环 ；2.兑换成功,将信息加入发货表
//                    $new_tesco = empty($m_tesco) ? json_encode(['2']) : json_encode(array_merge($m_tesco,['2']));
//                    $res2 = $member->where('id',$data['act_mem_id'])->update(['tesco' => $new_tesco]);
                    $res3 = $theDelivery->create(['member_id'=> $data['act_mem_id'],'intmall_id' =>'2','Order'=>'端午活动小米手环intmall_id = actmall_id']);//intmall_id = actmall_id
                    if ($res1 && $res3 ){
                        DB::commit();
                        res(null, '恭喜，获得奖励，请您在用户信息中核对您的收货地址');
                    }else{
                        DB::rollback();//事务回滚
                        res(null, '兑换失败，请联系客服', 'fail', 100);
                    }
                });
            }else{
                res(null, '已兑换或未达成', 'fail', 105);//条件不符合105
            }

        }
    }
    /**
     * 大批量生成一种旗子并入库（超350）
     * 传入：活动act_id 旗子名称flag_name 粽子类别或等级flag_lv
     *生成经纬度:lat:22.5321947045 lng:113.9358133078,
     * 默认参数：status=1（可忽略）
     * 返回：种子坐标，
     * $act_id,$flag_name,$flag_lv,
     */
//    protected function add_some_zon(Request $request,ActOneFlag $actOneFlag)
//    {
//        set_time_limit(0);//设置超时时间
//        $data = $request->only('act_id','flag_name','flag_lv');
//        $role = [
//            'act_id' => 'exists:activity,id',
//            'flag_name' => 'required',
//            'flag_lv' => 'integer',
//        ];
//        $message = [
//            'act_id.exists' => '请求不合法',
//            'flag_name.required' => '旗子别名必填',
//            'flag_lv.integer' => '请求不合法2',
//        ];
//        $validator = Validator::make($data, $role, $message);
//        if ($validator->fails()) {
//            res(null, $validator->messages()->first(), 'fail', 101);
//        }
//
//        $adr = make_ave_longitude(12);//获取平均分布的$n*320条数据用来平均分布粽子
//        shuffle ($adr);//打乱顺序
//        dump($adr);
//        $arr_tf = [];
//        foreach ($adr as $k => $v){
//            $arr_tf[] = $actOneFlag->create([
//                'act_id' => $data['act_id'],
//                'flag_name'=> $data['flag_name'],
//                'flag_lv' => $data['flag_lv'],
//                'lng' => $v['lng'],
//                'lat' => $v['lat'],
//            ]);
//        }
//        dump( $arr_tf);
//
//    }
    /**
     * 在南山区生成粽子51个
     */
//    protected function add_zon(Request $request,ActOneFlag $actOneFlag)
//    {
//        set_time_limit(0);//设置超时时间
//        $data = $request->only('act_id','flag_name','flag_lv');
//        $role = [
//            'act_id' => 'exists:activity,id',
//            'flag_name' => 'required',
//            'flag_lv' => 'integer',
//        ];
//        $message = [
//            'act_id.exists' => '请求不合法',
//            'flag_name.required' => '旗子别名必填',
//            'flag_lv.integer' => '请求不合法2',
//        ];
//        $validator = Validator::make($data, $role, $message);
//        if ($validator->fails()) {
//            res(null, $validator->messages()->first(), 'fail', 101);
//        }
//
//        //$adr = make_ave_longitude(12);//获取平均分布的$n*320条数据用来平均分布粽子
//        $nan = config('address.match')['南山区'];   //获取南山区的所有地址
//        $adr = [];
//        foreach ($nan as $k => $v){
//            $adr[] =  Latitude_and_longitude($v,1);
//        }
//        dump($adr);
//        $arr_tf = [];
//        foreach ($adr as $k => $v){
//            $arr_tf[] = $actOneFlag->create([
//                'act_id' => $data['act_id'],
//                'flag_name'=> $data['flag_name'],
//                'flag_lv' => $data['flag_lv'],
//                'lng' => $v[0]['lng'],
//                'lat' => $v[0]['lat'],
//            ]);
//        }
//        dump( $arr_tf);
//        $new_zong = $actOneFlag->select('flag_name')->where('flag_lv',5)->get();// 4-3900 3-3900 2-3900 1-7800
//        $cout_new = empty($new_zong) ? 0 : count($new_zong);//粽子个数
//        dump($cout_new);
//    }
    /**
     * 全深圳生成低于300个粽子的方法（生成49个）
     * @param Request $request
     * @param ActOneFlag $actOneFlag
     */
//    protected function add_zon_ave(Request $request,ActOneFlag $actOneFlag)
//    {
//        set_time_limit(0);//设置超时时间
//        $data = $request->only('act_id','flag_name','flag_lv');
//        $role = [
//            'act_id' => 'exists:activity,id',
//            'flag_name' => 'required',
//            'flag_lv' => 'integer',
//        ];
//        $message = [
//            'act_id.exists' => '请求不合法',
//            'flag_name.required' => '旗子别名必填',
//            'flag_lv.integer' => '请求不合法2',
//        ];
//        $validator = Validator::make($data, $role, $message);
//        if ($validator->fails()) {
//            res(null, $validator->messages()->first(), 'fail', 101);
//        }
//
//        //$adr = make_ave_longitude(12);//获取平均分布的$n*320条数据用来平均分布粽子
//        $all_adr = config('address.match');   //获取深圳所有地址
//        $result = array_reduce($all_adr, function ($result, $value) {
//            return array_merge($result, array_values($value));
//        }, array());//转一维数组 320多条
//        //打乱
//        shuffle ($result);
//        //截取50个坐标
//        $key = array_rand($result,49);
//        dump($result['321']);
//        $adr = [];
//        foreach ($key as $v){
//            $adr[] = Latitude_and_longitude($result[$v],1);
//        }
//        $arr_tf = [];
//        foreach ($adr as $k => $v){
//            $arr_tf[] = $actOneFlag->create([
//                'act_id' => $data['act_id'],
//                'flag_name'=> $data['flag_name'],
//                'flag_lv' => $data['flag_lv'],
//                'lng' => $v[0]['lng'],
//                'lat' => $v[0]['lat'],
//            ]);
//        }
//        $new_zong = $actOneFlag->select('flag_name')->where('flag_lv',5)->get();// 4-3900 3-3900 2-3900 1-7800
//        $cout_new = empty($new_zong) ? 0 : count($new_zong);//粽子个数
//        dump($cout_new);
//    }
    /**
     * 生成全部粽子后重新打乱排序
     */
//    public function shf_zon(ActOneFlag $actOneFlag)
//    {
//        $data = $actOneFlag->select('id','lng','lat','flag_name','flag_lv','act_id')
//            ->where('id','>','6')
//            ->get()->toArray();
//        shuffle ($data);//打乱顺序
//        shuffle ($data);//打乱顺序
//        DB::transaction(function () use ($actOneFlag,$data) {
//            foreach ($data as $k => $v){
//                $arr_tf[] = $actOneFlag->create([
//                    'act_id' => $v['act_id'],
//                    'flag_name'=> $v['flag_name'],
//                    'flag_lv' => $v['flag_lv'],
//                    'lng' => $v['lng'],
//                    'lat' => $v['lat'],
//                ]);
//            }
//            DB::commit();
//            echo date("H:i:s");
//        });
//        $tf = $actOneFlag->where('id','>','6')->where('id','<','19613')->forceDelete();dump($tf);die();//删除数据
//    }





}
