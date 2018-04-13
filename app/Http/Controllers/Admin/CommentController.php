<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class CommentController extends Controller
{
    public function index()
    {
        return view('admin.comment.index');
    }

    public function ajax_list(Request $request, Comment $comment)
    {

        if ($request->ajax()) {
            $data = $comment->with('member')->with('dynamic')->with('topic')->select('id', 'dy_id', 'to_id', 'member_id', 'content', 'created_at')->get();
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

    public function edit(Comment $comment)
    {
        $data['commentInfo'] = $comment;
        return view('admin.comment.edit', $data);
    }

    public function update(Request $request, Comment $comment)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('editorValue');
        //后台只允许修改评论的内容
        $role = [
            'editorValue' => 'required',
        ];
        $message = [
            'editorValue.required' => '评论内容不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        $data['content'] = $data['editorValue'];
        // 更新数据
        $res = $comment->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $comment = new Comment();
        $comment = $comment->find($id);
        $res = $comment->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /*
     * 评论
     */
    public function comments(Request $request, Comment $comment)
    {
        $data = $request->only("member_id", 'record_id', 'subject_catename', 'content');
        $role = [
            'member_id' => 'required',
            'record_id' => 'required',//被评论的记录的id
            'content' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'record_id.required' => '被评论的记录的id不能为空！',
            'content.required' => '评论内容不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //需要知道评论人,被评论的是动态还是话题,他的id,评论的内容
        if ($data['subject_catename'] == 1) {
            //为空,代表他是动态
            $arr['dy_id'] = $data['record_id'];
        } else {
            //不为空,代表是话题
            $arr['to_id'] = $data['record_id'];
        }
        $arr['member_id'] = $data['member_id'];
        $arr['content'] = $data['content'];
        $res = $comment->create($arr);
        if ($res) {
            res(null, '成功');
        }
        res(null, '失败', 'fail', 100);
    }
}
