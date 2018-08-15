<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class FeedbackController extends Controller
{
    public function index()
    {
        return view('admin.feedback.index');
    }

    public function create(Member $member)
    {
        $data['feedbackInfo'] = $member->select('id', 'nickname')->get();
        return view('admin.feedback.create', $data);
    }

    public function ajax_list(Request $request, Feedback $feedback)
    {

        if ($request->ajax()) {
            $data = $feedback->with('member')->select('id', 'inform_url', 'cation', 'member_id', 'content', 'contact', 'handling', 'created_at')->get();
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

    public function edit(Feedback $feedback)
    {
        $data['feedbackInfo'] = $feedback;
        return view('admin.feedback.edit', $data);
    }

    public function update(Request $request, Feedback $feedback)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('handling');
        $res = $feedback->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $feedback = new Feedback();
        $feedback = $feedback->find($id);
        $res = $feedback->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     * 意见反馈
     */
    public function create_feedback(Request $request, Feedback $feedback)
    {
        $data = $request->only("member_id", 'cation', 'content', 'contact', 'img1', 'img2', 'img3', 'img4');
        $role = [
            'member_id' => 'required',
            'cation' => 'required',
            'content' => 'required',
            'img1' => 'nullable|image',
            'img2' => 'nullable|image',
            'img3' => 'nullable|image',
            'img4' => 'nullable|image',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'cation.required' => '反馈类型不能为空！',//1:功能建议2bug提交3商家问题
            'content.required' => '评价内容不能为空！',
            'img1.image' => '图片1格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'img2.image' => '图片2格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png2！',
            'img3.image' => '图片3格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png3！',
            'img4.image' => '图片3格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png3！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //处理图片 只要有一张图片,则进入
        if (!empty($data['img1']) || !empty($data['img2']) || !empty($data['img3']) || !empty($data['img4'])) {
            if (!empty($data['img1'])) {
                $res = uploadpic('img1', 'uploads/img_url/feedback/'.date('Y-m-d'));//
                switch ($res) {
                    case 1:
                        res(null, '图片上传失败', 'fail', 100);
                    case 2:
                        res(null, '图片不合法', 'fail', 100);
                    case 3:
                        res(null, '图片后缀不对', 'fail', 100);
                    case 4:
                        res(null, '图片储存失败', 'fail', 100);
                }
                $arr[] = $res;
            }
            if (!empty($data['img2'])) {
                $res = uploadpic('img2', 'uploads/img_url/feedback/'.date('Y-m-d'));//
                switch ($res) {
                    case 1:
                        res(null, '图片上传失败', 'fail', 100);
                    case 2:
                        res(null, '图片不合法', 'fail', 100);
                    case 3:
                        res(null, '图片后缀不对', 'fail', 100);
                    case 4:
                        res(null, '图片储存失败', 'fail', 100);
                }
                $arr[] = $res; //把得到的地址给picname存到数据库
            }
            if (!empty($data['img3'])) {
                $res = uploadpic('img3', 'uploads/img_url/feedback/'.date('Y-m-d'));//
                switch ($res) {
                    case 1:
                        res(null, '图片上传失败', 'fail', 100);
                    case 2:
                        res(null, '图片不合法', 'fail', 100);
                    case 3:
                        res(null, '图片后缀不对', 'fail', 100);
                    case 4:
                        res(null, '图片储存失败', 'fail', 100);
                }
                $arr[] = $res; //把得到的地址给picname存到数据库
            }
            if (!empty($data['img4'])) {
                $res = uploadpic('img4', 'uploads/img_url/feedback/'.date('Y-m-d'));//
                switch ($res) {
                    case 1:
                        res(null, '图片上传失败', 'fail', 100);
                    case 2:
                        res(null, '图片不合法', 'fail', 100);
                    case 3:
                        res(null, '图片后缀不对', 'fail', 100);
                    case 4:
                        res(null, '图片储存失败', 'fail', 100);
                }
                $arr[] = $res; //把得到的地址给picname存到数据库
            }
            //能进到这里,说明至少有一张图片
            $data['inform_url'] = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        //数据调整
        $data['handling'] = 2;
        $res = $feedback->create($data);
        if ($res->id) {
            res(null, '成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     *   反馈记录
     */
    public function opinion_feedback_record(Request $request, Feedback $feedback)
    {
        $data = $request->only("member_id", 'cation', 'content', 'contact', 'img1', 'img2', 'img3', 'img4');
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
        $res1 = $feedback->where('member_id', $data['member_id'])->select('created_at', 'cation', 'content')->get();
        if (empty($res1[0])) {
            res(null, '记录为空', 'success', 201);
        }
        foreach ($res1 as $k => $v) {
            $time = format_date($v['created_at']);
            $arr[] = [
                'cation' => $v['cation'],
                'time' => $time,
                'content' => $v['content']
            ];
        }
        res($arr);
    }

}
