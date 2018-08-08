<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use App\Models\Dynamic;
use App\Models\Inform;
use App\Models\Member;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class CommentController extends Controller
{
    public function index()
    {
        return view('admin.comment.index');
    }

    /**
     * 评论列表(管理员)
     * @param Request $request
     * @param Comment $comment
     * @return array
     */
    public function ajax_list(Request $request, Comment $comment)
    {

        if ($request->ajax()) {
            $data = $comment->with('member')->with('dynamic')->with('topic')->select('id', 'dy_id', 'to_id', 'member_id', 'content', 'created_at','parent_id')->get();
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

    /**
     * 管理员删除
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function destroy($id)
    {
        $comment = new Comment();
        //查询该评论的回复
        $re_re =  $comment->where('parent_id',$id)->get();

        if(!$re_re->count()){//如果没有回复直接删除
            //删除该条评论
            $res = $comment->where('id',$id)->delete();
        }else{//删除该条评论下的所有回复
            //获取所有评论信息并转化为数组
            $all_com = $comment->select('id','parent_id')->get();
            //获取该评论的所有后代
            $re_re_ids = $comment::getChildrenId($all_com,$id);
            //删除该评论的所有后代及本身
            $del_ids = array_merge($re_re_ids,[$id]);
            $res = $comment->whereIn('id',$del_ids)->delete();
        }
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     * 发表评论
     * @param Request $request
     * member_id当前用户的id；record_id被评论的记录的id；subject_catename话题的分类名称,如果是动态为空(null),值为1 ,如果是话题(不为空)则为2；content评论的内容;
     * $comment为动态评论表
     * @param Comment $comment
     */
    public function comments(Request $request, Comment $comment,Inform $inform,Dynamic $dynamic,Topic $topic)
    {
        $data = $request->only("member_id", 'record_id', 'subject_catename', 'content');

        //需要知道评论人,被评论的是动态还是话题,他的id,评论的内容
        if ($data['subject_catename'] == 1) {//动态

            $role = [
                'member_id' => 'exists:member,id',
                'record_id' => 'exists:dynamic,id',
                'content' => 'required',
            ];
            $message = [
                'member_id.exists' => '用户请求非法',
                'record_id.exists' => '不存在该动态',
                'content.required' => '评论内容不能为空！',
            ];
            //过滤信息
            $validator = Validator::make($data, $role, $message);
            if ($validator->fails()) {
                res(null, $validator->messages()->first(), 'fail', 101);
            }

            //为空,代表他是动态
            $arr['dy_id'] = $data['record_id'];
            //1. 获取该博客博主的id 即$b_ids['member_id']
            $b_ids = $dynamic->select('member_id')->where('id',$data['record_id'])->first();
        } else {

            $role = [
                'member_id' => 'exists:member,id',
                'record_id' => 'exists:topic,id',
                'content' => 'required',
            ];
            $message = [
                'member_id.exists' => '用户请求非法',
                'record_id.exists' => '不存在该话题',
                'content.required' => '评论内容不能为空！',
            ];
            //过滤信息
            $validator = Validator::make($data, $role, $message);
            if ($validator->fails()) {
                res(null, $validator->messages()->first(), 'fail', 101);
            }

            //不为空,代表是话题
            $arr['to_id'] = $data['record_id'];
            //1. 获取该博客博主的id 即$b_ids['member_id']
            $b_ids = $topic->select('member_id')->where('id',$data['record_id'])->first();
        }
        $arr['member_id'] = $data['member_id'];
        $arr['content'] = $data['content'];
        $res = $comment->create($arr);
        if ($res) {
            $comment->where('id',$res['id'])->update(['first_branch_id' => $res['id']]);//一级评论的first_branch_id是他本身
            //通知该文章所有者
            if($inform->msm_inf($b_ids['member_id'])){
                res(null, '成功');
            }
            res(null, '成功，未知错误');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 查询删除或回复的权限
     * @param Request $request
     * member_id当前用户的id；；content评论的内容;parent_id回复id
     * @param Comment $comment
     */
    public function chk_comments(Request $request, Comment $comment, Inform $inform)
    {
        $data = $request->only("member_id", 'parent_id','content');
        $role = [
            'member_id' => 'required',
            'parent_id' => 'required',
            'content' => 'required',
        ];
        $message = [
            'member_id.required' => '用户不能为空',
            'parent_id.required' => '回复的评论不能为空',
            'content.required' => '评论内容不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //获取当前评论的所有者
        $p_mid = $comment->select('member_id')->where('id',$data['parent_id'])->first();
        //不能评论自己的评论
        if($p_mid['member_id'] == $data['member_id']){
            res(['re_status'=>'0'], '可删除不能回复','success',200);//没有回复权限
        }else{
            res(['re_status'=>'1'], '可回复不能删除','success',200 );//可以回复
        }

    }

    /**
     * 回复评论
     * @param Request $request
     * member_id当前用户的id；；content评论的内容;parent_id回复id
     * @param Comment $comment
     */
    public function re_comments(Request $request, Comment $comment, Inform $inform)
    {
        $data = $request->only("member_id", 'parent_id','content');
        $role = [
            'member_id' => 'exists:member,id',
            'parent_id' => 'exists:comment,id',
            'content' => 'required',
        ];
        $message = [
            'member_id.exists' => '用户请求非法',
            'parent_id.exists' => '请求非法',
            'content.required' => '评论内容不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //获取被评论的所有者及所属博文id
        $p_mid = $comment->select('member_id','to_id','dy_id','first_branch_id')->where('id',$data['parent_id'])->first();
        //不能评论自己的评论
        if($p_mid['member_id'] == $data['member_id']){
            res(['re_status'=>'0'], '不能评论自己的评论','fail',101 );//没有回复权限
        }
        //找出parent_id的member_id写入p_mid
        $data['p_mid'] = $p_mid['member_id'];
        //记录哪条博文下的评论
        $data['to_id'] = $p_mid['to_id'];
        $data['dy_id'] = $p_mid['dy_id'];
        $data['first_branch_id'] = $p_mid['first_branch_id'];
        $res = $comment->create($data);
        if ($res) {
            //通知被评论者
            if($inform->msm_inf($p_mid['member_id'])){
                res($res['id'], '成功');
            }
            res(null, '成功，未知故障');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 管理员强制删除某条评论-真实删除（慎用）
     * @param Request $request
     * @param Comment $comment
     * @throws \Exception
     */
    public function pun_comments(Request $request, Comment $comment)
    {
        $data = $request->only("admin_id", 'comment_id');
        $role = [
            'admin_id' => 'exists:admin,id',
            'comment_id' => 'exists:comment,id',
        ];
        $message = [
            'admin_id.exists' => '用户请求非法',
            'comment_id.exists' => '请求不合法',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询该评论的回复
        $re_re =  $comment->where('parent_id',$data['comment_id'])->get();

        if(!$re_re->count()){//如果没有回复直接删除
            //删除该条评论
            $res = $comment->where('id',$data['comment_id'])->delete();

        }else{//删除该条评论下的所有回复
            //获取所有评论信息并转化为数组
            $all_com = $comment->select('id','parent_id')->get();
            //获取该评论的所有后代
            $re_re_ids = $comment::getChildrenId($all_com,$data['comment_id']);
            //删除该评论的所有后代及本身
            $id = array($data['comment_id']);
            $del_ids = array_merge($re_re_ids,$id);
            $res = $comment->whereIn('id',$del_ids)->delete();
        }
        if ($res) {
            res(null, '成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /** * * 博主或评论者删除某条评论及其下的所有评论
     * * $data['当前评论的id']
     * @param Request $request
     * @param Comment $comment
     * @param Dynamic $dynamic
     * @param Topic $topic
     * @throws \Exception
     */
    public function del_comments(Request $request, Comment $comment,Dynamic $dynamic,Topic $topic)
    {
        $data = $request->only("member_id", 'comment_id','subject_path','record_id');
        $role = [
            'member_id' => 'exists:member,id',
            'comment_id' => 'exists:comment,id',
            'record_id' => 'required'
        ];
        $message = [
            'member_id.exists' => '用户请求非法',
            'comment_id.exists' => '请求不合法',
            'record_id.required' => '博客不合法'
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //1.获取该博客博主的member_id   即$b_ids['member_id']
        if ($data['subject_path'] == 1) {
            //为空,代表他是动态 动态id为$arr['dy_id'] = $data['record_id'];
            //获取该博客博主的member_id   即$b_ids['member_id']
            $b_ids = $dynamic->select('member_id')->where('id',$data['record_id'])->first();

        } else {
            //不为空,代表是话题$arr['to_id'] = $data['record_id'];
            //1. 获取该博客博主的id 即$p_ids['member_id']
            $b_ids = $topic->select('member_id')->where('id',$data['record_id'])->first();
        }
        //2.获取评论所有者id $tid['member_id']
        $tid = $comment->select('member_id')->where('id',$data['comment_id'])->first();
        //3.如果是评论者或者博主可以删除，反之不能删除
        if ($data['member_id'] == ($b_ids['member_id']) || $tid['member_id'] == $data['member_id'] ){
            //找出一级评论级其下的所有子孙后代
            $all_com = $comment->select('id','parent_id')->get();//数据库所有评论id
            $id_arr = $comment->getChildrenId($all_com, $data['comment_id']);
            //查询该评论的后代
            if(!count($id_arr)){//如果没有回复直接删除
            $res = $comment->where('id',$data['comment_id'])->delete();
            }else{//删除该条评论下的所有回复
                $id = array($data['comment_id']);
                $del_ids = array_merge($id_arr,$id);
            $res = $comment->whereIn('id',$del_ids)->delete();
            }

            if ($res) {
                res(null, '成功');
            }
            res(null, '失败', 'fail', 100);

        }else{
            res(1, '您无权限删除该评论', 'fail', 100);
        }
    }

    /**
     * 查询某个动态或者话题的所有评论
     * subject_path 类别： 1动态，2话题
     * @param Request $request
     * @param Comment $comment
     */
    public function query_comment(Request $request, Comment $comment)
    {
        $data = $request->only("member_id", 'record_id', 'subject_path', 'content');
        $role = [
            'member_id' => 'exists:member,id',
            'record_id' => 'required',//被查询的 动态或者话题 的id
            'subject_path' => 'required|between:1,3'
        ];
        $message = [
            'member_id.exists' => '用户请求非法',
            'record_id.required' => '被评论的记录的id不能为空！',
            'subject_path.required' => '请求条件缺失',
            'subject_path.between' => '请求不合法',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        #1.判断当前查询动态评论还是话题评论，获取博客下的所有一级评论:$p_ids
        if ($data['subject_path'] == 1) {//为空,代表他是动态 动态id为$arr['dy_id'] = $data['record_id'];
            $arr = $comment
                ->select('comment.id','comment.member_id','comment.created_at','a.nickname','comment.parent_id','comment.p_mid','b.nickname AS p_m_name','comment.content','comment.created_at','a.avatar','b.avatar AS p_avatar','comment.first_branch_id')
                ->where('dy_id',$data['record_id'])
                ->leftjoin('member AS a','a.id','=','comment.member_id')
                ->leftjoin('member AS b','b.id','=','comment.p_mid')
                ->orderBy('id')->get();

        } else {//不为空,代表是话题$arr['to_id'] = $data['record_id'];
            $arr = $comment
                ->select('comment.id','comment.member_id','comment.created_at','a.nickname','comment.parent_id','comment.p_mid','b.nickname AS p_m_name','comment.content','comment.created_at','a.avatar','b.avatar AS p_avatar','comment.first_branch_id')
                ->where('to_id',$data['record_id'])
                ->leftjoin('member AS a','a.id','=','comment.member_id')
                ->leftjoin('member AS b','b.id','=','comment.p_mid')
                ->orderBy('id')->get();
        }
        $arr=$arr->groupBy('first_branch_id');
        dump(obj_arr($arr));
        res($arr);




//        #1.判断当前查询动态评论还是话题评论，获取博客下的所有一级评论:$p_ids
//        if ($data['subject_path'] == 1) {
//            //为空,代表他是动态 动态id为$arr['dy_id'] = $data['record_id'];
//            $p_ids = $comment->select('id')->where('dy_id',$data['record_id'])->get();
//        } else {
//            //不为空,代表是话题$arr['to_id'] = $data['record_id'];
//            $p_ids = $comment->select('id')->where('to_id',$data['record_id'])->get();
//        }
//        #2.找出数据库中评论的所有后代及本身
//        $all_com = $comment->select('id','parent_id')->get();
//        $id_arr = $comment->getChildrenIds($all_com, $p_ids);
////        dump($id_arr);
//        #3.找出所有评论并排序
//        if(count($id_arr)){
//            $arr = $comment
//                ->select('comment.id','comment.member_id','comment.created_at','a.nickname','comment.parent_id','comment.p_mid','b.nickname AS p_m_name','comment.content','comment.created_at','a.avatar','b.avatar AS p_avatar','comment.first_branch_id')
//                ->whereIn('comment.id',$id_arr)
//                ->leftjoin('member AS a','a.id','=','comment.member_id')
//                ->leftjoin('member AS b','b.id','=','comment.p_mid')
//                ->get();
//            res($arr);
//        }else{
//            res(null);
//        }
    }

    /**
     * 查询某条评论下的所有回复
     * @param Request $request
     * @param Comment $comment
     */
    public function hot_comment(Request $request, Comment $comment)
    {
        $data = $request->only("member_id", 'comment_id');
        $role = [
            'member_id' => 'required',
            'comment_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户请求非法',
            'comment_id.required' => '请求不合法',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //获取所有评论信息
        $all_com = $comment->select('id','parent_id')->get();
        //获取该评论所有后代并输出的一维数组,包含其本身
        $th_ids = array_merge([$data['comment_id']],$comment::getChildrenId($all_com,$data['comment_id']));
        $cmt_arr = $comment->select('*')->whereIn('id',$th_ids)->get();
        res($cmt_arr);
    }



}
