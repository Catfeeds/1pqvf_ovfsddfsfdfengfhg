<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\ActMall;
use App\Models\Member;
use App\Models\Perseverance;
use App\Models\Health;
use App\Models\TakeFlag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class ActivityController extends Controller
{

    public function index()
    {
        return view('admin.activity.index');
    }

    public function create(ActMall $actMall)
    {
        $data['actmallInfo'] = $actMall->select('id', 'note', 'act_num')->get();
        return view('admin.activity.create', $data);
    }

    public function store(Request $request, Activity $activity, ActMall $actMall)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('actMall_id', 'kucun', 'actMall_num', 'title', 'img_url', 'note', 'editorValue', 'start_at', 'end_at', 'top_img_url');
        $role = [
            'actMall_id' => 'required',
            'kucun' => 'required',
            'actMall_num' => 'required|integer',
            'title' => 'required',
            'img_url' => 'required|image',
            'top_img_url' => 'required|image',
            'note' => 'required',
            'editorValue' => 'nullable',
            'start_at' => 'required|date',
            'end_at' => 'required|after_or_equal:start_at',
        ];
        $message = [
            'actMall_id.required' => '奖品选择错误！',
            'kucun.required' => '库存不能为空！',
            'actMall_num.required' => '奖品数量不能为空！',
            'actMall_num.integer' => '奖品数量必须大于1！',
            'title.integer' => '活动标题不能为空！',
            'img_url.required' => '活动封面不能为空！',
            'top_img_url.required' => '顶部图不能为空！',
            'img_url.image' => '活动封面图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'top_img_url.image' => '顶部图片格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'note.required' => '活动简介不能为空',
            'start_at.required' => '开始时间不能为空',
            'start_at.date' => '时间格式不正确',
            'end_at.required' => '结束时间不能为空',
            'end_at.after_or_equal' => '结束时间不能在开始时间之前',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
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
        }
        if (!empty($data['top_img_url'])) {
            $res = uploadpic('top_img_url', 'uploads/img_url');//
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
            $data['top_img_url'] = $res; //把得到的地址给picname存到数据库
        }
        //调整库存
        if ($data['kucun'] <= 0) {
            return ['status' => 'fail', 'msg' => '该奖品库存不足'];
        }
        if ($data['actMall_num'] > $data['kucun']) {
            return ['status' => 'fail', 'msg' => '库存不足'];
        }
        //调整时间
        if (!empty($data['start_at'])) {
            if ($data['start_at'] == '1970-01-01 00:00:00' || $data['end_at'] == '1970-01-01 00:00:00') {
                return ['status' => 'fail', 'msg' => '时间格式不合法'];
            }
            $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));
            $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));
        }
        //对富文本进行处理
        $data['editorValue'] = str_replace('<p>', '', $data['editorValue']);
        $data['editorValue'] = str_replace('&nbsp;', '', $data['editorValue']);
        $data['editorValue'] = str_replace('<br/>', '</p>', $data['editorValue']);
        $data['editorValue'] = str_replace(' ', '', $data['editorValue']);
        $data['editorValue'] = explode('</p>', $data['editorValue']);
        if (count($data['editorValue'])) {
            foreach ($data['editorValue'] as $k => $v) {
                if ($v == '' || $v == ' ') {
                    unset($data['editorValue'][$k]);
                }
            }
        }
        $data['content'] = json_encode($data['editorValue'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        //入库
        $res = $activity->create($data);
        if ($res->id) {
            //活动一旦生成,对应的奖品数量应该先减少,防止超发
            $re = $actMall->where(['id' => $data['actMall_id']])->select('act_num')->first();
            if ($data['actMall_num'] > $re->act_num) {
                return ['status' => 'fail', 'msg' => '添加失败,库存超出'];
            } else {
                $num = $re->act_num;
                $num = $num - $data['actMall_num'];
                $actMall->where(['id' => $data['actMall_id']])->select()->update(['act_num' => $num]);
            }
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Activity $activity)
    {
        if ($request->ajax()) {
            // 查询奖品名称
            $data = $activity->with('actmall')->select('id', 'note', 'title', 'img_url', 'content', 'actMall_id', 'actMall_num', 'start_at', 'end_at', 'man_num', 'top_img_url')->get();
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

    public function edit(Activity $activity, ActMall $actMall)
    {
        $data['actmallInfo'] = $actMall->select('id', 'note', 'act_num')->get();
        $data['activityInfo'] = $activity;
        return view('admin.activity.edit', $data);
    }

    public function update(Request $request, Activity $activity, ActMall $actMall)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('actMall_id', 'kucun', 'actMall_num', 'old_actmall_id', 'old_actMall_num', 'title', 'img_url', 'note', 'editorValue', 'start_at', 'end_at', 'top_img_url');
        $role = [
            'actMall_id' => 'required',
            'kucun' => 'required',
            'actMall_num' => 'required|integer',
            'old_actmall_id' => 'required',
            'old_actMall_num' => 'required',
            'img_url' => 'nullable|image',
            'top_img_url' => 'nullable|image',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|after_or_equal:start_at',
        ];
        $message = [
            'actMall_id.required' => '奖品不能为空！',
            'kucun.required' => '库存错误！',
            'actMall_num.required' => '奖品数量不能为空！',
            'actMall_num.integer' => '奖品数量必须是数字！',
            'old_actmall_id.required' => '奖品错误！',
            'old_actMall_num.required' => '数量错误！',
            'img_url.image' => '商品图片格式合法,必须是 jpeg、bmp、jpg、gif、gpeg、png格式！',
            'top_img_url.image' => '顶部图片格式合法,必须是 jpeg、bmp、jpg、gif、gpeg、png格式！',
            'start_at.date' => '时间格式不正确',
            'end_at.after_or_equal' => '结束时间不能在开始时间之前',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调整时间
        if (!empty($data['start_at'])) {
            if ($data['start_at'] == '1970-01-01 00:00:00' || $data['end_at'] == '1970-01-01 00:00:00') {
                return ['status' => 'fail', 'msg' => '时间格式不合法'];
            }
            $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));
            $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));
        }
        //数据调整
        if (!empty($data['actMall_num']) && $data['actMall_num'] < 0 || $data['actMall_num'] == 0) {
            $data['actMall_num'] = 1;
        }
        //判断奖品变更
        //1.1 奖品没变,
        if ($data['old_actmall_id'] == $data['actMall_id']) {
            //判断数量变没变  如果数量没变,则不理会,改变了,则判断是加,还是减,但是不能超出剩余的库存
            if ($data['actMall_num'] > $data['old_actMall_num']) {
                //如果新数量大于大于原始,则证明是加,取出加了多少
                //差值 等下商品库存需要利用差值减
                $num = $data['actMall_num'] - $data['old_actMall_num'];
                //总库存
                $nums = $data['kucun'] + $data['old_actMall_num'];
                //判断差值是否不大于 库存+原值
                if ($data['actMall_num'] > $nums) {
                    //超出了库存
                    return ['status' => 'fail', 'msg' => '库存不足'];
                } else {
                    //符合,更新活动商城商品库存
                    $re = $actMall->where(['id' => $data['actMall_id']])->select('act_num')->first();
                    //更新库存
                    $num = $re->act_num - $num;
                    $actMall->where(['id' => $data['actMall_id']])->select()->update(['act_num' => $num]);
                }

            } else if ($data['actMall_num'] < $data['old_actMall_num']) {
                //如果新数量小于原始数量,则是减,看看减了多少,
                $num = $data['old_actMall_num'] - $data['actMall_num'];
                //总库存
                $nums = $data['kucun'] + $data['old_actMall_num'];
                if ($data['actMall_num'] > $nums) {
                    //超出了库存
                    return ['status' => 'fail', 'msg' => '库存不足'];
                } else {
                    //符合,更新活动商城商品库存
                    $re = $actMall->where(['id' => $data['actMall_id']])->select('act_num')->first();
                    //将库存补回去
                    $num = $re->act_num + $num;
                    $actMall->where(['id' => $data['actMall_id']])->select()->update(['act_num' => $num]);
                }
            }
        } else if ($data['old_actmall_id'] != $data['actMall_id']) {
            //先将旧奖品的库存补上
            //原奖品数量
            $re = $actMall->where(['id' => $data['old_actmall_id']])->select('act_num')->first();
            //将库存补回去
            $num = $re->act_num + $data['old_actMall_num'];
            $actMall->where(['id' => $data['old_actmall_id']])->select()->update(['act_num' => $num]);
            // 然后将当前的数量从库存去掉
            //活动一旦生成,对应的奖品数量应该先减少,防止超发
            $re = $actMall->where(['id' => $data['actMall_id']])->select('act_num')->first();
            if ($data['actMall_num'] > $re->act_num) {
                return ['status' => 'fail', 'msg' => '添加失败,库存超出'];
            } else {
                $num = $re->act_num;
                $num = $num - $data['actMall_num'];
                $actMall->where(['id' => $data['actMall_id']])->select()->update(['act_num' => $num]);
            }
        }

        if (!empty($data['img_url'])) {
            $res = uploadpic('img_url', 'uploads/img_url');
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
            $ress = $activity->img_url;
            if (!empty($ress)) {
                unlink($ress);
            }
        }
        if (!empty($data['top_img_url'])) {
            $res = uploadpic('top_img_url', 'uploads/img_url');
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
            $data['top_img_url'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $activity->top_img_url;
            if (!empty($ress)) {
                unlink($ress);
            }
        }
        foreach ($data as $k => $v) {
            if (empty($v)) {
                unset($data[$k]);
            }
        }
        if (!empty($data['editorValue'])) {
            //对富文本进行处理
            $data['editorValue'] = str_replace('<p>', '', $data['editorValue']);
            $data['editorValue'] = str_replace('&nbsp;', '', $data['editorValue']);
            $data['editorValue'] = str_replace('<br/>', '</p>', $data['editorValue']);
            $data['editorValue'] = str_replace(' ', '', $data['editorValue']);
            $data['editorValue'] = explode('</p>', $data['editorValue']);
            if (count($data['editorValue'])) {
                foreach ($data['editorValue'] as $k => $v) {
                    if ($v == '' || $v == ' ') {
                        unset($data['editorValue'][$k]);
                    }
                }
            }
            $data['content'] = json_encode($data['editorValue'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        // 更新数据
        $res = $activity->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $activity = new Activity();
        $activity = $activity->find($id);
        $res = $activity->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     * 赛事接口 排行->赛事首页
     */
    public function activity(Request $request, Activity $activity)
    {
        //先查出所有活动(进行中,结束,活动封面,id)
        $res = $activity->orderBy('id', 'DESC')->select('end_at', 'id', 'img_url', 'title')->get();
        //返回  进行中(如果时间没超出=HOT),如果结束时间已经超出当前时间(END)
        $time = time();
        foreach ($res as $key => $val) {
            $arr[$key]['activity_id'] = $res[$key]['id'];
            $arr[$key]['img_url'] = $request->server('HTTP_HOST') . '/' . $res[$key]['img_url'];
        }
        foreach ($arr as $key => $val) {
            if (strtotime($res[$key]['end_at']) > $time) {
                //结束时间,大于当前时间
                $arr[$key]['status'] = 1;//1=进行中
            } else {
                $arr[$key]['status'] = 2;
            }
        }
        res($arr, '查询成功');
    }

    /**
     * 赛事接口 我的活动->赛事
     * 本接口只返回用户参与了的活动,没有参与的活动不会返回
     */
    public function my_activity(Request $request, Perseverance $perseverance, Health $health, Activity $activity, TakeFlag $takeFlag)
    {
        $data = $request->only('member_id');
        //根据用户的id,额外查询三个表中,需要返回的字段
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
        //判断用户有没有在这些活动子表中,如果没有,则不需要调整数据,直接返回(当前步数,签到天数等)
        $res1 = $health->select('total', 'activity_id', 'status')->where('member_id', $data['member_id'])->first();
        $res2 = $perseverance->select('punch_d', 'activity_id', 'status2')->where('member_id', $data['member_id'])->first();
        $res3 = $activity->select('end_at', 'id', 'img_url')->get();//得到所有的活动
        $res4 = $takeFlag->select('flag_num', 'activity_id', 'status')->where('member_id', $data['member_id'])->first();
        if (empty($res1['activity_id']) && empty($res2['activity_id']) && empty($res4['activity_id'])) {
            res(null, '用户没有参加任何赛事', 'success', 201);
        }
        $time = time();
        $arr = [];
        //用户至少参加了一个赛事
        foreach ($res3 as $key => $val) {

            if (!empty($res1['activity_id']) && $res3[$key]['id'] == 1) {//健康达人
                $flag = 1;
                $num = empty($res1['total']) ? 0 : $res1['total'];//总步数
                $states = $res1['status'];//达成状态
                $status = strtotime($res3[$key]['end_at']) > $time ? 1 : 2;//1为进行中,2为已结束
                if ($status === 2) {
                    //已结束,查看是否达成
                    $state = $states === 2 ? $states : 1;//==1代表已达成
                } else {
                    $state = 2;//未达成
                }
                $arr[] = [
                    'flag' => $flag,//夺旗先锋
                    'num' => $num,
                    'activity_id' => $res3[$key]['id'],
                    'img_url' => $request->server('HTTP_HOST') . '/' . $res3[$key]['img_url'],
                    'status' => $status,
                    'state' => $state,
                ];
            }
            if (!empty($res2['activity_id']) && $res3[$key]['id'] == 2) { //毅力使者
                $flag = 2;
                $num = empty($res2['punch_d']) ? 0 : $res2['punch_d'];//签到天数
                $states = $res2['status2'];
                $status = strtotime($res3[$key]['end_at']) > $time ? 1 : 2;//1为进行中,2为已结束
                if ($status === 2) {
                    //已结束,查看是否达成
                    $state = $states === 2 ? $states : 1;//==1代表已达成
                } else {
                    $state = 2;//未达成
                }
                $arr[] = [
                    'flag' => $flag,//夺旗先锋
                    'num' => $num,
                    'activity_id' => $res3[$key]['id'],
                    'img_url' => $request->server('HTTP_HOST') . '/' . $res3[$key]['img_url'],
                    'status' => $status,
                    'state' => $state,
                ];
            }
            //夺旗先锋
            if (!empty($res4['activity_id']) && $res3[$key]['id'] == 3) {
                $flag = 3;
                $num = empty($res4['flag_num']) ? 0 : $res4['flag_num'];//旗子数量
                $states = $res4['status'];
                $status = strtotime($res3[$key]['end_at']) > $time ? 1 : 2;//1为进行中,2为已结束
                if ($status === 2) {
                    //已结束,查看是否达成
                    $state = $states === 2 ? $states : 1;//==1代表已达成
                } else {
                    $state = 2;//未达成
                }
                $arr[] = [
                    'flag' => $flag,//夺旗先锋
                    'num' => $num,
                    'activity_id' => $res3[$key]['id'],
                    'img_url' => $request->server('HTTP_HOST') . '/' . $res3[$key]['img_url'],
                    'status' => $status,
                    'state' => $state,
                ];
            }
        }
        res($arr, '查询成功');
    }

    /*
     * 展示赛事详情页面
     */
    public function details(Request $request, Activity $activity, Health $health, Perseverance $perseverance, TakeFlag $takeFlag)
    {
        $data = $request->only('member_id', 'activity_id');
        $role = [
            'member_id' => 'required',
            'activity_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'activity_id.required' => '赛事id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        if ($data['activity_id'] == 1) {//当前活动为健康达人$health
            //当达到活动状态=达成的时候
            $res = $activity->with('actmall')->select('note', 'title', 'content', 'actMall_id', 'man_num', 'top_img_url')->where(['id' => $data['activity_id']])->first();
            $res2 = $health->select('total', 'status')->where(['member_id' => $data['member_id']])->first();
            //用户还没报名,无需返回我的步数
            if ($res2 == null) {
                //用户还没报名
                $res2['total'] = 0;
                $res2['status'] = 2;
                $status2 = 2;//报名状态
            } else {
                $res2['total'] = $res2['total'] == null ? 0 : $res2['total'];
                $res2['status'] = $res2['status'] == null ? 0 : $res2['status'];
                $status2 = 1;//报名状态 1= 已报名
            }
            $res['man_num'] = $res['man_num'] == null ? 0 : $res['man_num'];
            //数据调整
            $content = json_decode($res['content'], true);
            $result = [
                'type' => $data['activity_id'],//归属活动
                'activity_img' => $request->server('HTTP_HOST') . '/' . $res['top_img_url'],//赛事封面
                'note' => $res['note'],//赛事简介
                'content' => $content,//赛事说明,
                'title' => $res['title'],//赛事标题
                'prize' => $res['actmall']['note'],//奖品
                'prize_img' => $request->server('HTTP_HOST') . '/' . $res['actmall']['img_url'],//奖品图片
                'column1' => ['title' => '名额上限', 'num' => 3000],//本次参赛的名额上限
                'column2' => ['title' => '挑战人数', 'num' => $res['man_num']],//$res['man_num'],//参赛人数
                'column3' => ['title' => '我的步数', 'num' => $res2['total']],//$res2['total'],//用户的总步数
                'status' => $res2['status'],//活动达成的状态
                'status2' => $status2,//报名状态,1=已报名,2=未报名
            ];
            res($result);
        } elseif ($data['activity_id'] == 2) {//2代表活动为毅力使者时$perseverance
            $res = $activity->with('actmall')->select('note', 'title', 'top_img_url', 'content', 'actMall_id', 'man_num', 'actMall_num')->where(['id' => $data['activity_id']])->first();
            $res2 = $perseverance->select('total_steps', 'punch_d', 'status', 'status2')->where(['member_id' => $data['member_id']])->first();
            $time = date('Ymd', time());
            //如果在记录中有今天,则代表打卡了
            $status = json_decode($res2['status'], true);
            $sta = 2;  //今天是否打卡 1 = 已打卡      2 = 未打卡
            $status2 = 2;//未打卡
            if (!empty($res2['status'])) {
                // 不为空,判断有没有今天
                $falg = false;
                foreach ($status as $key => $val) {
                    //如果有今天
                    if ($time == $val) {
                        $falg = true;//有今天
                        $status2 = 1;//已打卡
                        break;
                    }
                }
                if ($falg == true) {
                    $sta = 1;
                }
            }
            if ($res2 == null) {
                //调用报名接口
                $this->enrol($data['member_id'], $data['activity_id']);
                //同时,打卡天数,总步数,打卡状态等全部重置
                $res2['total_steps'] = 0;
                $res2['punch_d'] = 0;
                $res2['status2'] = 2;//报名可能存在失败,因为不在活动时间内
                $sta = 2;
            }
            //判断是否已经领奖
            if ($res2['status2'] == 3) {
                $sta2 = 3;
            } else {
                if ($res2['punch_d'] >= 30) {
                    //满足30天
                    $sta2 = 1;

                } else {
                    $sta2 = 2;
                }
            }
            //数据调整
            $content = json_decode($res['content'], true);
            $total_steps = empty($res2['total_steps']) ? 0 : $res2['total_steps'];
            $sta = $sta == 1 ? '已打卡' : '未打卡';
            $data = [
                'type' => $data['activity_id'],//归属活动
                'activity_img' => $request->server('HTTP_HOST') . '/' . $res['top_img_url'],//赛事封面
                'note' => $res['note'],//赛事简介
                'content' => $content,//赛事说明,
                'title' => $res['title'],//赛事标题
                'prize' => $res['actmall']['note'],//奖品
                'prize_img' => $request->server('HTTP_HOST') . '/' . $res['actmall']['img_url'],//奖品图片
                'column1' => ['title' => '打卡状态', 'num' => $sta],//$sta,
                'column2' => ['title' => '坚持天数', 'num' => $res2['punch_d'] . '天'],//$res2['punch_d'],//打卡天数
                'column3' => ['title' => '我的步数', 'num' => $total_steps],//$res2['total_steps'],//我的步数
                'status' => $sta2,//活动达成的状态 1=完成,2=失败,3=已经领奖
                'status2' => $status2,//打卡状态, 1=已打卡,2=未打卡
            ];
            res($data);
        } elseif ($data['activity_id'] == 3) {//3表示当前活动为夺旗先锋时 $takeFlag
            $res = $takeFlag->select('id', 'flag_num', 'status')->where(['member_id' => $data['member_id']])->first();
            //  赛事封面图,赛事简介,赛事说明,赛事标题,报名人数,剩余天数,我的旗子,奖品图片,奖品note
            $res2 = $activity->with('actmall')->select('note', 'title', 'top_img_url', 'content', 'actMall_id', 'end_at', 'start_at')->where(['id' => $data['activity_id']])->first();
            //如果当前时间,是最后一天或者已经结束,剩余天数=0
            if (strtotime(date('Y-m-d')) >= strtotime($res2['end_at'])) {
                $time = 0;
                //此处进行排名修改,找出前三名,并修改状态
                $rows = $takeFlag->orderBy('flag_num', 'DESC')->select('member_id', 'flag_num')->limit(3)->get();
                foreach ($rows as $key => $val) {
                    $ids[] = $val['member_id'];
                }
                if (!empty($ids)) {
                    $temp_data1 = DB::update(DB::raw("update pq_takeFlag set status=1 where member_id in ($ids[0],$ids[1],$ids[2])"));
                    $temp_data2 = DB::update(DB::raw("update pq_takeFlag set status=2 where member_id not in ($ids[0],$ids[1],$ids[2])"));
                }
            } else {
                //否则,说明还没到结束时间,那么状态为3,时间为剩余天数
                $time = date('d', strtotime($res2['end_at']) - time());
                $time = $time > 0 ? $time : 0;
            }
            $behind = 0;
            //如果用户还没有报名/则无需返回我的旗子
            if ($res == null) {
                $falg_num = 0;
                $sta2 = 2;
                $status2 = 2;
            } else {
                $status2 = 1;//已报名
                //我的旗子数
                $falg_num = $res['flag_num'];
                //是否已经领奖
                if ($res['status'] == 3) {
                    $sta2 = 3;
                } else {
                    //如果剩余天数=0
                    if ($time == 0) {
                        //如果=0 说明活动在结算,或者已经过期
                        $ranking = 3; //要多少名
                        $rows = $takeFlag->orderBy('flag_num', 'DESC')->select('member_id', 'flag_num')->get();
                        $v = 1000;
                        foreach ($rows as $key => $val) {
                            if ($val['member_id'] == $data['member_id']) {
                                //找到了,看看是在第几
                                //用户的上一名
                                $v = $key + 1;//用户所在的名次
                                if ($v != 1) {
                                    //不等于第一名,说明上面还有人,看看上一名和他的旗子差距
                                    //上一名的旗子
                                    $behind = $rows[$key - 1]['flag_num'] - $rows[$key]['flag_num'];
                                }
                                break;
                            }
                        }
                        if ($v > $ranking) {
                            //在100名之外
                            $sta2 = 2;
                        } else {
                            $sta2 = 1;//在一百名之内,可以领奖
                            //此时,修改夺旗先锋的状态
                            $takeFlag->select()->where('member_id', $data['member_id'])->update(['status' => 1]);
                        }
                    } else {
                        $rows = $takeFlag->orderBy('flag_num', 'DESC')->select('member_id', 'flag_num')->get();
                        foreach ($rows as $key => $val) {
                            if ($val['member_id'] == $data['member_id']) {
                                //用户的上一名
                                if ($key + 1 != 1) {
                                    //不等于第一名,说明上面还有人,看看上一名和他的旗子差距
                                    //上一名的旗子
                                    $behind = $rows[$key - 1]['flag_num'] - $rows[$key]['flag_num'];
                                }
                                break;
                            }
                        }
                        $sta2 = 2;
                    }
                }
            }
            //数据调整
            $content = json_decode($res2['content'], true);
            $data = [
                'type' => $data['activity_id'],//归属活动
                'activity_img' => $request->server('HTTP_HOST') . '/' . $res2['top_img_url'],//赛事封面
                'note' => $res2['note'],//赛事简介
                'content' => $content,//赛事说明,
                'title' => $res2['title'],//赛事标题
                'prize' => $res2['actmall']['note'],//奖品
                'prize_img' => $request->server('HTTP_HOST') . '/' . $res2['actmall']['img_url'],//奖品图片
                'column1' => ['title' => '落后上一名', 'num' => $behind . '帜'],
                'column2' => ['title' => '剩余天数', 'num' => $time . '天'],
                'column3' => ['title' => '我的旗子', 'num' => $falg_num],
                'status' => $sta2,//活动达成的状态
                'status2' => $status2,//报名状态, 1=已报名,2=未报名
            ];
            res($data);
        } else {
            res(null, '没有该活动', 'fail', 100);
        }
    }

    /*
     *  自动报名接口--毅力使者
     */
    public function enrol($member_id, $activity_id)
    {
        $data = [
            'member_id' => $member_id,
            'activity_id' => $activity_id,//活动id
        ];
        $activity = new Activity();
        $res = $activity->select('start_at', 'end_at')->where('id', $data['activity_id'])->first();
        //如果不在活动时间内,则返回x,代表活动状态未达成
        $time1 = strtotime($res['start_at']);
        $time2 = strtotime($res['end_at']);
        $time3 = time();
        if ($time3 < $time1 || $time3 > $time2) {
            return 3;
        }
        $perseverance = new Perseverance();
        $res = $perseverance->create($data);
        if ($res->id) {
            //报名成功
            $activity = new Activity();
            $res = $activity->select('man_num')->where('id', $data['activity_id'])->first();
            if ($res == null) {
                $activity->select()->where('id', $data['activity_id'])->update(['man_num', 1]);
            } else {
                $n = $res['man_num'] + 1;
                $activity->select()->where('id', $data['activity_id'])->update(['man_num' => $n]);
            }
            return 1;
        }
        return 2;
    }

    /*
     * 赛事奖品领奖接口
     */
    public function exchange(Request $request, Activity $activity, Member $member, Health $health, Perseverance $perseverance, TakeFlag $takeFlag)
    {
        $data = $request->only('member_id', 'activity_id');
        $role = [
            'member_id' => 'required',
            'activity_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'activity_id.required' => '赛事id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询是否已经领奖了
        if ($data['activity_id'] == 1) {
            $res = $health->select('status')->where('member_id', $data['member_id'])->first();
        } elseif ($data['activity_id'] == 2) {
            $res = $perseverance->select('status2')->where('member_id', $data['member_id'])->first();
            $res['status'] = $res['status2'];
        } elseif ($data['activity_id'] == 3) {
            $res = $takeFlag->select('status')->where('member_id', $data['member_id'])->first();
        } else {
            res(null, '找不到该赛事', 'fail', 100);
        }
        if ($res['status'] == 3) {
            res(null, '已领奖,请勿重复领取', 'success', 202);
        }
        //判断该活动是否结束,只有结束了,才允许继续执行
        $num = $activity->select('actMall_num', 'end_at')->find($data['activity_id']);
        if (time() < strtotime($num['end_at'])) {
            res(null, '活动还没结束,暂不能领奖', 'fail', 104);
        }
        //如果已达成,才允许领奖
        if ($res['status'] == 1) {
            //先判断用户是否有地址,如果没有,则不允许领奖
            $row = $member->select('integral', 'address')->find($data['member_id']);
            if (!empty($row['address'])) {
                $add = json_decode($row['address'], true);
                $add = $add['address'];
                //判断收货地址是否完善,如果存在null,则需要完善
                $falg = false;
                if (array_key_exists('nickname', $add) && array_key_exists('phone', $add) && array_key_exists('province', $add) && array_key_exists('address', $add) && array_key_exists('zip_code', $add)) {
                    $falg = true;
                }
                if ($falg == false) {
                    res(null, '信息不完善', 'fail', 105);
                }
            } else {
                res(null, '领奖地址为空', 'fail', 105);
            }
            //查询奖品是否足够
            $num = $activity->select('actMall_num')->find($data['activity_id']);
            if ($num['actMall_num'] >= 1) {
                DB::transaction(function () use ($health, $data, $activity, $perseverance, $member, $takeFlag, $num, $row) {
                    //如果是健康达人或者夺旗先锋,返回积分,同时把领奖状态改为3
                    $add = json_encode($row['address'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if ($data['activity_id'] == 1) {
                        //将健康达人的积分清零,并将领奖状态改为3,并将收货地址赋值,并将领奖时间赋值
                        $res1 = $health->select()->where('member_id', $data['member_id'])->update(['cost' => '0', 'status' => '3', 'address' => $add, 'award' => date('Y-m-d H:i:s')]);
                        $integral = $row['integral'] + 300;
                        $res2 = $member->where(['id' => $data['member_id']])->update(['integral' => $integral]);
                    } elseif ($data['activity_id'] == 3) {
                        $res1 = $takeFlag->select()->where('member_id', $data['member_id'])->update(['cost' => '0', 'status' => '3', 'address' => $add, 'award' => date('Y-m-d H:i:s')]);//返还积分状态修改为已领取
                        $integral = $row['integral'] + 200;
                        $res2 = $member->where(['id' => $data['member_id']])->update(['integral' => $integral]);
                    } else {
                        $res1 = $perseverance->select()->where('member_id', $data['member_id'])->update(['status2' => '3', 'address' => $add, 'award' => date('Y-m-d H:i:s')]);//返还积分状态修改为已领取
                        $res2 = true;
                    }
                    $res3 = $activity->select()->where('id', $data['activity_id'])->update(['actMall_num' => $num['actMall_num'] - 1]);//库存-1
                    if ($res1 && $res2 && $res3) {
                        DB::commit();
                        res(null, '领取成功', 'success', 200);
                    } else {
                        DB::rollback();//事务回滚
                        res(null, '网络繁忙', 'fail', 100);
                    }
                });
            } else {
                res(null, '奖品不足', 'fail', 100);
            }
        }
        res(null, '目标未达成', 'fail', 109);
    }

    /**
     * 赛事报名接口
     */
    public function enrols(Request $request, Health $health, TakeFlag $takeFlag, Member $member, Activity $activity)
    {
        //判断是健康达人还是夺旗先锋
        $data = $request->only('member_id', 'activity_id');
        $role = [
            'member_id' => 'required',
            'activity_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'activity_id.required' => '赛事id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        if ($data['activity_id'] == 1) {
            //健康达人
            $res = $health->where(['member_id' => $data['member_id']])->first();
            if ($res != null) {
                res(null, '您已报名', 'success', 202);
            }
            //查询名额是否上限和是否在活动时间内
            $activity_num = $activity->select('man_num', 'start_at', 'end_at')->where(['id' => $data['activity_id']])->first();
            $time1 = strtotime($activity_num['start_at']);
            $time2 = strtotime($activity_num['end_at']);
            $time3 = time();
            if ($time3 < $time1 || $time3 > $time2) {
                res(null, '不在活动时间内', 'fail', 104);
            } elseif ($activity_num['man_num'] >= 3000) {
                res(null, '名额已满', 'fail', 105);
            }
            //查询并返回
            $res = $member->select('integral')->find($data['member_id']);
            if ($res == null) {
                res(null, '用户不存在', 'fail', 101);
            } else if ($res['integral'] == null || $res['integral'] < 300) {
                res(null, '报名积分不足,本次活动报名所需300积分', 'fail', 105);
            }
            //报名成功,先扣掉用户的积分,然后将积分先存起来
            DB::transaction(function () use ($health, $member, $data, $activity_num, $activity, $res) {
                $integral = $res['integral'] - 300;
                $res1 = $member->where(['id' => $data['member_id']])->update(['integral' => $integral]);
                //数据调整
                $data['cost'] = 300;
                $data['status'] = 2;
                $data['total'] = 0;
                $res2 = $health->create($data);
                //成功,则活动表参赛人数+1
                $num = $activity_num['man_num'] + 1;
                $res3 = $activity->where(['id' => $data['activity_id']])->update(['man_num' => $num]);
                //健康达人表入库
                if ($res1 && $res2->id && $res3) {
                    DB::commit();
                    res(null, '报名成功');
                } else {
                    DB::rollback();//事务回滚
                    res(null, '报名失败', 'fail', 100);
                }
            });
            //健康达人结束

        } else if ($data['activity_id'] == 3) {
            //夺旗先锋
            $res = $takeFlag->where(['member_id' => $data['member_id']])->first();
            if ($res != null) {
                res(null, '您已报名', 'success', 202);
            }
            //查询是否在活动时间内
            $activity_num = $activity->select('man_num', 'start_at', 'end_at')->where(['id' => $data['activity_id']])->first();
            $time1 = strtotime($activity_num['start_at']);
            $time2 = strtotime($activity_num['end_at']);
            $time3 = time();
            if ($time3 < $time1 || $time3 > $time2) {
                res(null, '不在活动时间内', 'fail', 104);
            }
            //查询并返回
            $res = $member->select('integral')->find($data['member_id']);
            if ($res == null) {
                res(null, '用户不存在', 'fail', 101);
            } else if ($res['integral'] == null || $res['integral'] < 200) {
                res(null, '报名积分不足,本次活动报名所需200积分', 'fail', 105);
            }
            //报名成功,先扣掉用户的积分,然后将积分先存起来
            DB::transaction(function () use ($takeFlag, $member, $data, $activity_num, $activity, $res) {
                $integral = $res['integral'] - 200;
                $res1 = $member->where(['id' => $data['member_id']])->update(['integral' => $integral]);
                //数据调整
                $data['cost'] = 200;
                $data['status'] = 2;
                $data['flag_num'] = 0;
                $res2 = $takeFlag->create($data);
                //成功,则活动表参赛人数+1
                $num = $activity_num['man_num'] + 1;
                $res3 = $activity->where(['id' => $data['activity_id']])->update(['man_num' => $num]);
                //健康达人表入库
                if ($res1 && $res2->id && $res3) {
                    DB::commit();
//                    $data = [ 'man_num'=> $num];
                    res(null, '报名成功');
                } else {
                    DB::rollback();//事务回滚
                    res(null, '报名失败', 'fail', 100);
                }
            });
        } else {
            res(null, '该赛事id没有对应的活动', 'fail', 101);
        }
    }

}
