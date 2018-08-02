<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\Comment;
use App\Models\Dynamic;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class DynamicController extends Controller
{

    public function index()
    {
        return view('admin.dynamic.index');
    }

    /**
     * 回收站
     * 返回：
     */
    public function recycling()
    {
        return view('admin.dynamic.recycling');
    }

    /**
     * 回收站列表
     * 返回：
     */
    public function recycling_list(Request $request, Dynamic $dynamic)
    {

        if ($request->ajax()) {
            $data = $dynamic->with('member')->onlyTrashed()->select('id', 'created_at', 'member_id', 'nice_num', 'img_url', 'addres')->get()->toArray();
            foreach ( $data as $k=>$v ){
                $data[$k]['nice_num'] = count( json_decode( $v['nice_num'],true ) );
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

    public function restore(Request $request, Dynamic $dynamic)
    {
        $id = $request->only('id');
        $res = $dynamic->where('id', $id['id'])->restore();
        if ($res) {
            return ['status' => "success"];
        } else {
            return ['status' => "fail", 'error' => '失败！'];
        }
    }
    public function create(Member $member)
    {
        $data['memberInof'] = $member->select('id', 'nickname')->get();
        return view('admin.dynamic.create', $data);
    }

    public function store(Request $request, Dynamic $dynamic)
    {
        if (!$request->ajax()) {
            return ['status' => "fail", 'error' => '非法的请求类型'];
        }
        $data = $request->only('member_id', 'img_url', 'editorValue', 'addres', 'content');
        $role = [
            'member_id' => 'required | exists:member,id',
            'img_url' => 'required',
            'content' => 'required',
            'addres' => 'required',
        ];
        $message = [
            'member_id.required' => '发布人不能为空！',
             'member_id.exists' => '非法id！',
            'img_url.required' => '图片不能为空！',
            'content.required' => '动态不能为空！',
            'addres.required' => '发布的地址不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => "fail", 'msg' => $validator->messages()->first()];
        }
        $data['img_url'] = json_encode($data['img_url']);
        $res = $dynamic->create($data);
        if ($res->id) {
            return ['status' => "success", 'msg' => '添加成功'];
        }
        return ['status' => "fail", 'msg' => '添加失败'];
    }

    /**
     * 动态首页列表（管理员）
     * @param Request $request
     * @param Dynamic $dynamic
     * @return array
     */
    public function ajax_list(Request $request, Dynamic $dynamic)
    {
        if ($request->ajax()) {
            $data = $dynamic->with('member')->select('id', 'created_at', 'member_id', 'nice_num', 'img_url', 'content', 'addres')->get()->toArray();
            foreach ( $data as $k=>$v ){
                $data[$k]['nice_num'] = !empty($v['nice_num']) ? count( json_decode( $v['nice_num'],true ) ) : 0 ;
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

    public function edit(Dynamic $dynamic)
    {
        $data['dynamicInfo'] = $dynamic;
        $data['Info'] = $dynamic->with('member')->first();
        return view('admin.dynamic.edit', $data);
    }

    public function update(Request $request, Dynamic $dynamic)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('addres', 'img_url', 'old_url', 'content');

        //调用公共文件上传
        if (empty($data['img_url'])) {
            unset($data['img_url']);
        } else {
            $data['img_url'] = json_encode($data['img_url']);
        }
        // 更新数据
        $res = $dynamic->update($data);
        if ($res) {
            //删除原图
            if (!empty($data['old_url']) && !empty($data['img_url'])) {
                foreach ($data['old_url'] as $k => $v) {
                    @unlink($v);
                }
            }
            return ['status' => "success", 'msg' => "修改成功"];
        } else {
            return ['status' => "fail", 'code' => 3, 'error' => "修改失败！"];
        }
    }

    /**
     * 管理员删除动态
     * $id动态id
     */
    public function destroy($id,Comment $comment)
    {
        $dynamic = new Dynamic();
        # 删除图片
        $dy_coll = $dynamic->select('img_url')->find($id);
        $dy_coll = obj_arr($dy_coll);//强制转数组
        // 如果存在图片，需要删除图片
        if(!empty($dy_coll['img_url'])){
            $fine_arr = json_decode( $dy_coll['img_url']);
            //为图片拼接完整路径
            array_walk(
                $fine_arr,
                function (&$s, $k, $prefix='./') {
                    $s = str_pad($s, strlen($prefix) + strlen($s), $prefix, STR_PAD_LEFT);
                }
            );
            //批量删除
            @delPics($fine_arr);
        }
        # 删除文章下的所有评论
        //查询文章下的一级评论
        $dy_cos = $comment->select('id')->where('dy_id',$id)->get();
        $dy_cos = obj_arr($dy_cos);
        if(!empty($dy_cos)){//不为空一一删除全部；为空不需要删除
            $all_com = $comment->select('id','parent_id')->get()->toArray();//所有评论id及父id
            $id_arr = $comment->getChildrenIds($all_com, $dy_cos);//该文章下的所有评论的id
            $comment->whereIn('id',$id_arr)->delete();
        }
        # 删除文章
        $res = $dynamic->where('id',$id)->delete();
        if ($res) {
            return ['status' => "success"];
        } else {
            return ['status' => "fail", 'error' => "删除失败！"];
        }
    }
    /**
     * 删除动态(真删除) //需要动态id,因为用户删除的只能是自己的动态,所以不考虑是否是官方
     */
    public function del_dy(Request $request, Dynamic $dynamic, Comment $comment)
    {
        $data = $request->only('act_mem_id','dy_id');
        $role = ['dy_id' => 'exists:dynamic,id'];//被删除的记录的id
        $message = ['dy_id.exists' => '请求不合法！',];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $dy_coll = $dynamic->select('member_id','img_url')->where('id',$data['dy_id'])->first();
        # 判断当前文章博主是否为当前用户
        if ($dy_coll['member_id'] != $data['act_mem_id']){//不是拥有者：拒绝
            res(null, '无权限或不存在', 'fail', 102);
        }
        # 如果存在图片，需要删除图片
        if(!empty($dy_coll['img_url'])){
            $fine_arr = json_decode( $dy_coll['img_url']);
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
        $dy_cos = $comment->select('id')->where('dy_id',$data['dy_id'])->get()->toArray();
        if(!empty($dy_cos)){//不为空一一删除全部；为空不需要删除
            $all_com = $comment->select('id','parent_id')->get()->toArray();//所有评论id及父id
            $id_arr = $comment->getChildrenIds($all_com, $dy_cos);//该文章下的所有评论的id
            $del_comment = $comment->whereIn('id',$id_arr)->delete();
        }else{
            $del_comment = true;
        }
        # 删除文章
        $res = $dynamic->where('id',$data['dy_id'])->delete();
        if ($res) {
            if($del_comment){
                res(null, '删除成功');
            }
            res(null, '删除成功,未删除评论');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     * 发布动态
     */
    public function release_dynamic(Request $request, Dynamic $dynamic)
    {
        $data = $request->only('member_id', 'content', 'addres', 'img1', 'img2', 'img3');
        $role = [
            'member_id' => 'exists:member,id',
            'content' => 'required',
            'addres' => 'required',
            'img1' => 'required|image',
            'img2' => 'nullable|image',
            'img3' => 'nullable|image',
        ];
        $message = [
            'member_id.exists' => '用户id不合法！',
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
        $res = $dynamic->create($data);
        if ($res->id) {
            res(null, '发布成功');
        }
        res(null, '数据插入失败', 'fail', 100);
    }

    /**
     * 动态首页、动态列表
     * $request['page'] 当前页码
     * $item 每页显示的条数
     * $items 当前显示的动态总数
     */
    public function show_list(Request $request, Activity $activity, Dynamic $dynamic, Member $member)
    {
        //返回赛事的封面图
        $res = $activity->select('img_url')->orderBy('id', 'DESC')->limit(3)->get();
        $arr['shuffling'][] = $request->server('HTTP_HOST') . '/' . $res[0]['img_url'];
        $arr['shuffling'][] = $request->server('HTTP_HOST') . '/' . $res[1]['img_url'];
        $arr['shuffling'][] = $request->server('HTTP_HOST') . '/' . $res[2]['img_url'];
        //每页显示的条数
        $item = 16;
        //当前显示的动态总数
        $items = $item * $request['page'];
        //查询指定信息条数并返回动态的图片,内容,发布人的头像,昵称,点赞数
        $res = $dynamic->select('id', 'img_url', 'content', 'nice_num', 'member_id','created_at')
            ->limit($items)->orderBy('created_at', 'DESC')->get()->toArray();

        if (empty($res[0])) {
            $arr['list'] = null;
            res($arr, '查询成功', 'success', 200);
        }
        foreach ($res as $k => $v) {
            //根据用户的id,查找昵称和头像
            $row = $member->select('avatar', 'nickname')->find($v['member_id']);
            $img_url = json_decode($v['img_url'], true);
            $img_num = empty( $img_url) ? 0 : count($img_url);
            $nice = empty(json_decode($v['nice_num'],true)) ? 0 : count(json_decode($v['nice_num'], true));
            $arr['list'][] = [
                'dynamic_id' => $v['id'],
                'img_url' => $request->server('HTTP_HOST') . '/' . $img_url[0],//动态图片第一张
                'content' => $v['content'],//动态内容
                'nice_num' => $nice,//点赞数
                'member_nickname' => $row['nickname'],//发布人昵称
                'member_avatar' => $request->server('HTTP_HOST') . '/' . $row['avatar'],//发布人头像
                'user_id' => $v['member_id'],//发布人id
                'img_num' => $img_num,//照片总数
            ];
        }
        res($arr);
    }

    /**
     * 动态详情（不含评论）
     * 返回：博主id'member_id' 点赞总数nice_num 内容content 博主昵称member_nickname 博主头像member_avatar 博文图片img_url 点赞人头像列表'nice_list' 博主性别'sex' 是否已关注'is_focus' 当前查询的用户 'user_id'
     */
    public function show_dy_content(Request $request, Member $member, Dynamic $dynamic)
    {
        $data = $request->only('member_id', 'dynamic_id');
        $role = [
            'member_id' => 'required',
            'dynamic_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'dynamic_id.required' => '动态id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //动态的全部信息
        $res1 = $dynamic->select('member_id', 'nice_num','img_url','content','addres','created_at')->find($data['dynamic_id']);
        $dy_mem = $member->select('nickname','avatar')->where('id',$res1['member_id'])->first();//发布人昵称及头像;
        //$res1['nice_num']点赞详情
        if (empty($res1['nice_num'])) {
            $nice = null;//点赞人为空
            $nices['nice_list'] = null;
            $nice_num = 0 ;
            $is_nice = 1;
        } else {
            $nice = json_decode($res1['nice_num'], true);
            $nice_num= count($nice);//点赞总数
            arsort($nice);
            $i = 1;
            //只查找7个
            foreach ($nice as $k => $v) {
                if ($i >= 8) {
                    break;
                }
                //最新评论人的id
                $avatar = $member->select('id','avatar')->find($k);
                $nices['nice_list'][] = $request->server('HTTP_HOST') . '/' . $avatar['avatar'];
                $i++;
            }
            $is_nice = in_array($data['member_id'],array_keys($nice)) ? 0 : 1;
        }


        $arr = [];
        $arr = $nices;//点赞人的头像$arr[ "nice_list"]
        $arr['nice_num'] =  $nice_num; //点赞数
        $arr['nice_list'] = $nices['nice_list'];//点赞人头像
        $arr['is_nice'] = $is_nice;//是否点赞（0已点；1可以点赞）
        $arr['writer_id'] = $res1['member_id'];//博主id
        $arr['writer_at'] =  date('Y-m-d H:i:s',strtotime($res1['created_at']));//发布时间
        $arr['member_id'] = $data['member_id'];//当前用户id
        $arr['content'] = $res1['content'];//动态内容
        $arr['writer_nickname'] = $dy_mem['nickname'];//发布人昵称
        $arr['writer_avatar'] = $request->server('HTTP_HOST') . '/' . $dy_mem['avatar'];//发布人头像
        $img_url = json_decode($res1['img_url']);
        if(!empty($img_url)){
            $img_url = add_arr_prefix('url_prefix',$img_url);
        }
        $arr['img_url'] = $img_url;
        $res2 = $member->select('fans_id', 'sex')->find($res1['member_id']);
        $arr['sex'] = $res2['sex'];//性别
        $friends_id = empty($res2['fans_id']) ? false : json_decode($res2['fans_id']);//是否关注
        if ($friends_id == false) {
            //如果发布人的粉丝为空,说明当前用户还没关注
            $arr['is_focus'] = 2;
        } else {
            //如果当前用户,在发布人的粉丝列表中,说明已经关注 1= 已关注,2= 未关注
            if (in_array($data['member_id'], $friends_id)) {
                $arr['is_focus'] = 1;
            } else {
                $arr['is_focus'] = 2;
            }
        }
        res($arr);
    }

    /**
     *  点赞详情页
     */
    public function show_nice_content(Request $request, Dynamic $dynamic, Member $member)
    {
        $data = $request->only('dynamic_id');
        $role = [
            'dynamic_id' => 'required',
        ];
        $message = [
            'dynamic_id.required' => '动态id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查看这条动态
        $res = $dynamic->select('nice_num')->find($data['dynamic_id']);
        if (empty($res['nice_num'])) {
            res(null, '查询成功', 'fail', 201);
        }
        $nice_row = json_decode($res['nice_num'], true);
        $arr['nice_num'] = count($nice_row);//多少人点赞
        //根据点赞记录集,找出点赞人的昵称和头像,并根据评论时间,返回评论时间
        arsort($nice_row);//倒序
        foreach ($nice_row as $k => $v) {
            //k=用户id v=评论时间
            $res1 = $member->select('avatar', 'nickname')->find($k);
            $time = format_date($v);
            $arr['list'][] = [
                'avatar' => $request->server('HTTP_HOST') . '/' . $res1['avatar'],//评论人头像
                'nickname' => $res1['nickname'],//评论人昵称
                'comment_time' => $time
            ];
        }
        res($arr);
    }

    /****************************** 递归***************************************************************************/

    /**
     * 传递父级分类ID返回所有子分类ID（不含父id）
     * @param [type] $cate 要递归的数组
     * @param [type] $pid 父级分类ID
     * @return [type]  [description]
     */
    public function getChildrenId($cate, $pid)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['parent_id'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, self::getChildrenId($cate, $v['id']));
            }
        }
        return $arr;
    }

    /**
     * 传递所有父级id数组集合 返回其本身及其后代id的一维数组（含父id）
     * @param [array] $cate 要递归的数组
     * @param [array] $p_ids 父级分类ID的二维数组例如：array:3 [0 => array:1 ["id" => 4]1 => array:1 ["id" => 6]2 => array:1 ["id" => 8]]
     * @return [type]  [description]
     */
    public function getChildrenIds($cate, $p_ids)
    {
        $arr = array();
        foreach ($p_ids as $val){
            $co_id = array($val['id']);
            $arr =array_merge($co_id, array_merge($arr,self::getChildrenId($cate,$val['id'])));
        }
        return $arr;
    }

}
