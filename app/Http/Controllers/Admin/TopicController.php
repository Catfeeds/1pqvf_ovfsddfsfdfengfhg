<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Comment;
use App\Models\Member;
use App\Models\Subject;
use App\Models\Topic;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class TopicController extends Controller
{

    public function index()
    {
        return view('admin.topic.index');
    }

    /**
     *  添加话题
     */
    public function create(Subject $subject, Admin $admin)
    {
        $data['adminInfo'] = $admin->select('id', 'username')->get();
        $data['subjectInfo'] = $subject->select('id', 'cate_name')->get();
        return view('admin.topic.create', $data);
    }

    /**
     * 上传图片
     */
    public function uploads(Request $request)
    {
        $targetDir = 'uploads/img_url';
        $uploadDir = 'uploads/img_url';
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        $fileName = $request->file->getClientOriginalName();//->getClientOriginalName()
        $fileInfo = pathinfo($fileName);
        $extension = $fileInfo['extension'];
        $fileName = 'img' . time() . rand(100000, 999999) . '.' . $extension;//$file->getClientOriginalName();
        $filePath = $targetDir . '/' . $fileName;
        $uploadPath = $uploadDir . '/' . $fileName;
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                if ($tmpfilePath == "{$filePath}_{$chunk}" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}");
        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    @fclose($in);
                    @unlink("{$filePath}_{$index}");
                }
                flock($out, LOCK_UN);
            }
            @fclose($out);
        }
        $data = array('pic' => $uploadPath);
        res($data);
    }

    public function store(Request $request, Topic $topic, Subject $subject)
    {
        //话题和动态的图片上传都要改成多图上传处理
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('admin_id', 'subject_id', 'editorValue', 'img_url', 'member_id');
        $role = [
            'admin_id' => 'required',
            'subject_id' => 'required',
            'editorValue' => 'required',
            'img_url' => 'required',
        ];
        $message = [
            'admin_id.required' => '发布人不能为空！',
            'subject_id.required' => '所属分类不能为空！',
            'editorValue.required' => '话题内容不能为空！',
            'img_url.required' => '图片不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        foreach ($data['img_url'] as $k => $v) {
            $arr[] = $v;
        }
        $data['img_url'] = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        //入库
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
        $subject_catebane = $subject->select('cate_name')->find($data['subject_id']);
        $data['subjec_catename'] = $subject_catebane['cate_name'];
        $data['content'] = json_encode($data['editorValue'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $res = $topic->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Topic $topic)
    {
        if ($request->ajax()) {
            $data = $topic->with('subject')->with('admin')->with('member')->select('id', 'admin_id', 'subject_id', 'subjec_catename', 'member_id', 'nice_num', 'content', 'img_url', 'created_at')->get();
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

    public function edit(Topic $topic, Admin $admin, Subject $subject)
    {
        $data['topicInfo'] = $topic;
        $data['adminInfo'] = $admin->select('id', 'username')->get();
        $data['subjectInfo'] = $subject->select('id', 'cate_name')->get();
        return view('admin.topic.edit', $data);
    }

    public function update(Request $request, Topic $topic, Subject $subject)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('admin_id', 'subject_id', 'editorValue', 'img_url', 'old_url');
        $role = [
            'admin_id' => 'required',
            'subject_id' => 'required',
            'editorValue' => 'required',
        ];
        $message = [
            'admin_id.required' => '发布人不能为空！',
            'subject_id.required' => '所属分类不能为空！',
            'editorValue.required' => '话题内容不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调用公共文件上传
        if (empty($data['img_url'])) {
            unset($data['img_url']);
        } else {
            $data['img_url'] = json_encode($data['img_url']);
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
        $subject_catebane = $subject->select('cate_name')->find($data['subject_id']);
        $data['subjec_catename'] = $subject_catebane['cate_name'];
        // 更新数据
        $res = $topic->update($data);
        if ($res) {
            //删除原图 同时排除没有新图上传
            if (!empty($data['old_url']) && !empty($data['img_url'])) {
                foreach ($data['old_url'] as $k => $v) {
                    @unlink($v);
                }
            }
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $topic = new Topic();
        $topic = $topic->find($id);
        $res = $topic->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /**
     *  话题详情
     */
    public function details(Request $request, Subject $subject, Topic $topic, Admin $admin, Member $member, Comment $comment)
    {
        $data = $request->only('subject_id', 'member_id');
        $role = [
            'subject_id' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'subject_id.required' => '话题分类id不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res = $subject->select('read_num')->find($data['subject_id']);
        $read_num = empty($res['read_num']) ? 0 : $res['read_num'];//阅读数
        $upd = $subject->where('id', $data['subject_id'])->update(['read_num' => $read_num + 1]);//更新下阅读数
        $res2 = $topic->select('id', 'admin_id', 'member_id', 'nice_num', 'content', 'img_url', 'created_at', 'addres')->orderBy('created_at', 'DESC')->where('subject_id', $data['subject_id'])->get();
        $num = empty($res2[0]) ? false : count($res2);//参与数
        $arr['read_num'] = $read_num;//阅读数
        if ($num === false) {
            $arr['participate_num'] = 0;//参与数0
            $arr['list'] = null;//话题为空
            res($arr);
        } else {
            $arr['participate_num'] = count($res2);//参与数0
        }
        foreach ($res2 as $k => $v) {
            //发布人是否是管理,头像,昵称
            if (empty($v['admin_id'])) {
                $user_id = $v['member_id'];
                $is_admin = 2;//不是管理
                $row = $member->select('nickname', 'avatar')->find($user_id);
                $nickname = $row['nickname'];//发布人的昵称
            } else {
                $user_id = $v['admin_id'];
                $is_admin = 1;//是管理
                $row = $admin->select('username', 'avatar')->find($user_id);
                $nickname = $row['username'];
            }
            //时间
            $time = format_date($v['created_at']);
            //点赞数
            $nice = json_decode($v['nice_num'], true);
            $nice_num = count($nice);
            if ($nice_num !== 0) {
                //看看用户能不能点赞
                $is_nice = array_key_exists($data['member_id'], $nice) ? 2 : 1;
            } else {
                $is_nice = 1;//可以点赞
            }
            //评论人 评论内容
            $row2 = $comment->select('member_id', 'content')->where('to_id', $v['id'])->orderBy('created_at', 'DESC')->get();
            if (empty($row2[0])) {
                $comment_nickname = null; //最新评论人
                $comment_content = null;  //最新评论内容
                $comment_num = 0;//评论数量
            } else {
                $row3 = $member->select('nickname')->find($row2[0]['member_id']);
                $comment_nickname = $row3['nickname'];
                $comment_content = $row2[0]['content'];
                $comment_num = count($row2);//评论数量
            }
            $content = json_decode($v['content'], true);//发布的内容
            //发布的图片
            $img = json_decode($v['img_url'], true);
            foreach ($img as $key => $val) {
                $imgs[] = $request->server('HTTP_HOST') . '/' . $val;
            }
            //发布人的头像,昵称,是否是官方,发布地点,发布时间,发布的内容,图片,最新评论人的昵称,评论的内容,点赞数,评论数
            $arr['list'][] = [
                'topic_id'=>$v['id'],//记录的id
                'release_nickname' => $nickname,//发布人的昵称
                'release_avatar' => $request->server('HTTP_HOST') . '/' . $row['avatar'],//发布人的头像
                'is_admin' => $is_admin,//是否是管理员 1=是管理(官方) 2=不是
                'address' => $v['addres'],//发布的地点
                'release_time' => $time,//发布的时间
                'release_content' => $content[0],//发布的内容
                'release_img_url' => $imgs,//发布的图片
                'comment_nickname' => $comment_nickname,//评论人的昵称
                'comment_content' => $comment_content,//评论人的内容
                'nice_num' => $nice_num,//点赞数
                'is_nice' => $is_nice,//是否可以点赞 1=可以点赞 2=不可以
                'comment_num' => $comment_num,//评论数
            ];
        }
        res($arr);
    }

    /**
     * 发布话题(用户)
     */
    public function release_topic(Request $request, Topic $topic, Subject $subject)
    {
        $data = $request->only('member_id', 'subject_id', 'content', 'addres', 'img1', 'img2', 'img3');
        $role = [
            'member_id' => 'required',
            'subject_id' => 'required',
            'content' => 'required',
            'addres' => 'required',
            'img1' => 'required|image',
            'img2' => 'nullable|image',
            'img3' => 'nullable|image',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'subject_id.required' => '话题分类的id不能为空！',
            'content.required' => '动态内容不能为空！',
            'addres.required' => '发布的地点不能为空！',
            'img1.required' => '最少要一张图片！',
            'img1.image' => '图片1格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'img2.image' => '图片2格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png2！',
            'img3.image' => '图片3格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png3！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        if (!empty($data['img1'])) {
            $res = uploadpic('img1', 'uploads/img_url');//
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
        if (!empty($data['img2'])) {
            $res = uploadpic('img2', 'uploads/img_url');//
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
            $res = uploadpic('img3', 'uploads/img_url');//
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
        $data['img_url'] = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        //数据调整
        $data['content'] = json_encode([0 => $data['content']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $row = $subject->select('cate_name')->find($data['subject_id']);
        $data['subjec_catename'] = $row['cate_name'];
        $res = $topic->create($data);
        if ($res->id) {
            res(null, '发布成功');
        }
        res(null, '数据插入失败', 'fail', 100);
    }
}
