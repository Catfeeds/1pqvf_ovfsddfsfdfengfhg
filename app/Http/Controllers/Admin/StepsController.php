<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\Perseverance;
use App\Models\Rankings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Steps;
use App\Models\Member;
use App\Models\Health;
use Validator;
use Illuminate\Support\Facades\Redis;

class StepsController extends Controller
{

    /**
     * @param 增加步数接口 ,存入步数表及用户步数记录
     */
    public function They_count(Request $request, Steps $steps, Member $member)
    {
        $data = $request->only('member_id', 'steps');
        $role = [
            'member_id' => 'required',
            'steps' => 'required|min:1|integer',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'steps.required' => '步数不能为空！',
            'steps.min' => '步数不能能低于1！',
            'steps.integer' => '步数只能为数字！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $data['id'] = $data['member_id'];
        $time = date('Ymd', time());
        $res = $steps->where(['mem_id' => $data['id']])->where(['sports_t' => $time])->first();
        //用户今天的步数不为空
        if (!empty($res)) {
            //第一次:录入100步
            //第二次:录入200步(实际为100),那么应该增加的步数,实际上应该是100
            $data['steps'] = $data['steps'] - $res->steps;//200 - 100 100步
            $res->steps = $res->steps + $data['steps']; // 100+100
            $row = $res->save();
        } else {
            $re['mem_id'] = $data['id'];
            $re['sports_t'] = $time;
            $re['steps'] = $data['steps'];
            $row = $steps->create($re);
        }
        $res = $member->select('steps', 'integral')->find($data['id']);
        if (empty($res)) {
            res(null, '该用户不存在或被禁用', 'fail', 106);
        }
        $arr = [];
        $arr = $res['steps'];
        if (!empty($arr)) {
            //先循环找出今天是否记录,如果记录了,则累计 否则,则直接将今天的记录存入(不损坏原数据)
            $flag = false;
            //首先判断这个件在数据中是否存在,如果存在,则累计,然后直返$flag
            foreach ($arr as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k == $time) {
                        //有今天的记录,累计
                        $arr[$key][$k] = $v + $data['steps'];
                        $flag = true;
                        break 2;
                    }
                }
            }
            if (!$flag) {
                //不存在
                $arr[][$time] = $data['steps'];
            }
        } else {
            //如果为空,则直接将今天的记录存入
            $arr[0][$time] = $data['steps'];
        }
        //同时,调用一次健康达人记录表
        $r = $this->integral($data['id'], $data['steps']);
        //调用一次毅力使者
        $arr = json_encode($arr);
        //积分
        $integral = $this->member_step($data['id'], $data['steps'], $res['integral']);
        //先取出redis中,今天的积分
        $res = $member->where(['id' => $data['id']])->select()->update(['steps' => $arr, 'integral' => $integral]);
        //注意,步数可能多次传入,要先判断某一天是否存在,然后累计
        if ($res) {
            //记录成功后,再录入排行表
            $re = $this->rankings($data['id'], $data['steps']);
            res(null, '记录成功');
        }
        res(null, '添加失败', 'fail', 100);
    }

    /**
     * 每天上限200积分
     */
    public function member_step($member_id, $steps, $integral)
    {
        $time = date('Ymd', time()) . $member_id;
        //先判断此积分是否超过200
        $int = (floor($steps / 100) >= 200) ? 200 : floor($steps / 100);//本次步数运动的积分
        //判断是否已经记录
        $res = Redis::get($time);
        if (!empty($res)) {
            //已经记录过了,看看上一次记录了多少
            if ($res >= 200) {//今天已经200了
                return $integral;
            }
            //上次记录还没到200,看看本次
            $c = $res + $int;
            if ($c == 200) {
                //刚好200,说明本次要增加的,跟上次记录的,加起来刚好是200积分 如 30 ,本次记录170,刚好200,将170和用户原本拥有的积分,加起来
                Redis::set($time, 200);
                return ($int + $integral);
            } elseif ($c > 200) {
                //本次要加的,加上上次记录的,已经超出了200 ,如已经记录30,本次记录140 =210
                $x = 200 - $res;//已经超出,则直接上限减去此数即可得出本次最多能加多少,再将本次要加的积分,和之前的积分加起来
                Redis::set($time, 200);
                return ($x + $integral);
            } elseif ($c < 200) {
                //本次要加的,加上上次记录不足200,入30+60 ,直接将90存入redis
                Redis::set($time, $c);
                return ($int + $integral);
            }
        } else {
            Redis::set($time, $int);
            return ($int + $integral);
        }
    }

    /**
     * 步数接口
     */
    public function integral($member_id, $integral)
    {
        //判断是否在活动时间内,不再则不计算
        $activity = New Activity();
        $activity_id = 1;
        $data = $activity->select('start_at', 'end_at')->where(['id' => $activity_id])->first();
        $time1 = strtotime($data['start_at']);
        $time2 = strtotime($data['end_at']);
        $time3 = time();
        if ($time3 < $time1 || $time3 > $time2) {
            //包括最后一天.不在录入
            return ['stauts' => 'fail', 'msg' => '不在活动时间内'];
        }

        //每天接收今天的运动步数,然后累计到总步数里面,月底结算总步数
        $data['member_id'] = $member_id;
        $data['integral'] = $integral;
        $health = new Health();
        $res = $health->where(['member_id' => $data['member_id']])->first();
        if ($res == null) {
            return ['stauts' => 'fail', 'msg' => '用户还没报名'];
        }
        if ($res['status'] == 1 || $res['status'] == 3) {
            //如果用户已经是达成状态,则不需要重复记录
            return ['status' => 'success', 'msg' => '目标已达成', 'code' => 202];
        }
        //总步数(每月累计步数)

        $total = $res->total + $data['integral'];
        $res1 = $health->where(['member_id' => $data['member_id']])->update(['steps' => $data['integral'], 'total' => $total]);
        //返回总步数
        if (!$res1) {
            return ['stauts' => 'fail', 'msg' => '修改失败'];
        }
        //每次增加步数的时候,查询一次当前状态
        $res2 = $this->complete($data['member_id']);
        if ($res2['status'] == 'success') {
            return ['status' => 'success', 'msg' => '目标已达成', 'code' => 202];
        }
        return ['stauts' => 'success', 'msg' => '成功', 'total' => $total];
    }

    /*
     * 活动状态是否达成
     */
    public function complete($member_id)
    {
        $health = new Health();
        $res = $health->where('member_id', $member_id)->select('status', 'target', 'total')->first();
        if ($res['total'] >= $res['target']) {
            //先判断是否等于3,如果等于3,则直接返回
            switch ($res['status']) {
                case 1:
                    return ['status' => 'success', 'msg' => '活动目标已达成'];
                    break;
                case 3:
                    return ['status' => 'success', 'msg' => '已领奖'];
                    break;
                default:
                    $res = $health->where('member_id', $member_id)->select()->update(['status' => 1]);
                    if ($res) {
                        return ['status' => 'success', 'msg' => '活动目标已达成'];
                    }
                    break;
            }
        }
        return ['status' => 'fail', 'msg' => '继续努力'];
    }

    /*
     * 录入排行表
     */
    public function rankings($member_id, $step)
    {
        //接收当前用户的步数和id
        $time = date('Ymd', time());//找下有没有今天,如果没有,则直接插入,有,则循环重新赋值
        $rankings = new Rankings();
        $res = $rankings->where('create_at', $time)->first();
        if (empty($res)) {
            //为空,今天还没有排名,将今天的数据插入
            $arr = ['0' => [
                $member_id => $step,
            ]];
            $data = [
                'create_at' => $time,
                'ranking' => json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),//每日的第一名
            ];
            $res = $rankings->create($data);
        } else {
            //不为空,在步数表中找出今天的记录,重新进行一次排序
            $step = new Steps();
            $res = $step->orderBy('steps', 'DESC')->select('mem_id', 'steps')->where('sports_t', $time)->get();
            foreach ($res as $k => $v) {
                $arr[] = [$v['mem_id'] => $v['steps']];
            }
            $data = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);//每日的第一名
            $res = $rankings->where('create_at', $time)->update(['ranking' => $data]);
        }
        return 1;
    }

}
