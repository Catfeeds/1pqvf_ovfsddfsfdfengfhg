<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\Health;
use App\Models\Medal;
use App\Models\Member;
use App\Models\Perseverance;
use App\Models\Rankings;
use App\Models\TakeFlag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class MedalController extends Controller
{

    public function index()
    {
        return view('admin.medal.index');
    }

    public function create(Medal $medal)
    {
        $data['bright'] = $medal->select('id', 'note')->where(['pid' => 0])->get();
        return view('admin.medal.create', $data);
    }

    public function store(Request $request, Medal $medal)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('medal_url', 'note', 'type', 'content', 'rewards', 'pid', 'bright');
        $role = [
            'medal_url' => 'required|image',
            'note' => 'required',
            'type' => 'required|integer',
            'content' => 'required',
            'rewards' => 'required|integer',
        ];
        $message = [
            'medal_url.required' => '奖章图片不能为空！',
            'medal_url.image' => '奖章图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'note.required' => '奖章简介不能为空！',
            'content.required' => '奖章详情不能为空！',
            'type.required' => '奖章类型不能为空！',
            'type.integer' => '奖章类型必须为数值！',
            'rewards.required' => '奖励积分不能为空！',
            'rewards.integer' => '奖励积分不正确！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //如果用户选择的是点亮,则不理会
        if ($data['pid'] != 0) {
            //选了暗淡,则将对应的奖章赋值给pid
            $data['pid'] = $data['bright'];
        }
        if (!empty($data['medal_url'])) {
            $res = uploadpic('medal_url', 'uploads/medal_url');//
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
            $data['medal_url'] = $res;
        }
        //数据调整
        $res = $medal->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Medal $medal)
    {
        if ($request->ajax()) {
            $data = $medal->select('id', 'content', 'medal_url', 'note', 'type', 'rewards', 'pid')->get();//'action',
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

    public function edit(Medal $medal)
    {
        $data['medal'] = $medal;
        //将生成路由分离
        $data['bright'] = $medal->select('id', 'note')->where(['pid' => 0])->get();
        return view('admin.medal.edit', $data);
    }

    public function update(Request $request, Medal $medal)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('medal_url', 'note', 'type', 'content', 'old_url', 'rewards', 'pid', 'bright');//,'create','select'
        $role = [
            'medal_url' => 'nullable|image',
            'type' => 'nullable|integer',
            'rewards' => 'nullable|integer',
        ];
        $message = [
            'medal_url.image' => '上传的图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'type.integer' => '类型错误！',
            'rewards.integer' => '奖励错误！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        if (!empty($data['medal_url'])) {
            $res = uploadpic('medal_url', 'uploads/medal_url');//
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
            $data['medal_url'] = $res;
        }
        //数据调整
        foreach ($data as $key => $val) {
            if (empty($data[$key]) && $key != 'pid') {
                unset($data[$key]);
            }
        }
        if ($data['pid'] != 0) {
            $data['pid'] = $data['bright'];
        }
        // 更新数据
        $res = $medal->update($data);
        if ($res) {
            if (!empty($data['medal_url'])) {
                @unlink($data['old_url']);
            }
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $medal = new Medal();
        $medal = $medal->find($id);
        $res = $medal->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }


    /*
     * 查询奖章
     * */
    public function select_medal(Request $request, Member $member, Medal $medal)
    {
        //根据用户的id
        $data = $request->only('member_id');
        $role = [
            'member_id' => 'required  | exists:member,id',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'member_id.exists' => '用户id不正确！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $arr = [];
        //调用一次12个字接口
        $sta = $this->insert_medal($data['member_id']);
        if ($sta == []) {
            $sta = null;
        }
        //查询并返回
        $res = $member->where(['id' => $data['member_id']])->select('medal')->first();
        if (empty($res)) {
            res(null, '用户不存在', 'fail', 102);
        }
        $medals = $res['medal'];//json_decode(,true);
        if (empty($medals)) {
            //如果为空,说明用户没有奖章,将所有暗淡的奖章返回 1:运动,2:优惠券,3:活动
            $res = $medal->select('id', 'medal_url', 'type', 'content', 'note', 'rewards')->where('pid', '!=', 0)->get();
            foreach ($res as $k => $v) {
                //未获得的奖章(暗淡)
                $type = $v['type'] == 1 ? 'sports' : ($v['type'] == 2 ? 'coupons' : 'activity');
                $arr[$type][] = [
                    'medal_id' => $v['id'],
                    'medal_url' => $request->server('HTTP_HOST') . '/' . $v['medal_url'],
                    'content' => $v['content'],
                    'note' => $v['note'],
                    'rewards' => $v['rewards'],
                ];
            }
            //已获得的奖章为0
            $arr['existing']['sports'] = 0;
            $arr['existing']['coupons'] = 0;
            $arr['existing']['activity'] = 0;
        } else {
            //如果有奖章,将已点亮和未点亮返回
            $res2 = $medal->select('id', 'medal_url', 'type', 'content', 'note', 'rewards')->whereIn('id', $medals)->get();
            foreach ($res2 as $key => $v) {
                $type = $res2 [$key]['type'] == 1 ? 'sports' : ($res2 [$key]['type'] == 2 ? 'coupons' : 'activity');
                //已获得的奖章(点亮)
                $arr[$type][] = [
                    'medal_id' => $res2[$key]['id'],
                    'medal_url' => $request->server('HTTP_HOST') . '/' . $res2[$key]['medal_url'],
                    'content' => $res2[$key]['content'],
                    'note' => $res2[$key]['note'],
                    'rewards' => $res2[$key]['rewards'],
                ];
                //将新获得的奖章id,放入其中
                if (!empty($sta)) {
                    //有新获得的徽章,循环已获得的徽章,将其放入
                    if (in_array($res2 [$key]['id'], $sta['medal_id'])) {
                        //如果当前的徽章id在新获得的徽章里面
                        $sta2[] = [
                            'medal_id' => $res2 [$key]['id'],
                            'medal_url' => $request->server('HTTP_HOST') . '/' . $res2[$key]['medal_url'],
                            'content' => $res2 [$key]['content'],
                            'note' => $res2 [$key]['note'],
                            'rewards' => $res2 [$key]['rewards'],
                        ];
                    }
                }
            }
            if (!empty($sta)) {
                $sta = $sta2;
            }
            //各类型已获得的奖章
            $arr['existing']['sports'] = empty($arr['sports']) ? 0 : count($arr['sports']);
            $arr['existing']['coupons'] = empty($arr['coupons']) ? 0 : count($arr['coupons']);
            $arr['existing']['activity'] = empty($arr['activity']) ? 0 : count($arr['activity']);
            //再找出未获得的奖章
            $res3 = $medal->select('id', 'medal_url', 'type', 'content', 'note', 'rewards')->where('pid', '!=', 0)->whereNotIn('pid', $medals)->get();
            if (!empty($res3[0])) {
                foreach ($res3 as $k => $v) {
                    //未获得的奖章(暗淡)
                    $type = $res3 [$k]['type'] == 1 ? 'sports' : ($res3 [$k]['type'] == 2 ? 'coupons' : 'activity');

                    $arr[$type][] = [
                        'medal_id' => $res3[$k]['id'],
                        'medal_url' => $request->server('HTTP_HOST') . '/' . $res3[$k]['medal_url'],
                        'content' => $res3[$k]['content'],
                        'note' => $res3[$k]['note'],
                        'rewards' => $res3[$k]['rewards'],
                    ];
                }
            }
        }
        $arr['news'] = $sta;
        res($arr);
    }

    /*
     * 查看奖章是否满足条件
     */
    public function insert_medal($id)
    {
        $arr = [];
        //调用一次12个字接口
        $res1 = $this->step($id, 1);//迈出第一步
        $res2 = $this->Adhere_to_the_movement($id, 2, 6);//坚持一周
        $res3 = $this->Adhere_to_the_movement($id, 3, 20);//坚持三周
        $res4 = $this->Adhere_to_the_movement($id, 4, 99);//坚持一百天
        $res5 = $this->SportsRanking($id, 5, 6);//刷榜达人,连续一周第一名
        $res6 = $this->SportsRanking($id, 6, 20);//享受运动,连续20天第一名
        $res7 = $this->I_want_to_discount($id, 7);//我要优惠
        $res8 = $this->coupons($id, 8, 10);//券场新人  券场大咖
        $res9 = $this->coupons($id, 9, 100);//券场新人  券场大咖
        $res10 = $this->Im_a_businessman($id, 10);//我是生意人
        $res11 = $this->takeFlag($id, 11);//夺旗先锋
        $res12 = $this->perseverance($id, 12);//毅力使者
        $res13 = $this->health($id, 13);//健康达人
        if ($res1 != 0) {
            $arr['medal_id'][] = $res1;
        }
        if ($res2 != 0) {
            $arr['medal_id'][] = $res2;
        }
        if ($res3 != 0) {
            $arr['medal_id'][] = $res3;
        }
        if ($res4 != 0) {
            $arr['medal_id'][] = $res4;
        }
        if ($res5 != 0) {
            $arr['medal_id'][] = $res5;
        }
        if ($res6 != 0) {
            $arr['medal_id'][] = $res6;
        }
        if ($res7 != 0) {
            $arr['medal_id'][] = $res7;
        }
        if ($res8 != 0) {
            $arr['medal_id'][] = $res8;
        }
        if ($res9 != 0) {
            $arr['medal_id'][] = $res9;
        }
        if ($res10 != 0) {
            $arr['medal_id'][] = $res10;
        }
        if ($res11 != 0) {
            $arr['medal_id'][] = $res11;
        }
        if ($res12 != 0) {
            $arr['medal_id'][] = $res12;
        }
        if ($res13 != 0) {
            $arr['medal_id'][] = $res13;
        }
        return $arr;
    }

    /*
     * 迈出第一步
     */
    public function step($id, $medal_id)
    {
        $member = New Member();
        $res = $member->select('medal', 'steps', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    //已经拥有此徽章
                    return false;
                }
            }
        }
        //如果还没拥有,或者为空,查询一下是否满足条件
        if (!empty($res['steps'])) {
            //找出该徽章奖励多少积分
            $medal = new Medal();
            $rewards = $medal->select('rewards')->find($medal_id);
            $integral = $res['integral'] + $rewards['rewards'];
            //先判断是否为空
            if (empty($res['medal'])) {
                //如果为空,则直接加入
                $arr = ['0' => $medal_id];
                $arr = json_encode($arr);
                $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
                return $medal_id;
            } else {
                //如果不为空,取出循环,然后将其加入
                $arr = $res['medal'];
                array_push($arr, $medal_id);
                $arr = json_encode($arr);
                $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
                return $medal_id;
            }
        }
        return false;
    }

    /**
     * 坚持运动系列徽章
     */
    public function Adhere_to_the_movement($id, $medal_id, $dates)
    {
        $member = New Member();
        $res = $member->select('medal', 'steps', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    return 0;
                }
            }
        }
        if (!empty($res['steps'])) {
            foreach ($res['steps'] as $key => $val) {
                foreach ($val as $k => $v) {
                    $arr[] = $k;
                }
            }
            $arr = sorts($arr);
            $n = SignIn($arr);
            if ($n >= $dates) {
                //满足一周,发放奖章,并返回奖章id
                $medal = new Medal();
                $rewards = $medal->select('rewards')->find($medal_id);
                $integral = $res['integral'] + $rewards['rewards'];
                if (empty($res['medal'])) {
                    //如果为空,则直接加入
                    $arr = ['0' => $medal_id];
                } else {
                    //如果不为空,取出循环,然后将其加入
                    $arr = $res['medal'];
                    array_push($arr, $medal_id);
                }
                $arr = json_encode($arr);
                $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
                return $medal_id;
            }
        }
        return 0;
    }

    /**
     * 刷榜达人
     */
    public function SportsRanking($id, $medal_id, $dates)
    {
        $member = New Member();
        $res = $member->select('medal', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    //已经拥有此徽章,直接终端不再往下执行
                    return 0;
                }
            }
        }
        $rankings = New Rankings();
        $rankings = $rankings->select('ranking', 'create_at')->where('type', null)->get();
        if (!empty($rankings)) {
            $arr = [];
            foreach ($rankings as $key => $val) {
                $ranking = json_decode($val->ranking, true);
                //找出用户获得第一名的记录,记录在数组中
                foreach ($ranking as $k => $v) {
                    foreach ($v as $item => $value) {
                        if ($item == $id) {
                            $time = strtotime($val->create_at);
                            $date = date('Ymd', $time);
                            $arr[$key] = $date;//时间
                        }
                    }
                }
            }
            if (!empty($arr)) {
                $n = SignIn($arr);
                if ($n >= $dates) {
                    $medal = new Medal();
                    $rewards = $medal->select('rewards')->find($medal_id);
                    $integral = $res['integral'] + $rewards['rewards'];
                    //满足一周,发放奖章,并返回奖章id
                    if (empty($res['medal'])) {
                        //如果为空,则直接加入
                        $arr = ['0' => $medal_id];
                    } else {
                        //如果不为空,取出循环,然后将其加入
                        $arr = $res['medal'];
                        array_push($arr, $medal_id);
                    }
                    $arr = json_encode($arr);
                    $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
                    return $medal_id;
                }
            }
        }
        return 0;
    }

    /*
     * 我要优惠
     */
    public function I_want_to_discount($id, $medal_id)
    {
        $member = New Member();
        $res = $member->select('medal', 'coupon_id', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    return 0;
                }
            }
        }
        //判断用户的优惠券字段是否为空
        if (!empty($res['coupon_id'])) {
            $medal = new Medal();
            $rewards = $medal->select('rewards')->find($medal_id);
            $integral = $res['integral'] + $rewards['rewards'];
            if (empty($res['medal'])) {
                //如果为空,则直接加入
                $arr = ['0' => $medal_id];
            } else {
                //如果不为空,取出循环,然后将其加入
                $arr = $res['medal'];
                array_push($arr, $medal_id);
            }
            $arr = json_encode($arr);
            $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
            return $medal_id;
        }
        //不为空,则证明达成,发送奖章
        return 0;
    }

    /**
     * 券场新人 券场大咖
     */
    public function coupons($id, $medal_id, $nums)
    {
        $member = New Member();
        $res = $member->select('medal', 'coupon_id', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    return 0;
                }
            }
        }
        //判断用户的优惠券字段是否为空
        if (!empty($res['coupon_id'])) {
            //不为空,判断优惠券数量是否达到了10
            $n = count($res['coupon_id']);
            if ($n >= $nums) {
                $medal = new Medal();
                $rewards = $medal->select('rewards')->find($medal_id);
                $integral = $res['integral'] + $rewards['rewards'];
                if (empty($res['medal'])) {
                    //如果为空,则直接加入
                    $arr = ['0' => $medal_id];
                } else {
                    //如果不为空,取出循环,然后将其加入
                    $arr = $res['medal'];
                    array_push($arr, $medal_id);
                }
                $arr = json_encode($arr);
                $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
                return $medal_id;
            }
        }
        return 0;
    }

    /*
     * 我是生意人
     */
    public function Im_a_businessman($id, $medal_id)
    {
        $integral_swap = 1000;//达成条件所需积分
        $member = New Member();
        $res = $member->select('medal', 'integral_swap', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    return 0;
                }
            }
        }
        //判断用户的券换分字段是否为空
        if (!empty($res['integral_swap'])) {
            if ($res['integral_swap'] >= $integral_swap) {
                $medal = new Medal();
                $rewards = $medal->select('rewards')->find($medal_id);
                $integral = $res['integral'] + $rewards['rewards'];
                if (empty($res['medal'])) {
                    //如果为空,则直接加入
                    $arr = ['0' => $medal_id];
                } else {
                    //如果不为空,取出循环,然后将其加入
                    $arr = $res['medal'];
                    array_push($arr, $medal_id);
                }
                $arr = json_encode($arr);
                $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
                return $medal_id;
            }
        }
        return 0;
    }

    /*
     * 夺旗先锋
     */
    public function takeFlag($id, $medal_id)
    {
        $member = New Member();
        $res = $member->select('medal', 'integral_swap', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    //已经拥有此徽章,直接终端不再往下执行
                    return 0;
                }
            }
        }
        $res2 = New TakeFlag();
        $data = $res2->select('flag_num')->where('member_id', $id)->first();
        //看看是否能找到该用户
        if ($data == null || empty($data)) {
            return 0;//用户没有报名
        }
        //判断用户拥有的旗子数量是否大于5
        if ($data['flag_num'] >= 5) {
            $medal = new Medal();
            $rewards = $medal->select('rewards')->find($medal_id);
            $integral = $res['integral'] + $rewards['rewards'];
            if (empty($res['medal'])) {
                //如果为空,则直接加入
                $arr = ['0' => $medal_id];
            } else {
                //如果不为空,取出循环,然后将其加入
                $arr = $res['medal'];
                array_push($arr, $medal_id);
            }
            $arr = json_encode($arr);
            $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
            return $medal_id;
        }
        return 0;
    }

    /*
     * 毅力使者
     */
    public function perseverance($id, $medal_id)
    {
        $member = New Member();
        $res = $member->select('medal', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    //已经拥有此徽章,直接终端不再往下执行
                    return 0;
                }
            }
        }
        $res2 = New Perseverance();
        $data = $res2->select('punch_d')->where('member_id', $id)->first();
        //看看是否能找到该用户
        if ($data == null || empty($data)) {
            return 0;
        }
        //判断用户拥有的旗子数量是否大于5
        if ($data['punch_d'] >= 1) {
            $medal = new Medal();
            $rewards = $medal->select('rewards')->find($medal_id);
            $integral = $res['integral'] + $rewards['rewards'];
            if (empty($res['medal'])) {
                //如果为空,则直接加入
                $arr = ['0' => $medal_id];
            } else {
                //如果不为空,取出循环,然后将其加入
                $arr = $res['medal'];
                array_push($arr, $medal_id);
            }
            $arr = json_encode($arr);
            $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
            return $medal_id;
        }
        return 0;
    }

    /*
     * 健康达人
     */
    public function health($id, $medal_id)
    {
        $member = New Member();
        $res = $member->select('medal', 'integral')->where('id', $id)->first();
        if (!empty($res['medal'])) {
            //如果不为空,则判断是否拥有此徽章
            foreach ($res['medal'] as $k => $v) {
                if ($v == $medal_id) {
                    //已经拥有此徽章,直接终端不再往下执行
                    return 0;
                }
            }
        }
        //查询用户是否报名
        $res2 = new Health();
        $res2 = $res2->where('member_id', $id)->first();
        if (!empty($res2)) {
            $medal = new Medal();
            $rewards = $medal->select('rewards')->find($medal_id);
            $integral = $res['integral'] + $rewards['rewards'];
            if (empty($res['medal'])) {
                //如果为空,则直接加入
                $arr = ['0' => $medal_id];
            } else {
                //如果不为空,取出循环,然后将其加入
                $arr = $res['medal'];
                array_push($arr, $medal_id);
            }
            $arr = json_encode($arr);
            $member->select()->where('id', $id)->update(['medal' => $arr, 'integral' => $integral]);
            return $medal_id;
        }
        return 0;
    }

}
