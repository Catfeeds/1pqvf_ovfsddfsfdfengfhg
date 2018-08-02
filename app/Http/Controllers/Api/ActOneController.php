<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\ActOne;
use App\Models\ActOneFlag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class ActOneController extends Controller
{
    //报名状态,1=已报名,2=未报名

    /**
     * 查找所有深大旗子
     * act_id = 4
     */
    public function slc_flg(Request $request,ActOneFlag $act_one_flag)
    {
        $data = $request->only('act_mem_id','lat', 'lng');
        $role = [
            'act_mem_id' => 'exists:act_one,member_id',

            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'act_mem_id.exists' => '请先报名',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //获取该活动所有旗子的坐标
        $act_id = 4;//深大活动的id
        $status = 1;//生效的旗子
        $lng_lat = $act_one_flag->grt_all_flag($act_id,$status);
        res($lng_lat);
    }
    /**
     * 查询是否可以报名活动一
     * 报名状态,0=可以报名 1=已报名,2=不在活动时间内
     */
    function is_enrol(Request $request,ActOne $act_one,Activity $activity){
        //判断是健康达人还是夺旗先锋
        $data = $request->only('member_id', 'activity_id');
        $role = [
            'member_id' => 'exists:member,id',
            'activity_id' => 'exists:activity,id',
        ];
        $message = [
            'member_id.exists' => '用户id不合法！',
            'activity_id.exists' => '赛事id不合法！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询是否在活动时间内
        $activity_num = $activity->select('man_num', 'start_at', 'end_at')->where('id',$data['activity_id'])->first();
        $time1 = strtotime($activity_num['start_at']);//开始时间
        $time2 = strtotime($activity_num['end_at']);//结束时间
        $time3 = time();
        if (! ($time3 >$time1 && $time3 < $time2)) {
            res(['en_status'=>2], '不在活动时间内', 'fail', 104);
        }
        //查询是否已报名
        $res = $act_one->where('member_id',$data['member_id'])->first();
        if (!empty($res)) {
            res(['en_status'=>1], '您已报名', 'fail', 202);
        }
        res(['en_status'=>0],'可以报名', 'success', 200);
    }

    /**
     * 查询获取的徽章情况
     * @param Request $request
     * @param ActOne $act_one
     * 返回地点状态
     */
    public function has_flg(Request $request,ActOne $act_one){
        $data = $request->only('act_mem_id','lat', 'lng');
        $role = [
            'act_mem_id' => 'exists:act_one,member_id',
        ];
        $message = [
            'act_mem_id.exists' => '请先报名',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        # 查询已获取的徽章
        $has_fl = $act_one->select('has_flag')->where('member_id',$data['act_mem_id'])->first();
        if(empty($has_fl['has_flag'])){//都没获取返回空
            res(null, '查询成功', 'success', 200);
        }
        //返回已获取的徽章id
        $arr = json_decode($has_fl['has_flag']);
        res($arr);
    }
    /**
     * 点击旗子查看小旗子详情
     */
    public function cl_flg(Request $request,ActOne $act_one,ActOneFlag $act_one_flag){
        $data = $request->only('act_mem_id','lat', 'lng','flag_id');
        $role = [
            'act_mem_id' => 'exists:act_one,member_id',
            'lat' => 'required',
            'lng' => 'required',
            'flag_id' => 'exists:act_one_flag,id',
        ];
        $message = [
            'act_mem_id.exists' => '请先报名',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
            'flag_id.exists' => '操作不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询该旗子详情
        $flg_info = $act_one_flag->select('id','status','flag_name')->where('id',$data['flag_id'])->first();
        $flg_info = obj_arr($flg_info);
        #判断旗子能不能被点亮$lt_status
        //获取该用户拥有的旗子的一维数组
        $flg_ids = $act_one->check_has_flg($data['act_mem_id']) ;
        //判断该旗子点亮权限 [0已点亮,1=未点亮]
        if(empty($flg_ids)){//如果为空可以点亮
            $lt_status = ['lt_status' => 1];
        }else{
            //不为空
            if (in_array($data['flag_id'], $flg_ids)) {//已点亮，不可点亮
                $lt_status = ['lt_status' => 0];
            }else{
                $lt_status = ['lt_status' => 1];
            }
        }
        //拼接数据
        $arr = array_merge($flg_info,$lt_status);
        res($arr);
    }

    /**
     * 点亮小旗子
     * @param Request $request
     * @param ActOne $act_one
     */
    public function lt_flg(Request $request,ActOne $act_one){
        $data = $request->only('act_mem_id','lat', 'lng','flag_id');
        $role = [
            'act_mem_id' => 'exists:act_one,member_id',
            'lat' => 'required',
            'lng' => 'required',
            'flag_id' => 'exists:act_one_flag,id',
        ];
        $message = [
            'act_mem_id.exists' => '请先报名',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
            'flag_id.exists' => '操作不合法',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //前端判断距离，大于误差距离，拒绝点亮
        //获取该用户拥有的旗子的一维数组
        $flg_ids = $act_one->check_has_flg($data['act_mem_id']);
        //点亮该旗子
        if(empty($flg_ids)){//如果为空直接加入
            $arr[] = $data['flag_id'];
        }else{
            //不为空
            if (in_array($data['flag_id'], $flg_ids)) {
                res(null, '已点亮', 'success', 202);
            }
            array_push($flg_ids, $data['flag_id']);
            $arr = $flg_ids;
        }
        $arr = json_encode($arr);
        $res = $act_one->where('member_id', $data['act_mem_id'])->update(['has_flag' => $arr]);
        if ($res) {
            res(null, '恭喜，点亮成功');
        }
        res(null, '失败', 'fail', 100);
    }
}
