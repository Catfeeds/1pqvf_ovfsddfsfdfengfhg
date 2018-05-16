<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\Perseverance;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class PerseveranceController extends Controller
{

    public function index()
    {
        return view('admin.perseverance.index');
    }

    public function ajax_list(Request $request, Perseverance $perseverance)
    {
        if ($request->ajax()) {
            $data = $perseverance->with('member')->select('id', 'member_id', 'punch_d', 'award', 'delivery', 'status2')->get();//address
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

    /*
     * 后台展示收货地址
     */
    public function showadd($member_id)
    {
        $member = new Member();
        $res = $member->select('address')->find($member_id);
        $data['info'] = $res['address'];
        return view('admin.perseverance.showadd', $data);
    }

    /*
     * 发货
     */
    public function send($meber_id)
    {
        $perseverance = new Perseverance();
        $res = $perseverance->where('member_id', $meber_id)->select('status2', 'delivery')->first();
        //已领奖
        if ($res['status2'] == 3) {
            //判断是否已经发货
            if (empty($res['delivery'])) {
                $res = $perseverance->where('member_id', $meber_id)->select()->update(['delivery' => date('Y-m-d H:i:s')]);
                if ($res) {
                    return ['status' => 'success', 'msg' => '成功'];
                }
            }
        }
        return ['status' => 'fail', 'msg' => '用户还没点击领奖,或用户的目标未达成'];
    }

    /*
     * 取消发货
     */
    public function undo($meber_id)
    {
        //根据传递过来的用户id,去健康达人中寻找,
        $perseverance = new Perseverance();
        $res = $perseverance->where('member_id', $meber_id)->select('delivery')->first();
        //已领奖
        //判断是否已经发货
        if (!empty($res['delivery'])) {
            $res = $perseverance->where('member_id', $meber_id)->select()->update(['delivery' => null]);
            if ($res) {
                return ['status' => 'success', 'msg' => '成功'];
            }
        }
        return ['status' => 'fail', 'msg' => '还未发货'];
    }

    /**
     * 毅力使者打卡接口
     */
    public function clock_in(Request $request, Perseverance $perseverance, Member $member, Activity $activity)
    {
        $activity_id = 2;
        //活动结束后,防止数据修改
        $res = $activity->select('start_at', 'end_at')->where('id', $activity_id)->first();
        $time1 = strtotime($res['start_at']);
        $time2 = strtotime($res['end_at']);
        $time3 = time();
        if ($time3 < $time1 || $time3 > $time2) {
            res(null, '不在活动时间内', 'fail', 104);
        }
        //总步数是否要随着断签而清零  先把总步数累计,再把总步数清零
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
        //判断用户合法性,
        $res = $member->find($data['member_id']);
        if ($res == null) {
            res(null, '用户不合法', 'fail', 101);
        }
        $time = date('Ymd', time());
        //如果今日用户已经打卡,则踢出去
        $res1 = $perseverance->where('member_id', $data['member_id'])->select('status', 'status2', 'punch_d', 'total_steps', 'award')->first();
        if (!empty($res1['status'])) {
            $row = json_decode($res1['status'], true);
            foreach ($row as $key => $val) {
                if ($val == $time) {
                    //有今天的记录,踢出去
                    res(null, '你今天已经打卡了', 'success', 202);
                }
            }
        }
        //查询今日的步数
        $res = $member->select('steps')->find($data['member_id']);
        $em = null;//今天运动的步数
        $falg = false;//是否断签
        //如果运动不为空
        if (!empty($res['steps'])) {
            //判断今天运动了几步
            foreach ($res['steps'] as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k == $time) {
                        $em = $v;
                    }
                }
            }
            if (!empty($res1['status'])) {
                $array = json_decode($res1['status'], true);
                //将time 1-2 之前的排除
                $array = check_times($array, $time1, $time2);
                if ($array == []) {
                    res(null, '系统时间不正确', 'fail', 110);
                }
                //验证是否断签
                $falg = check($array, $time);
            }
        }
        if ($em == null || $em < 20000) {
            res(null, '步数不足2万步', 'fail', 105);
        }
        //如果是第一天
        if (empty($res1['status']) || $falg) {
            //则加入
            $arr2[0] = $time;
        } else {
            $row = json_decode($res1['status'], true);
            $count = count($row);
            $arr2 = $row;
            $arr2[$count] = $time;
        }
        //如果出现断签,则重新计算打卡天数
        if ($falg) {
            $punch_d = 1;
        } else {
            $punch_d = $res1['punch_d'] + 1;
            $em = $res1['total_steps'];
        }
        //当打卡次数大于等于30天,则将活动状态改为x
        if ($punch_d >= 30) {
            //判断状态是否为已领奖,如果不为已领奖,则修改
            if ($res1['status2'] == 2) {
                //同时,将活动达成状态改为1
                $perseverance->select()->where(['member_id' => $data['member_id']])->update(['status2' => 1]);
            }
        }
        $arr = json_encode($arr2);
        $res = $perseverance->select()->where('member_id', $data['member_id'])->update(['status' => $arr, 'punch_d' => $punch_d, 'total_steps' => $em]);
        if ($res) {
            res(null, '打卡成功');
        }
        res(null, '失败', 'fail', 100);
    }

}
