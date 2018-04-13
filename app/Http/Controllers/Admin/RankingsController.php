<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\Picture;
use App\Models\Rankings;
use App\Models\Steps;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class RankingsController extends Controller
{

    public function index()
    {
        return view('admin.rankings.index');
    }

    public function ajax_list(Request $request, Rankings $rankings)//,Member $member
    {
        if ($request->ajax()) {
            $data = $rankings->with('activity')->select('id', 'create_at', 'ranking', 'type')->get();
            foreach ($data as $k => $v) {
                $time = date('Y-m-d H:i:s', strtotime($v['create_at']));
                $data[$k]['create_at'] = $time;
            }
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
     * 查看详细名次
     */
    public function showcontent($ranking_id)
    {
        $ranking = new Rankings();
        $member = new Member();
        $res = $ranking->select('ranking')->find($ranking_id);
        $row = json_decode($res['ranking'], true);
        foreach ($row as $k => $v) {
            foreach ($v as $item => $ks) {
                $res = $member->select('nickname')->find($item);
                $row[$k]['nickname'] = $res['nickname'];
            }
        }
        $data['info'] = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return view('admin.rankings.showContent', $data);
    }


    /**
     * 查看步数/获取优惠券排行
     */
    public function see_ranking(Request $request, Steps $steps, Coupon $coupon, Rankings $rankings)
    {
        $data = $request->only('member_id');
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
        //今日名次
        $time = date('Ymd', time());
        $res1 = $rankings->where('create_at', $time)->select('ranking')->first();
        if (empty($res1)) {
            $arr['ranking'] = 0;
            $arr['steps'] = 0;
        } else {
            $ranking = json_decode($res1['ranking'], true);
            $a = 1;
            foreach ($ranking as $k => $v) {
                foreach ($v as $key => $val) {
                    if ($key == $data['member_id']) {
                        $arr['ranking'] = $a;
                        $arr['steps'] = $val;
                        break;//找到用户所在的名次了
                    }
                    $a++;
                }
            }
        }
        //步数排行
        $date = new \DateTime();
        $date->modify('this week');
        $first_day_of_week = $date->format('Ymd');//本周第一天
        $date->modify('this week +6 days');
        $end_day_of_week = $date->format('Ymd');//本周最后一天
        //找出本周用户的步数
        $res1 = $steps->select('steps')->where('mem_id', $data['member_id'])->where('sports_t', '>=', $first_day_of_week)->where('sports_t', '<=', $end_day_of_week)->first();
        $week = [];
        $ranking = 0;
        $step = 0;
        if (!empty($res1)) {
            //本周用户没有运动
            $res2 = $steps->select('mem_id', 'steps')->where('sports_t', '>=', $first_day_of_week)->where('sports_t', '<=', $end_day_of_week)->get();
            foreach ($res2 as $k => $v) {
                if (array_key_exists($v['mem_id'], $week)) {
                    $week[$v['mem_id']] += $v['steps'];//累计 $week[$v['mem_id']] +
                } else {
                    $week[$v['mem_id']] = $v['steps'];
                }
            }
            //对其排序后查看用户的名次和步数,此时用户必定榜上有名
            arsort($week);
            $a = 1;
            foreach ($week as $k => $v) {
                if ($k == $data['member_id']) {
                    $ranking = $a;
                    break;//找到用户所在的名次了
                }
                $a++;
            }
            $step = $week[$data['member_id']];
        }
        $arr['week_step_ranking'] = $ranking;
        $arr['week_step_num'] = $step;
        //优惠券排行
        $first_day_of_week = date('Y-m-d H:i:s', strtotime($first_day_of_week));
        $end_day_of_week = date('Y-m-d H:i:s', strtotime($end_day_of_week));
        $res = $coupon->select('member_id')->where('member_id', $data['member_id'])->where('create_at', '>=', $first_day_of_week)->where('create_at', '<=', $end_day_of_week)->first();
        if (empty($res)) {
            //用户一周之内,没有获得优惠券,那么名次和数量是0
            $arr['coupon_ranking'] = 0;
            $arr['coupon_num'] = 0;
        } else {
            //查询出本周所有的,
            $res = $coupon->select('member_id')->where('create_at', '>=', $first_day_of_week)->where('create_at', '<=', $end_day_of_week)->get();
            $temp = [];
            $temp2 = [];
            foreach ($res as $k => $v) {
                $temp[$v['member_id']][] = 1;
            }
            foreach ($temp as $k => $v) {
                $temp2[$k] = count($temp[$k]);
            }
            arsort($temp2);
            $a = 1;
            foreach ($temp2 as $k => $v) {
                if ($k == $data['member_id']) {
                    $arr['coupon_ranking'] = $a;
                    break;////找到用户所在的名次了
                }
                $a++;
            }
            $arr['coupon_num'] = $temp2[$data['member_id']];
        }
        res($arr);
    }

    /*
     * 查看排行->日
     */
    public function see_day_ranking(Request $request, Member $member, Steps $steps, Rankings $rankings)
    {
        $data = $request->only('member_id');
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
        $time = date('Ymd', time());//今天
        $res2 = $rankings->select('ranking')->where('create_at', $time)->first();
        //今天所有的运动步数
        if (empty($res2['ranking'])) {
            res(null, '查询成功', 'success', 201);
        }
        $ranking = json_decode($res2['ranking'], true);
        $falg = false;
        foreach ($ranking as $k => $v) {
            foreach ($v as $item => $value) {
                //每日中每个人的详细信息
                $res4 = $member->select('avatar', 'nickname', 'phone')->find($item);
                $nickname = empty($res4['nickname']) ? $res4['phone'] : $res4['nickname'];
                $arr['list'][$k] = ['nickname' => $nickname, 'avatar' => $request->server('HTTP_HOST') . '/' . $res4['avatar'], 'step' => $value, 'ranking' => $k + 1];
                //今天用户的排名???
                if ($item == $data['member_id']) {
                    $falg = true;
                    //有用户,存入用户的所在名次和步数
                    $arr['member'] = ['ranking' => $k + 1, 'steps' => $value];
                }
                if (count($arr['list']) >= 100) {
                    break 2;
                }
            }
        }
        while (count($arr['list']) > 5) {
            array_pop($arr['list']);
        }
        if ($falg == false) {
            //超出一百名,找下有没有用户的步数
            $m = $steps->select('steps')->where('mem_id', $data['member_id'])->where('sports_t', $time)->first();
            if (empty($m)) {
                $arr['member'] = ['ranking' => 0, 'steps' => 0];
            } else {
                $arr['member'] = ['ranking' => 0, 'steps' => $m['steps']];
            }
        }
        res($arr);
    }

    /*
     * 查看排行->月
     */
    public function see_month_ranking(Request $request, Member $member, Steps $steps, Rankings $rankings)
    {
        $data = $request->only('member_id');
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
        //头像和昵称不返还
        $start = date('Ym01', strtotime(date('Ymd')));//获取指定月份的第一天
        $end = date('Ymt', strtotime(date('Ymd'))); //获取指定月份的最后一天
        $res2 = $rankings->select('ranking')->where('create_at', '>=', $start)->where('create_at', '<=', $end)->get();
        //今天所有的运动步数
        if (empty($res2[0])) {
            res(null, '查询成功', 'success', 201);
        }
        //在本月中,查看所有
        foreach ($res2 as $k => $v) {
            $day_data = json_decode($v['ranking'], true);
            //月: 先将所有人的id和步数存入同一个数组
            $array[] = $day_data;
        }

        $yes = [];
        //将相同ID的,存入累计
        foreach ($array as $k => $v) {
            foreach ($v as $key => $val) {
                foreach ($val as $item => $value) {
                    if (array_key_exists($item, $yes)) {
                        $yes[$item] = $yes[$item] + $value;
                    } else {
                        $yes[$item] = $value;
                    }
                }
            }
        }
        arsort($yes);
        $i = 0;
        $falg = false;
        foreach ($yes as $k => $v) {
            $m = $member->select('avatar', 'nickname', 'phone')->find($k);
            $nickname = empty($m['nickname']) ? $m['phone'] : $m['nickname'];
            $arr['list'][] = ['nickname' => $nickname, 'avatar' => $request->server('HTTP_HOST') . '/' . $m['avatar'], 'step' => $v, 'ranking' => $i + 1];
            //查找本月中,有没有用户,
            if ($k == $data['member_id']) {
                //本月中有用户,先将步数赋值,名次等下再给
                $arr['member'] = ['steps' => $v, 'ranking' => $i + 1];
                $falg = true;
            }
            $i = $i + 1;
            if (count($arr['list']) >= 100) {
                break;
            }
        }
        if ($falg == false) {
            //用户没有在前百,找下用户这个月的步数,名次则是100之外
            $res = $steps->select('steps')->where('mem_id', $data['member_id'])->where('sports_t', '>=', $start)->where('sports_t', '<=', $end)->get();
            //用户本月是否有运动
            if (empty($res[0])) {
                $arr['member'] = ['steps' => 0, 'ranking' => 0];
            } else {
                $steps = 0;
                foreach ($res as $k => $v) {
                    $steps += $v['steps'];
                }
                $arr['member'] = ['steps' => $steps, 'ranking' => 0];
            }
        }
        //删除95
        while (count($arr['list']) > 5) {
            array_pop($arr['list']);
        }
        res($arr);
    }

    /*
     * 查看排行->总
     */
    public function see_total_ranking(Request $request, Member $member, Steps $steps, Rankings $rankings)
    {
        $data = $request->only('member_id');
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
        $res2 = $rankings->select('ranking')->get();
        if (empty($res2[0])) {
            res(null, '查询成功', 'success', 201);
        }
        //在所有数据中,查看所有
        foreach ($res2 as $k => $v) {
            $day_data = json_decode($v['ranking'], true);
            //月: 先将所有人的id和步数存入同一个数组
            $array[] = $day_data;
        }

        $yes = [];
        //将相同ID的,存入累计
        foreach ($array as $k => $v) {
            foreach ($v as $key => $val) {
                foreach ($val as $item => $value) {
                    if (array_key_exists($item, $yes)) {
                        $yes[$item] = $yes[$item] + $value;
                    } else {
                        $yes[$item] = $value;
                    }
                }
            }
        }
        arsort($yes);
        $i = 0;
        $falg = false;
        foreach ($yes as $k => $v) {
            $m = $member->select('avatar', 'nickname', 'phone')->find($k);
            $nickname = empty($m['nickname']) ? $m['phone'] : $m['nickname'];
            $arr['list'][] = ['nickname' => $nickname, 'avatar' => $request->server('HTTP_HOST') . '/' . $m['avatar'], 'step' => $v, 'ranking' => $i + 1];
            //查找本月中,有没有用户,
            if ($k == $data['member_id']) {
                //本月中有用户,先将步数赋值,名次等下再给
                $arr['member'] = ['steps' => $v, 'ranking' => $i + 1];
                $falg = true;
            }
            $i = $i + 1;
            if (count($arr['list']) >= 100) {
                break;
            }
        }
        if ($falg == false) {
            //用户没有在前百,找下用户这个月的步数,名次则是100之外
            $res = $steps->select('steps')->where('mem_id', $data['member_id'])->get();
            //用户本月是否有运动
            if (empty($res[0])) {
                $arr['member'] = ['steps' => 0, 'ranking' => 0];
            } else {
                $steps = 0;
                foreach ($res as $k => $v) {
                    $steps += $v['steps'];
                }
                $arr['member'] = ['steps' => $steps, 'ranking' => 0];
            }
        }
        while (count($arr['list']) > 5) {
            array_pop($arr['list']);
        }
        res($arr);
    }

    public function destroy($id)
    {
        $rankings = new Rankings();
        $rankings = $rankings->find($id);
        $res = $rankings->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

}
