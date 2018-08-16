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
use function PHPSTORM_META\elementType;
use Validator;

class TopicController extends Controller
{

    public function index()
    {
        return view('admin.topic.index');
    }

    /**
     *  添加话题分类
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

    /**
     *  话题详细列表(管理员)
     * 返回：所有话题的id,所属话题分类，分类内容，图片等
     */
    public function ajax_list(Request $request, Topic $topic)
    {
        if ($request->ajax()) {
            $data = $topic
                ->leftJoin('member','member.id','=','topic.member_id')
                ->leftJoin('subject','subject.id','=','topic.subject_id')
                ->select('topic.id', 'topic.lev_state', 'topic.subject_id', 'topic.member_id', 'topic.nice_num', 'topic.content', 'topic.img_url', 'topic.created_at','subject.cate_name','member.nickname')
                ->get();
            foreach ($data as $k=>$v){
                $data[$k]['nice_num'] = empty($v['nice_num']) ? 0 : count($v['nice_num']);
            }
            $cnt = !empty($data) ? count($data) : 0;
            $info = [
                'draw' => $request->get('draw'),
                'recordsTotal' => $cnt,
                'recordsFiltered' => $cnt,
                'data' => $data,
            ];
//            dump(obj_arr($info));
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

    /**
     * 修改话题分类（管理员）
     * @param Request $request
     * @return array
     */
    public function update(Request $request, Topic $topic, Subject $subject)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('admin_id', 'subject_id', 'editorValue', 'img_url', 'old_url');
        $role = [
            'admin_id' => 'exists:admin,id',
            'subject_id' => 'exists:subject,id',
            'editorValue' => 'required',
        ];
        $message = [
            'admin_id.exists' => '管理员不合法！',
            'subject_id.exists' => '所属分类不存在！',
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

    /**
     * 管理员删除话题
     * @param $id
     * @return array
     */
    public function destroy($id,Comment $comment)
    {
        $topic = new Topic();
        $topic_coll = $topic->select('img_url')->where('id',$id)->first();
        $topic_coll = obj_arr($topic_coll);//强制转数组
        # 如果存在图片，需要删除图片
        if(!empty($topic_coll['img_url'])){
            $fine_arr = $topic_coll['img_url'];
            //为图片拼接完整路径
            array_walk(
                $fine_arr,
                function (&$s, $k, $prefix='./') {
                    $s = str_pad($s, strlen($prefix) + strlen($s), $prefix, STR_PAD_LEFT);
                }
            );
            //批量删除
            delPics($fine_arr);
        }
        # 删除文章下的所有评论
        //查询文章下的一级评论
        $t_cos = $comment->select('id')->where('to_id',$id)->get();
        $t_cos = obj_arr($t_cos);
        if(!empty($t_cos)){//不为空一一删除全部；为空不需要删除
            $all_com = $comment->select('id','parent_id')->get()->toArray();//所有评论id及父id
            $id_arr = $comment->getChildrenIds($all_com, $t_cos);//该文章下的所有评论的id
            $del_comment = $comment->whereIn('id',$id_arr)->delete();
        }else{
            $del_comment = true;
        }
        //删除话题
        $res = $topic->where('id',$id)->delete();
//        dump($res);exit();
        if ($res) {
            return ['status' => "success"];
        } else {
            return ['status' => "fail", 'error' => "删除失败！"];
        }
    }

    /**
     *  分类下的话题列表
     * 传入：当前用户member_id 话题分类subject_id
     * 返回： 指定话题分类下的列表
     */
    public function details(Request $request, Subject $subject, Topic $topic, Admin $admin, Member $member, Comment $comment)
    {
        $data = $request->only('subject_id', 'member_id');
        $role = [
            'subject_id' => 'required',
            'member_id' => 'exists:member,id',
        ];
        $message = [
            'subject_id.required' => '话题分类id不能为空！',
            'member_id.exists' => '用户不合法！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res = $subject->select('read_num')->find($data['subject_id']);
        $read_num = empty($res['read_num']) ? 0 : $res['read_num'];//阅读数
        $upd = $subject->where('id', $data['subject_id'])->update(['read_num' => $read_num + 1]);//更新下阅读数
        $res2 = $topic->select('id', 'lev_state', 'member_id', 'nice_num', 'content', 'img_url', 'created_at', 'addres')->orderBy('created_at', 'DESC')->where('subject_id', $data['subject_id'])->get();
        $num = empty($res2[0]) ? false : count($res2);//该话题总条数:空为false
        $arr['read_num'] = $read_num;//已阅读数
        if ($num === false) {
            $arr['participate_num'] = 0;//参与数0
            $arr['list'] = null;//话题为空
            res($arr);
        } else {
            $arr['participate_num'] = count($res2);//参与数0
        }
        foreach ($res2 as $k => $v) {
            //发布人是否是管理
            if ($v['lev_state'] == 1) {//1为普通用户
                $is_admin = 2;//不是管理
            } else {
                $is_admin = 1;//是管理
            }
            $user_id = $v['member_id'];
            $row = $member->select('nickname', 'avatar')->find($user_id);//昵称、头像
            $nickname = $row['nickname'];//发布人的昵称
            $time = format_date($v['created_at']);//发布时间
            $nice = $v['nice_num'];//点赞数
            if(!empty($nice)){//有人点赞
                //看看用户能不能点赞
                $is_nice = array_key_exists($data['member_id'], $nice) ? 2 : 1;
                $nice_num = count($nice) ;
            }else{
                $is_nice = 1;//可以点赞
                $nice_num = 0;
            }
            //评论人 评论内容
            $row2 = $comment
                ->leftjoin('member','member.id','=','comment.member_id')
                ->select('comment.member_id', 'comment.content','member.nickname','comment.created_at','member.avatar')->where('to_id', $v['id'])->orderBy('comment.id', 'DESC')->get();//一级评论
            $comments_num =  $row2->count();
            if ($comments_num == 0) {//没有评论
                $comment_arr[0] = ['nickname'=>null,'content'=>null,'member_id'=>null,'created_at'=>null,'avatar'=>null];
                $comment_num = 0;//评论数量
            } elseif($comments_num <= 3) {//有评论小于等于三
                $comment_arr = obj_arr($row2);
                $comment_num = count($row2);//评论数量
            } else{
                $comment_arr =  array_slice(obj_arr($row2),0,3);
                $comment_num = count($row2);//评论数量
            }
            //发布的图片
            $imgs = empty($v['img_url']) ? null : add_arr_prefix('url_prefix',$v['img_url']);
            //发布人的头像,昵称,是否是官方,发布地点,发布时间,发布的内容,图片,最新评论人的昵称,评论的内容,点赞数,评论数
            $arr['list'][] = [
                'topic_id'=>$v['id'],//记录的id
                'release_nickname' => $nickname,//发布人的昵称
                'release_avatar' => $request->server('HTTP_HOST') . '/' . $row['avatar'],//发布人的头像
                'is_admin' => $is_admin,//是否是管理员 1=是管理(官方) 2=不是
                'address' => $v['addres'],//发布的地点
                'release_time' => $time,//发布的时间
                'release_content' => $v['content'][0],//发布的内容
                'release_img_url' => $imgs,//发布的图片
                'nice_num' => $nice_num,//点赞数
                'is_nice' => $is_nice,//是否可以点赞 1=可以点赞 2=不可以
                'comment_num' => $comment_num,//评论数
                'comments' => $comment_arr,
            ];
        }
        // res(dump($arr));
        res($arr);
    }

    /**
     * 发布话题(用户)
     * 返回：发布的话题，发布人的信息
     */
    public function release_topic(Request $request, Topic $topic, Subject $subject, Member $member)
    {
        $data = $request->only('member_id', 'subject_id', 'content', 'addres', 'img1', 'img2', 'img3');
        $role = [
            'member_id' => 'exists:member,id',
            'subject_id' => 'exists:subject,id',
            'content' => 'required',
            'addres' => 'required',
            'img1' => 'required|image',
            'img2' => 'nullable|image',
            'img3' => 'nullable|image',
        ];
        $message = [
            'member_id.exists' => '用户id非法！',
            'subject_id.exists' => '话题分类的非法！',
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
            $res = uploadpic('img1', 'uploads/img_url/topic/'.date('Y-m-d'));//
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
            $res = uploadpic('img2', 'uploads/img_url/topic/'.date('Y-m-d'));//
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
            $res = uploadpic('img3', 'uploads/img_url/topic/'.date('Y-m-d'));//
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
        $data['subjec_catename'] = $row['cate_name'];//话题分类名称
        //查询发布者是否是官方
        $is_admin = $member->select('is_admin')->where('id',$data['member_id'])->first();
        if($is_admin['is_admin'] == 1){//如果是官方则lev_state=null,默认为1
            $data['lev_state'] = null;
        }
        $res = $topic->create($data);
        if ($res->id) {
            res(null, '发布成功');
        }
        res(null, '数据插入失败', 'fail', 100);
    }

    /**
     * 删除话题(真删除) //需要动态id,因为用户删除的只能是自己的动态,所以不考虑是否是官方
     */
    public function del_topic(Request $request, Topic $topic, Comment $comment)
    {
        $data = $request->only('act_mem_id','topic_id');
        $role = ['topic_id' => 'exists:topic,id','act_mem_id' => 'required' ];//被删除的记录的id
        $message = ['topic_id.exists' => '请求不合法！','act_mem_id.required' => '当前用户不能为空'];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $topic_coll = $topic->select('member_id','img_url')->where('id',$data['topic_id'])->first();
        # 判断当前文章博主是否为当前用户
        if ($topic_coll['member_id'] != $data['act_mem_id']){//不是拥有者：拒绝
            res(null, '无权限或不存在', 'fail', 102);
        }
        # 如果存在图片，需要删除图片
        if(!empty($topic_coll['img_url'])){
            $fine_arr = json_decode( $topic_coll['img_url']);
            //为图片拼接完整路径
            array_walk(
                $fine_arr,
                function (&$s, $k, $prefix='./') {
                    $s = str_pad($s, strlen($prefix) + strlen($s), $prefix, STR_PAD_LEFT);
                }
            );
            //批量删除
            delPics($fine_arr);
        }
        # 删除文章
        $res = $topic->where('id',$data['topic_id'])->delete();
        # 删除文章下的所有评论
        //查询文章下的一级评论
        $dy_cos = $comment->select('id')->where('to_id',$data['topic_id'])->get()->toArray();
        if(!empty($dy_cos)){//不为空一一删除全部；为空不需要删除
            $all_com = $comment->select('id','parent_id')->get()->toArray();//所有评论id及父id
            $id_arr = $comment->getChildrenIds($all_com, $dy_cos);//该文章下的所有评论的id
            $del_comment = $comment->whereIn('id',$id_arr)->delete();
        }else{
            $del_comment = true;
        }
        if ($res) {
            if($del_comment){
                res(null, '删除成功');
            }
            res(null, '删除成功,未删除评论');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 查询单个话题
     */
    function hot_topic(Request $request, Topic $topic, Member $member)
    {
        $data = $request->only('act_mem_id','topic_id');
        $role = ['topic_id' => 'required','act_mem_id' => 'required' ];//被删除的记录的id
        $message = ['topic_id.required' => '请求不合法！','act_mem_id.required' => '当前用户不能为空'];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $topic_info = $topic->select('id','member_id','subject_id','subjec_catename','nice_num','content','img_url','addres','created_at')->where('id',$data['topic_id'])->first()->toArray();
        if(empty($topic_info)){
            res(null, '不存在该话题', 'fail', 101);
        }else{
            $topic_info['img_url'] =  add_arr_prefix('url_prefix',$topic_info['img_url']);//添加图片url
            $topic_info['created_at'] = format_date($topic_info['created_at']);//多久之前
            if(!empty($topic_info['nice_num'])){
                $nice_num = count($topic_info['nice_num']);
                $nic_mes = $member->select('id','nickname','avatar')->whereIn('id',array_keys($topic_info['nice_num']))->get();
                foreach ($nic_mes as $k => $v){
                    $nic_mes[$k]['avatar'] = config('app.url').$v['avatar'];
                }
            }else{
                $nice_num = 0;
                $nic_mes = null;
            }
            $topic_info['nice_num'] =  $nice_num;//点赞总数
            $topic_info['nic_mems'] = $nic_mes;//所有点赞人信息

            res($topic_info);
        }

    }

}