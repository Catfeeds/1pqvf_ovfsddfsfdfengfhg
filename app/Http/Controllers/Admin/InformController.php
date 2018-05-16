<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inform;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class InformController extends Controller
{

    public function index()
    {
        return view('admin.inform.index');
    }

    public function create(Request $request)
    {
        return view('admin.inform.create');
    }

    /**
     * 系统消息提示——发给所有用户
     * @param Request $request
     * @param Inform $inform
     * @return array
     */
    public function store(Request $request, Inform $inform)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('editorValue', 'title');//inform_url
        $role = [
            'title' => 'required',
            'editorValue' => 'required',
        ];
        $message = [
            'title.required' => '标题不能为空！',
            'editorValue.required' => '内容不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调整数据结构
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
        unset($data['editorValue']);
        $data['inf_path'] = 1;//发给所有人
        $res = $inform->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Inform $inform)
    {
        if ($request->ajax()) {
            $data = $inform->select('id', 'content', 'title', 'push_time')->get();//inform_url
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

    public function edit(Inform $inform)
    {
        $data['informInfo'] = $inform;
        return view('admin.inform.edit', $data);
    }

    public function update(Request $request, Inform $inform)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('editorValue', 'title', 'old_inform_url');//inform_url
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
        $data['inf_path'] = 2;//定义发给所有人
        $res = $inform->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }

    }

    public function destroy($id)
    {
        $inform = new Inform();
        $inform = $inform->find($id);
        $res = $inform->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     * 手动发送到每一个用户
     */
    public function inform(Request $request, Inform $inform)
    {
        $id = $request->only('id');
        $res = $inform->where('id', $id['id'])->update(['push_time' => date('Y-m-d H:i:s', time())]);
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '失败！'];
        }
    }

    /*
     * 取消
     */
    public function inform2(Request $request, Inform $inform)
    {
        $id = $request->only('id');
        $res = $inform->where('id', $id['id'])->update(['push_time' => null]);
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '失败！'];
        }
    }

    /**
     * 提示新消息通知 启动时访问的接口
     */
    public function new_alerts(Request $request, Inform $inform)
    {
        $data = $request->only('member_id');
        $role = [
            'member_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //找出‘您有一条新评论’
        $in_path = $inform->select('to_member')->where('inf_path',2)->first()->toArray();
        //找出所有的系统通知 inf_path = 1 系统消息才存在push_time
        $res = $inform->select('read')->where('push_time', '!=', null)->get()->toArray();
        if (empty($res[0]) && empty($in_path['to_member']) ) {
            res(['status' => 2]);
        }
        //1.处理系统消息
        foreach ($res as $k => $v) {
            //查看用户是否在里面
            if (empty($v['read'])) {
                //本条消息,没有一个人鸟
                $data = ['status' => 1];// 1 为有新消息, 0 为空
                res($data);
            } else {
                $read = json_decode($v['read'], true);
                if (!in_array($data['member_id'], $read)) {
                    //用户在里面,代表已读,不在里面,则证明有新消息
                    $data = ['status' => 1];// 1：有新消息, 0 为空
                    res($data);
                }
            }
        }
        //2.处理‘您有一条新评论’ inf_path=2 如果在里面就是需要查看
        $path_arr = json_decode($in_path['to_member'], true);
        if(in_array($data['member_id'], $path_arr)){
            $data = ['status' => 1];// 1 有新消息
            res($data);
        }
        $data = ['status' => 2];// 2没有新消息, 0 为空
        res($data);
        //如果有新的,则返回1,没有则返回2
    }
    /**
     * 查看消息通知
     * 消息通知手动访问->被动()
     * @param Request $request
     * @param Inform $inform
     */
    public function see_alerts(Request $request, Inform $inform)
    {
        //将所有信息展示出来,同时将用户的id存入已读
        $data = $request->only('member_id');
        $role = [
            'member_id' => 'exists:member,id',
        ];
        $message = [
            'member_id.exists' => '用户不存在！',
        ];
//        $validator = Validator::make($data, $role, $message);
//        //如果验证失败,返回错误信息
//        if ($validator->fails()) {
//            res(null, $validator->messages()->first(), 'fail', 101);
//        }
        //1.找出所有的系统通知$arr
        $res = $inform->select('id', 'title', 'push_time', 'content', 'read')->where('push_time', '!=', null)->get();
        $arr = [];
        foreach ($res as $k => $v) {
            //将content修改
            $content = json_decode($v['content'], true);
            $arr[] = [
                'title' => $v['title'],
                'push_time' => format_date($v['push_time']),
                'content' => $content[0],
            ];
            //查看了,将当前用户的id存入
            if (empty($v['read'])) {
                //直接插入
                $read = [0 => $data['member_id']];
            } else {
                $read = json_decode($v['read']);
                //判断用户不在里面,则将用户压入
                if (!in_array($data['member_id'], $read)) {
                    array_push($read, $data['member_id']);
                }
            }
            $read = json_encode($read, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $inform->where('id', $v['id'])->update(['read' => $read]);
        }
        //2.查询“您有新评论”
        $path_2has = $inform->select('to_member')->where('inf_path',2)->first();
        $path_has = json_decode($path_2has['to_member'], true);
        dump($path_has);
        $path_2arr = [];
        if(is_array($path_has)) {
            if (in_array($data['member_id'], $path_has)) {//有新评论
                //发送新评论消息
                $path_2arr[0] = [
                    'title' => '您有一条新评论',
                    'push_time' => null,
                    'content' => null,
                ];
                //查看后删除to_member中该用户id 取消提醒
                $aft_path = delByValue($path_has,$data['member_id']);//删除指定值
                $aft_path = json_encode(array_values($aft_path));//排序
                $inform->where('inf_path',2)->update(['to_member' => $aft_path]);
            }
        }
        if(empty($arr) && empty($path_2arr) ){
            res(null, '空空如也', 'success', 201);
        }
        //有新通知，拼接通知
        $arr_inf = array_merge($arr,$path_2arr);
        res($arr_inf);
    }

    /**
     * 新增'您有一条新评论'通知
     * 》系统自动调用并自动生成数据
     * @param Request $request to_member发给谁 inf_path信息分类=2：即您有一条新评论
     * @param Inform $inform
     * @return array
     */
    public function msm_inf(Request $request, Inform $inform)
    {
        $data = $request->only('to_member');
        $ress = $inform->msm_inf($data['to_member']);//调用新增通知方法
        dump($ress);
    }




}
