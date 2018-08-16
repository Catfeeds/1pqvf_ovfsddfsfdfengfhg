<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Comment;
use App\Models\Dynamic;
use App\Models\Member;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Storage;//图片上传
class MemberController extends Controller
{
    public function index()
    {
        return view('admin.member.index');
    }

    public function create()
    {
        return view('admin.member.create');
    }

    public function store(Request $request, Member $member)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('nickname', 'phone', 'avatar', 'password', 'pwd', 'sex', 'age', 'city');
        $role = [
        	'nickname' => 'between:1,3',
            'phone' => 'required|regex:/1[3456789]\d{9}/|unique:member',
            'avatar' => 'nullable|file|image|mimes:png,gif,jpeg,jpg|max:3145728',
            'password' => 'required|between:3,20|same:pwd',
            'sex' => 'nullable|between:1,3',
            'age' => 'nullable|between:1,150',
        ];
        $message = [
        	'nickname.between' => '昵称为3-6个字节',
            'phone.required' => '手机号不能为空！',
            'phone.regex' => '手机号格式不正确！',
            'phone.unique' => '手机号已存在！',
            'avatar.file'      => '头像上传失败！',
            'avatar.image' => '上传的头像格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'avatar.mimes'     => '头像的文件格式不正确！',
            'avatar.max' => '文件最大最3m!',
            'password.required' => '密码不能为空！',
            'password.between' => '密码长度为3-20！',
            'password.same' => '密码和确认密码不一致！',
            'sex.between' => '性别不合法！',
            'age.between' => '年龄不符合要求！',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //入库
        $data['password'] = bcrypt($data['password']);
        $data['api_token'] = str_random(60);
        //调用公共文件上传
        if (!empty($data['avatar'])) {
            $res = uploadpic('avatar', 'uploads/avatar/member/'.date('Y-m-d'));//
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
            $data['avatar'] = $res; //把得到的地址给picname存到数据库
        }
        //后台创建的用户都是管理员账户
        $data['is_admin'] = 1;
        $res = $member->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, Member $member, Admin $admin)
    {
        if ($request->ajax()) {
            $data = $member->select('id', 'phone', 'nickname', 'avatar', 'sex', 'age', 'integral', 'qr_code', 'friends_id', 'fans_id', 'attention_id', 'tesco', 'disabled_at','city')
                ->get()->toArray();
            $arr = $data;
            if (!empty($data[0])) {
                $friends_nickname = '';
                $fans_nickname = '';
                foreach ($data as $k => $v) {
                    if (!empty($v['friends_id'])) {
                        $friends_id = json_decode($v['friends_id'], true);
                        foreach ($friends_id as $key => $val) {
                            if (preg_match('/[a-zA-Z]/', $val)) {
                                //他关注的是管理 a1
                                $id = substr($val, 1);
                                $row = $admin->select('username')->find($id);
                                $friends_nickname .= $row['username'] . '<br>';
                            } else {
                                $row = $member->select('nickname', 'phone')->find($val);
                                $friends_nickname .= empty($row['nickname']) ? $row['phone'] . '<br>' : $row['nickname'] . ' ';
                            }
                        }
                    }
                    if (!empty($v['fans_id'])) {
                        $fans_id = json_decode($v['fans_id'], true);
                        foreach ($fans_id as $key => $val) {
                            $row = $member->select('nickname', 'phone')->find($val);
                            $fans_nickname .= empty($row['nickname']) ? $row['phone'] . '<br>' : $row['nickname'] . ' ';
                        }
                    }
                    $arr[$k]['friends_nickname'] = $friends_nickname;
                    $arr[$k]['fans_nickname'] = $fans_nickname;
                }
            }
            $cnt = count($data);
            $info = [
                'draw' => $request->get('draw'),
                'recordsTotal' => $cnt,
                'recordsFiltered' => $cnt,
                'data' => $arr,
            ];
            return $info;
        }
    }

    public function edit(Member $member)
    {
        $data['memberInfo'] = $member;
        $data['memberInfo']['address'] = json_decode($data['memberInfo']['address']);
        return view('admin.member.edit', $data);
    }

    /*
     * api_token过期
     * */
    public function login(){
        res(null,'登录信息已过期','fail',103);
    }

    public function update(Request $request, Member $member)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('phone', 'password', 'pwd', 'nickname', 'avatar', 'city', 'sex', 'age', 'height', 'weight', 'address_nickname', 'address_phone', 'address_province', 'address_address', 'address_zip_code', 'disabled_at');
        // 接收数据
        // 校验数据
        // 手机端:  任何一项都可以为不修改,一旦存在数据,则需要验证,并更新   ~~~

        // 数据调整
        $data['disabled_at'] = $data['disabled_at'] == 1 ? null : date('Y-m-d H:i:s');

        //头像大小,城市
        //假如有字段则,则验证 sometimes
        $role = [
            'phone' => 'nullable|regex:/1[3456789]\d{9}$/|unique:member,phone',
            'password' => 'nullable|same:pwd',
            'nickname' => 'nullable|max:30',
            'avatar' => 'nullable|image|max:2048',//2m 单位kb  2048  200
            'sex' => 'nullable|integer',
            'age' => 'nullable|integer|between:1,150',
            'height' => 'nullable|integer|between:1,300',
            'weight' => 'nullable|integer|between:1,1000',
            'address' => 'nullable|max:120',
        ];
        $message = [
            'phone.regex' => '手机号码格式不正确！',
            'phone.unique' => '手机号码已存在！',
            'password.same' => '密码和确认密码不一致！',
            'nickname.max' => '用户名最大长度为30位！',
            'avatar.image' => '上传的头像格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
            'avatar.max' => '文件最大最2m!',
            'sex.integer' => '性别必须是(男/女/保密)！',
            'age.integer' => '年龄不正确,必须是数字',
            'height.integer' => '身高不正确,必须是数字',
            'weight.integer' => '体重不正确,必须是数字',
            'age.between' => '年龄不正确,必须在1-150之间！',
            'height.digits_between' => '身高不正确,必须在1-300之间！',
            'weight.digits_between' => '体重不正确,必须在1-1000之间！',
            'address.max' => '超出长度,最大长度为120！'
        ];

        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调用公共文件上传
        if (!empty($data['avatar'])) {
            $res = uploadpic('avatar', 'uploads/avatar/member/'.date('Y-m-d'));//
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
            $data['avatar'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $member->avatar;
            if (!empty($ress) && $ress !== 'uploads/avatar/morentouxiang.png') {
                unlink($ress);
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        if (!empty($data['address_nickname']) || !empty($data['address_phone']) || !empty($data['address_province']) || !empty($data['address_address']) || !empty($data['address_zip_code'])) {
            $data['address'] = [
                'nickname' => $data['address_nickname'],
                'phone' => $data['address_phone'],
                'province' => $data['address_province'],
                'address' => $data['address_address'],
                'zip_code' => $data['address_zip_code'],
            ];
            //只要有一个不为空,则重新组合
            $add = json_decode($member->address, true);
            $temp = array();
            if (!empty($add)) {
                foreach ($add as $key => $val) {
                    $temp[$key] = $val;
                }
            }

            //将新数据加到要更新的数据中,只更新有改动的字段
            foreach ($data['address'] as $key => $item) {
                if ($item != null) {
                    $temp['address'][$key] = $item;
                }
            }
            $add = json_encode($temp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data['address'] = $add;
        }
        //如果字段为空,则删除 排除禁用的情况
        foreach ($data as $k => $v) {
            if (empty($v) && $k != 'disabled_at') {
                unset($data[$k]);
            }
        }
        // 更新数据
        $res = $member->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '更新成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '更新失败！'];
        }
    }

    /**
     * 修改用户数据接口
     */
    public function member_upd(Request $request, Member $member)
    {
        //该接口可单独修改:昵称,性别,城市
        $data = $request->only('member_id', 'nickname', 'city', 'sex');
        $role = [
            'member_id' => 'required | exists:member,id',
            'nickname' => 'between:3,6',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'member_id.exists' => '非法请求！',
            'nickname.between' => '昵称为3-6个字节',
        ];
        $validator = Validator::make($data, $role, $message);
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //如果字段为空,则删除 排除禁用的情况
        foreach ($data as $k => $v) {
            if (empty($v)) {
                unset($data[$k]);
            }
        }
        if (empty($data['nickname']) && empty($data['city']) && empty($data['sex'])) {
            res(null, '修改昵称,城市,性别,必须有其中一项不为空');
        }
        // 更新数据
        $id = $data['member_id'];
        unset($data['member_id']);
        $res = $member->where(['id' => $id])->update($data);
        if ($res) {
            res(null, '更新成功');
        } else {
            res(null, '更新失败', 'fail', 100);
        }

    }

    /**
     * 修改密码
     */
    public function UpdPwd(Request $request, Member $member)
    {
        $data = $request->only('phone', 'password', 'smskey');
        //验证该手机号码在数据库中是否存在
        $role = [
            'phone' => 'required | exists:member,phone',
            'password' => 'required',
            'smskey' => 'required',
        ];
        $message = [
            'phone.required' => '手机号码不能为空',
            'phone.exists' => '该用户不存在',
            'password.required' => '密码不能为空',
            'smskey.required' => '验证码不能为空',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        if (empty(Redis::get($data['phone'])) || (Redis::get($data['phone']) != $data['smskey'])) {
            //判断验证码是否过期或验证失败
            res(null, '验证码错误或已过期', 'fail', 108);
        }
        $pwd = bcrypt($data['password']);
        $res = $member->where(['phone' => $data['phone']])->select()->update(['password' => $pwd]);
        if ($res) {
            res(null, '修改成功');
        }
        res(null, '修改失败', 'fail', 100);
    }

    /**
     * 修改手机号码
     */
    public function upd_member_phone(Request $request, Member $member)
    {
        $tmp = $request->only('member_id', 'phone', 'password', 'smskey');
        $role = [
            'member_id' => 'required',
            'phone' => 'required|unique:member,phone',
            'password' => 'required',
            'smskey' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空',
            'phone.required' => '手机号码不能为空',
            'phone.unique' => '手机号码已存在',
            'password.required' => '密码不能为空',
            'smskey.required' => '验证码不能为空',
        ];
        $validator = Validator::make($tmp, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //验证码
        if (empty(Redis::get($tmp['phone'])) || (Redis::get($tmp['phone']) != $tmp['smskey'])) {
            //判断验证码是否过期或验证失败
            res(null, '验证码错误或已过期', 'fail', 108);
        }
        //验证密码
        $checkPwd = Auth::guard('member')->attempt(['id' => $tmp['member_id'], 'password' => $tmp['password'], 'disabled_at' => null], false);
        if ($checkPwd) {
            //验证成功,修改手机号码
            $res = $member->where(['id' => $tmp['member_id']])->update(['phone' => $tmp['phone']]);
            if ($res) {
                res(null, '修改成功');
            }
            res(null, '失败');
        }
        res(null, '原密码错误', 'fail', 107);
    }

    /**
     * 修改收货地址
     */
    public function upd_member_addr(Request $request, Member $member)
    {
        //收货人的昵称,手机,省份,详细地址,邮政编码
        $tmp = $request->only('member_id', 'nickname', 'phone', 'province', 'address', 'zip_code');
        $role = [
            'member_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空',
        ];
        $validator = Validator::make($tmp, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        if (empty($tmp['nickname']) && empty($tmp['phone']) && empty($tmp['province']) && empty($tmp['address']) && empty($tmp['zip_code'])) {
            res(null, '至少要有一项做出修改', 'fail', 101);
        }
        //不做验证
        foreach ($tmp as $k => $v) {
            if ($k == 'member_id') {
                continue;
            }
            $data['address'][$k] = $v;
        }
        //更新之前,先取出旧数据,循环,当有新数据的时候,再进行更新
        $old = $member->select('address')->where(['id' => $tmp['member_id']])->first();
        $temp = array();
        $old['address'] = json_decode($old['address'], true);
        if (!empty($old['address'])) {
            foreach ($old['address'] as $key => $val) {
                $temp[$key] = $val;
            }
        }

        //将新数据加到要更新的数据中,只更新有改动的字段
        foreach ($tmp as $key => $item) {
            if ($item != null && $key != 'member_id') {
                $temp['address'][$key] = $item;
            }
        }
        $data = json_encode($temp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $res = $member->where(['id' => $tmp['member_id']])->update(['address' => $data]);
        //更新
        if ($res) {
            res(null, '修改成功');
        }
        res(null, '修改失败', 'fail', 100);
    }

    //修改头像
    public function upd_member_avatar(Request $request, Member $member)
    {
        $data = $request->only('member_id', 'avatar');
        $role = [
            'member_id' => 'required',
            'avatar' => 'required|image',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'avatar.required' => '头像不能为空！',
            'avatar.image' => '头像格式不正确,必须是 jpeg、bmp、jpg、gif、gpeg、png！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            res(null, $validator->messages()->first(), 'fail', 100);
        }
        //调用公共文件上传
        if (!empty($data['avatar'])) {
            $res = uploadpic('avatar', 'uploads/avatar/member/'.date('Y-m-d'));//
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
            $data['avatar'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $member->avatar;
            if (!empty($ress) && $ress !== 'uploads/avatar/morentouxiang.png') {
                unlink($ress);
            }
        }
        $res = $member->where('id', $data['member_id'])->update(['avatar' => $data['avatar']]);
        if ($res) {
            res(null, '修改成功');
        }
        res(null, '失败', 'fail', 100);
    }

    /**
     *  展示个人信息页面
     */
    public function show_member(Request $request, Member $member)
    {
        $id = $request->only("id");
        $data = $member->select('avatar', 'nickname', 'sex', 'city', 'phone', 'address', 'api_token')->where(['id' => $id])->first();
        $arr['nickname'] = $data['nickname'];
        $arr['sex'] = $data['sex'];
        $arr['city'] = $data['city'];
        $arr['phone'] = $data['phone'];
        return ['status' => 'success', 'code' => 200,
            'data' => json_encode($arr, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * 先验证api_token
     */
    public function check_api_token()
    {
        $res = Auth::guard('api')->user();
        if (empty($res)) {
            res(null, '登录信息已过期', 'fail', 103);
        }
        $data = [
            'id' => $res['id'],
            'nickname' => $res['nickname'],
            'api_token' => $res['api_token'],
        ];
        res($data);
    }

    /**
     *   会员登录
     */
    public function MemberLogin(Request $request, Member $member)
    {
        // 判断是否是post提交数据
        if ($request->isMethod('get')) {
            $data = $request->only("phone", "password");
            $data['phone'] = empty($data['phone']) ? null : (float)$data['phone'];
            $role = [
                'phone' => 'required|regex:/1[3456789]\d{9}$/',
                'password' => 'required',
            ];
            $message = [
                'phone.required' => '手机号码不能为空！',
                'phone.regex' => '手机号码格式不正确！',
                'password.required' => '密码不能为空！',
            ];
            //过滤信息
            $validator = Validator::make($data, $role, $message);
            if ($validator->fails()) {
                res(null, $validator->messages()->first(), 'fail', 101);
            }
            // 验证登录
            $res = Auth::guard('member')->attempt(['phone' => $data['phone'], 'password' => $data['password'], 'disabled_at' => null], false);//,
            // 手机号码登录
            if ($res) {
                // 登录成功!更新api_token
                $data['api_token'] = str_random(60);
                $member->where(['phone' => $data['phone']])->select()->update(['api_token' => $data['api_token']]);
                $re = $member->where(['phone' => $data['phone']])->select('id')->first();
                //更新登录状态
                $data = [
                    'api_token' => $data['api_token'],
                    'member_id' => $re['id']
                ];
                res($data);
            } else {
                // 登录失败！
                res(null, '密码错误', 'fail', 107);
            }
        }
        res(null, '非法请求', 'fail', 106);
    }

    /**
     * 我的->首页
     */
    public function my(Request $request, Member $member, Dynamic $dynamic)
    {
        $data = $request->only("member_id");
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
        //根据用户id,找出用户的昵称,头像  他关注的人数,关注他的人数,发布的动态数量
        //可能为空:昵称,动态
        $res1 = $member->select('phone', 'avatar', 'nickname', 'friends_id', 'fans_id')->find($data['member_id']);
        //根据发布时间,找出最新的
        $res2 = $dynamic->orderBy('created_at', 'DESC')->select('member_id', 'img_url')->where('member_id', $data['member_id'])->get();
        $res3['nickname'] = empty($res1['nickname']) ? $res1['phone'] : $res1['nickname'];
        $res3['friends'] = empty($res1['friends_id']) ? 0 : count(json_decode($res1['friends_id'], true));//好友 => 他关注的人
        $res3['fans'] = empty($res1['fans_id']) ? 0 : count(json_decode($res1['fans_id'], true));// 粉丝数量
        $http = $request->server('HTTP_HOST') . '/';
        $res3['avatar'] = $http . $res1['avatar'];
        $res3['dynum'] = count($res2);//他发布的动态数量
        if ($res3['dynum'] == 0) {
            //没有动态,图片为空
            $res3['img_url'] = null;
        } else {
            //有动态,计算图片的数量是否满足5
            $res3['img_url'] = $this->dynums($res2, $http);
        }
        res($res3);
    }

    /**
     *  用户最新发布的五条动态
     */
    public function dynums($data, $http)
    {
        //取出每条记录的图片,再将所有图片的url存入,只保留5条并返回
        foreach ($data as $k => $v) {
            $img = json_decode($v['img_url'], true);
            foreach ($img as $key => $val) {
                $arr[] = $http . $val;
            }
        }
        if (count($arr) > 5) {
            while (count($arr) > 5) {
                array_pop($arr);
            }
        }
        return $arr;
    }

    /**
     * 关注页面详情
     */
    public function my_focus(Request $request, Member $member, Admin $admin)
    {
        $data = $request->only("member_id");
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
        //找出用户关注的所有人
        $res1 = $member->select('friends_id')->find($data['member_id']);
        if (empty($res1['friends_id'])) {
            //用户还没关注任何人
            res(null, '查询成功,用户还没关注任何人', 'success', 201);
        } else {
            $fid = json_decode($res1['friends_id'], true);
            $a = 0;
            foreach ($fid as $k => $v) {
                if (strstr($v, "a")) {
                    $id = str_replace('a', '', $v);
                    $row[$a]['focus_id'] = $id;
                    $res2 = $admin->select('username', 'avatar')->find($id);
                    $row[$a]['flag'] = 3;
                    $row[$a]['nickname'] = $res2['username'];
                    $row[$a]['avatar'] = $request->server('HTTP_HOST') . '/' . $res2['avatar'];
                } else {
                    $row[$a]['focus_id'] = $v;
                    $res2 = $member->select('nickname', 'avatar', 'is_admin', 'sex')->find($v);
                    if ($res2['is_admin'] == 2) {
                        $row[$a]['flag'] = $res2['sex'];//1=女2=男
                    } else {
                        $row[$a]['flag'] = 3;
                    }
                    $row[$a]['nickname'] = $res2['nickname'];
                    $row[$a]['avatar'] = $request->server('HTTP_HOST') . '/' . $res2['avatar'];
                }
                $a = $a + 1;
            }
        }
        res($row);
    }

    /**
     * 我的->粉丝列表
     */
    public function my_fans(Request $request, Member $member)
    {
        $data = $request->only("member_id");
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
        //不考虑官方关注别人
        //先找出粉丝的id
        $res1 = $member->select('fans_id')->find($data['member_id']);
        if (empty($res1['fans_id'])) {
            //为空,直接返回
            res(null, '查询成功', 'success', 201);
        } else {
            //不为空
            $ids = json_decode($res1['fans_id'], true);
            $res = $member->select('nickname', 'avatar', 'is_admin', 'id', 'sex')->whereIn('id', $ids)->get();
            foreach ($res as $k => $v) {
                $arr[$k]['fans_id'] = $v['id'];
                if ($v['is_admin'] == 1) {
                    //官方
                    $arr[$k]['flag'] = 3;
                } else {
                    $arr[$k]['flag'] = $v['sex'];
                }
                $arr[$k]['nickname'] = $v['nickname'];
                $arr[$k]['avatar'] = $request->server('HTTP_HOST') . '/' . $v['avatar'];
            }
        }
        res($arr);
    }

    /**
     * 搜索用户
     */
    public function search_user(Request $request, Member $member, Admin $admin)
    {
        $data = $request->only("nickname", 'member_id');
        $role = [
            'nickname' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'nickname.required' => '用户昵称不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //先根据用户名,进行模糊搜索
        $res1 = $member->select('id', 'is_admin', 'nickname', 'avatar', 'sex')->where('nickname', 'like', '%' . $data['nickname'] . '%')->get();
        $res2 = $admin->select('id', 'username', 'avatar')->where('username', 'like', '%' . $data['nickname'] . '%')->get();
        //找出自己的已关注列表
        $res3 = $member->select('friends_id')->find($data['member_id']);
        $friends_id = empty($res3['friends_id']) ? false : json_decode($res3['friends_id'], true);
        if (empty($res1[0]) && empty($res2[0])) {
            res(null, '查询成功', 'success', 201);
        }
        $arr = [];
        //说明至少有一个不为空
        $as = 0;
        if (!empty($res1[0])) {
            //用户表
            foreach ($res1 as $k => $v) {
                $arr[$as]['user_id'] = $v['id'];
                if ($v['is_admin'] == 1) {
                    $arr[$as]['flag'] = 3;//官方的人员
                } else {
                    $arr[$as]['flag'] = $v['sex'];//性别
                }
                $arr[$as]['nickname'] = $v['nickname'];
                $arr[$as]['avatar'] = $request->server('HTTP_HOST') . '/' . $v['avatar'];
                //是否已关注
                if ($friends_id != false) {
                    //不为空,判断是否在里面
                    $arr[$as]['is_focus'] = in_array($v['id'], $friends_id) ? 1 : 2;
                } else {
                    $arr[$as]['is_focus'] = 2;//没关注  1=已关注,2=没关注
                }
                $as = $as + 1;
            }
        }
        if (!empty($res2[0])) {
            //管理表
            foreach ($res2 as $k => $v) {
                $arr[$as]['user_id'] = $v['id'];
                $arr[$as]['flag'] = 3;
                $arr[$as]['nickname'] = $v['username'];
                $arr[$as]['avatar'] = $request->server('HTTP_HOST') . '/' . $v['avatar'];
                if ($friends_id != false) {
                    //不为空,判断是否在里面
                    $arr[$as]['is_focus'] = in_array('a' . $v['id'], $friends_id) ? 1 : 2;
                } else {
                    $arr[$as]['is_focus'] = 2;//没关注
                }
                $as = $as + 1;
            }
        }
        res($arr);
    }

    /**
     * 查看粉丝/用户的详情页面
     */
    public function show_user_content(Request $request, Member $member, Dynamic $dynamic, Topic $topic, Admin $admin, Comment $comment)
    {
        $data = $request->only('member_id', "user_id", 'is_admin');
        $role = [
            'member_id' => 'required',
            'user_id' => 'required',
            'is_admin' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'user_id.required' => '被查看的用户id不能为空！',
            'is_admin.required' => '是否为官方不能为空,1=是官方,2=不是！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查看是否已经关注
        $res1 = $member->select('friends_id')->find($data['member_id']);
        $friends_id = empty($res1['friends_id']) ? false : json_decode($res1['friends_id']);
        if ($friends_id == false) {
            $arr['is_focus'] = 2;
        } else {
            //如果该用户的Id在当前用户的粉丝列表中,说明已经关注
            if (in_array($data['user_id'], $friends_id) || in_array('a' . $data['user_id'], $friends_id)) {
                $arr['is_focus'] = 1;
            } else {
                $arr['is_focus'] = 2;
            }
        }
        //先判断是用户还是管理
        if ($data['is_admin'] == 2) {
            //该用户的头像,名称,关注,粉丝,
            $res = $member->select('nickname', 'avatar', 'friends_id', 'fans_id', 'phone', 'sex')->find($data['user_id']);
            $arr['nickname'] = empty($res['nickname']) ? $res['phone'] : $res['nickname'];
            //是用户,根据该用户的Id,找出所有动态,以倒序
            $dy = $dynamic->where('member_id', $data['user_id'])->select('id', 'img_url', 'content', 'addres', 'nice_num', 'created_at')->orderBy('created_at', 'DESC')->get();
            if (empty($dy[0])) {
                //没有发布过动态
                $arr['list'] = null;
            } else {
                //头像和昵称就不重复找了,直接用上面的
                foreach ($dy as $k => $v) {
                    #评论
                    //根据每一条动态的id,找出他的评论
                    $cm = $comment->select('member_id', 'content')->orderBy('created_at', 'DESC')->where('dy_id', $v['id'])->get();
                    if (empty($cm[0])) {
                        $arr['list'][$k]['comment_num'] = 0;//评论数量为0
                        $arr['list'][$k]['comment_content'] = null;//最新评论为空
                        $arr['list'][$k]['comment_user'] = null;//评论人
                    } else {
                        $arr['list'][$k]['comment_num'] = count($cm);//评论数量
                        $arr['list'][$k]['comment_content'] = $cm[0]['content'];//最新评论
                        $nickname = $member->select('nickname', 'phone')->find($cm[0]['member_id']);
                        $nickname = empty($nickname['nickname']) ? $nickname['phone'] : $nickname['nickname'];
                        $arr['list'][$k]['comment_user'] = $nickname;//评论人
                    }
                    #动态
                    //点赞数量,标签,发布地点,图片
                    $arr['list'][$k]['record_id'] = $v['id'];//这条记录的Id
                    //当前用户是否为这条记录点赞过
                    if (empty($v['nice_num'])) {
                        $nice_num = 0;
                        $is_nice = 1;//可以点赞
                    } else {
                        $nice = json_decode($v['nice_num'], true);
                        $nice_num = count($nice);
                        //当前用户是否点过赞
                        $is_nice = array_key_exists($data['member_id'], $nice) ? 2 : 1;
                    }
                    $arr['list'][$k]['nice_num'] = $nice_num;//点赞数量
                    $arr['list'][$k]['is_nice'] = $is_nice;//是否可以点赞 1=可以点赞,2=已经点赞
                    $arr['list'][$k]['flag'] = $res['sex'];//标签
                    $arr['list'][$k]['addres'] = $v['addres'];//地点
                    $arr['list'][$k]['time'] = format_date($v['created_at']);//发布时间
                    $arr['list'][$k]['subject_catename'] = null;//动态是没有话题头的#xxx#
                    $arr['list'][$k]['content'][] = $v['content'];//动态的内容
                    $img = json_decode($v['img_url'], true);
                    foreach ($img as $key => $val) {
                        $arr['list'][$k]['img_url'][] = $request->server('HTTP_HOST') . '/' . $val;//发布时间
                    }
                }
            }
        } else {
            //是官方,找出他的话题
            $res = $admin->select('username', 'avatar', 'friends_id', 'fans_id')->find($data['user_id']);
            $arr['nickname'] = $res['username'];
            $to = $topic->where('admin_id', $data['user_id'])->select('id', 'img_url', 'content', 'addres', 'nice_num', 'created_at', 'subjec_catename')->orderBy('created_at', 'DESC')->get();
            if (empty($to[0])) {
                //没有发布过话题
                $arr['list'] = null;
            } else {
                //头像和昵称就不重复找了,直接用上面的
                foreach ($to as $k => $v) {
                    #评论
                    //根据每一条话题的id,找出他的评论
                    $cm = $comment->select('member_id', 'content')->where('to_id', $v['id'])->orderBy('created_at', 'DESC')->get();
                    if (empty($cm[0])) {
                        $arr['list'][$k]['comment_num'] = 0;//评论数量为0
                        $arr['list'][$k]['comment_content'] = null;//最新评论为空
                        $arr['list'][$k]['comment_user'] = null;//评论人
                    } else {
                        $arr['list'][$k]['comment_num'] = count($cm);//评论数量
                        $arr['list'][$k]['comment_content'] = $cm[0]['content'];//最新评论
                        $nickname = $member->select('nickname', 'phone')->find($cm[0]['member_id']);//根据第一条评论的评论人id,找出这个人的昵称
                        $nickname = empty($nickname['nickname']) ? $nickname['phone'] : $nickname['nickname'];
                        $arr['list'][$k]['comment_user'] = $nickname;//评论人
                    }
                    #话题
                    //点赞数量,标签,发布地点,图片
                    $arr['list'][$k]['record_id'] = $v['id'];//这条记录的Id
                    if (empty($v['nice_num'])) {
                        $nice_num = 0;
                        $is_nice = 1;//可以点赞
                    } else {
                        $nice = json_decode($v['nice_num'], true);
                        $nice_num = count($nice);
                        //当前用户是否点过赞
                        $is_nice = array_key_exists($data['member_id'], $nice) ? 2 : 1;
                    }
                    $arr['list'][$k]['nice_num'] = $nice_num;//点赞数量
                    $arr['list'][$k]['is_nice'] = $is_nice;//是否可以点赞 1=可以点赞,2=已经点赞
                    $arr['list'][$k]['flag'] = 3;//标签
                    $arr['list'][$k]['addres'] = $v['addres'];//地点
                    $arr['list'][$k]['time'] = format_date($v['created_at']);//发布时间
                    $arr['list'][$k]['subject_catename'] = $v['subjec_catename'];//动态是没有话题头的#xxx#
                    $arr['list'][$k]['content'] = json_decode($v['content'], true);//话题的内容
                    $img = json_decode($v['img_url'], true);
                    foreach ($img as $key => $val) {
                        $arr['list'][$k]['img_url'][] = $request->server('HTTP_HOST') . '/' . $val;//发布时间
                    }
                }
            }
        }
        $arr['avatar'] = $request->server('HTTP_HOST') . '/' . $res['avatar'];
        $arr['friends'] = count(json_decode($res['friends_id'], true));//关注
        $arr['fans'] = count(json_decode($res['fans_id'], true));//粉丝
        res($arr);
    }

    /**
     *点赞
     */
    public function nice(Request $request, Dynamic $dynamic, Topic $topic)
    {
        $data = $request->only("member_id", 'record_id', 'subject_catename');
        $role = [
            'member_id' => 'required',
            'record_id' => 'required',//被点赞的记录的id
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'record_id.required' => '被点赞的记录的id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //点赞需要:当前用户的id,被点赞的是动态还是话题,被点赞的记录id
        //先判断是否为空
        if ($data['subject_catename'] == 1) {
            //为空,代表他是动态
            $res = $dynamic->select('nice_num')->find($data['record_id']);
            $time = date('YmdHis', time());
            if (empty($res['nice_num'])) {
                //直接插入,返回1
                $arr = [$data['member_id'] => $time];//以用户为键
                $dynamic->where('id', $data['record_id'])->update(['nice_num' => json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                $arr = ['nice_num' => 1];
                res($arr, '成功');
            }
            //不为空,先取出,并循环
            $nice = json_decode($res['nice_num'], true);
            if (array_key_exists($data['member_id'], $nice)) {
                res(null, '已经点赞过了', 'success', 202);//存在,不能点赞
            }
            //不在里面
            $nice[$data['member_id']] = $time;
            $res = $dynamic->where('id', $data['record_id'])->update(['nice_num' => json_encode($nice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            if ($res) {
                $arr = ['nice_num' => count($nice)];
                res($arr, '成功');
            }
            res(null, '失败', 'fail', 100);//存在,不能点赞
        } else {
            //是话题
            $res = $topic->select('nice_num')->find($data['record_id']);
            $time = date('YmdHis', time());
            if (empty($res['nice_num'])) {
                //直接插入,返回1
                $arr = [$data['member_id'] => $time];//以用户为键
                $topic->where('id', $data['record_id'])->update(['nice_num' => json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                $arr = ['nice_num' => 1];
                res($arr, '成功');
            }
            //不为空,先取出,并循环
            $nice = json_decode($res['nice_num'], true);
            if (array_key_exists($data['member_id'], $nice)) {
                res(null, '已经点赞过了', 'success', 202);//存在,不能点赞
            }
            //不在里面
            $nice[$data['member_id']] = $time;
            $res = $topic->where('id', $data['record_id'])->update(['nice_num' => json_encode($nice, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            if ($res) {
                $arr = ['nice_num' => count($nice)];
                res($arr, '成功');
            }
            res(null, '失败', 'fail', 100);//存在,不能点赞
        }
    }

    /**
     *取消关注
     */
    public function cancel_focus(Request $request, Member $member, Admin $admin)
    {
        $data = $request->only("member_id", 'user_id', 'is_admin');
        $role = [
            'member_id' => 'required',
            'user_id' => 'required | exists:member,id',
            'is_admin' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'user_id.required' => '被关注人的id不能为空！',
            'user_id.exists' => '被关注人不合法！',
            'is_admin.required' => '是否为官方不能为空,1=是官方,2=不是！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //判断是否是官方,如果是官方,则去管理员表,否则则去用户表
        if ($data['is_admin'] == 2) {
            //要取消关注的是用户,先删除当前用户里的关注字段,再删除被关注人的粉丝字段
            $res1 = $member->select('friends_id')->find($data['member_id']);
            //先删除当前用户里的粉丝
            $friends_id = json_decode($res1['friends_id'], true);
            if (empty($friends_id) || !in_array($data['user_id'], $friends_id)) {
                res(null, '失败', 'fail', 100);
            }
            $arr1 = [];
            $arr2 = [];
            foreach ($friends_id as $k => $v) {
                if ($v != $data['user_id']) {
                    $arr1[] = $v;
                }
            }
            $res2 = $member->select('fans_id')->find($data['user_id']);
            $fans_id = json_decode($res2['fans_id'], true);
            foreach ($fans_id as $k => $v) {
                if ($v != $data['member_id']) {
                    $arr2[] = $v;
                }
            }
            $friends_id = empty($arr1) ? $arr1 = null : json_encode($arr1);
            $fans_id = empty($arr2) ? $arr2 = null : json_encode($arr2);
            $res3 = $member->where('id', $data['member_id'])->update(['friends_id' => $friends_id]);
            $res4 = $member->where('id', $data['user_id'])->update(['fans_id' => $fans_id]);
        } else {
            //要关注的是官方,先删除用户里的关注,再删除管理里的粉丝
            $res1 = $member->select('friends_id')->find($data['member_id']);
            $friends_id = json_decode($res1['friends_id'], true);
            if (empty($friends_id) || !in_array('a' . $data['user_id'], $friends_id)) {
                res(null, '失败', 'fail', 100);
            }
            $arr1 = [];
            $arr2 = [];
            //先删除当前用户里的粉丝
            foreach ($friends_id as $k => $v) {
                if ($v != 'a' . $data['user_id']) {
                    $arr1[] = $v;
                }
            }
            $res2 = $admin->select('fans_id')->find($data['user_id']);
            $fans_id = json_decode($res2['fans_id'], true);
            foreach ($fans_id as $k => $v) {
                if ($v != $data['member_id']) {
                    $arr2[] = $v;
                }
            }
            $friends_id = empty($arr1) ? $arr1 = null : json_encode($arr1);
            $fans_id = empty($arr2) ? $arr2 = null : json_encode($arr2);
            $res3 = $member->where('id', $data['member_id'])->update(['friends_id' => $friends_id]);
            $res4 = $admin->where('id', $data['user_id'])->update(['fans_id' => $fans_id]);
        }
        //最后,返回当前用户关注的人数
        res(null, '成功');
    }

    /**
     *关注某人
     */
    public function focus(Request $request, Member $member, Admin $admin)
    {
        $data = $request->only("member_id", 'user_id', 'is_admin');
        $role = [
            'member_id' => 'required | exists:member,id',
            'user_id' => 'required | exists:member,id',
            'is_admin' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'member_id.exists' => '用户不合法！',
            'user_id.required' => '被关注人的id不能为空！',
            'user_id.exists' => '被关注的人不存在！',
            'is_admin.required' => '是否为官方不能为空,1=是官方,2=不是！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        if ($data['is_admin'] == 2) {
            //要关注的是用户
            //先往当前用户的好友(friends_id)中,插入被关注人的id
            $res1 = $member->select('friends_id')->find($data['member_id']);
            if (empty($res1['friends_id'])) {
                $row1 = json_encode([0 => $data['user_id']]);
            } else {
                $arr = json_decode($res1['friends_id'], true);
                //查看用户是否已经关注过了
                foreach ($arr as $k => $v) {
                    if ($v == $data['user_id']) {
                        res(null, '已经关注过了', 'success', 202);
                    }
                }
                if ($data['is_admin'] == 2) {
                    array_push($arr, $data['user_id']);
                } else {
                    array_push($arr, 'a' . $data['user_id']);////////////////////////
                }
                $row1 = json_encode($arr);
            }
            //再往被关注的人的粉丝中(fans_id),插入当前用户的id
            $res2 = $member->select('fans_id')->find($data['user_id']);
            if (empty($res2['fans_id'])) {
                $row2 = json_encode([0 => $data['member_id']]);
            } else {
                $arr = json_decode($res2['fans_id'], true);
                array_push($arr, $data['member_id']);
                $row2 = json_encode($arr);

            }
            $res1 = $member->where('id', $data['member_id'])->update(['friends_id' => $row1]);
            $res2 = $member->where('id', $data['user_id'])->update(['fans_id' => $row2]);
        } else {
            //要关注的是官方
            $res1 = $member->select('friends_id')->find($data['member_id']);
            if (empty($res1['friends_id'])) {
                $row1 = json_encode([0 => 'a' . $data['user_id']]);
            } else {
                $arr = json_decode($res1['friends_id'], true);
                //查看用户是否已经关注过了
                foreach ($arr as $k => $v) {
                    if ($v == 'a' . $data['user_id']) {
                        res(null, '已经关注过了', 'success', 202);
                    }
                }
                array_push($arr, 'a' . $data['user_id']);
                $row1 = json_encode($arr);
            }
            //再往被关注的人的粉丝中(fans_id),插入当前用户的id
            $res2 = $admin->select('fans_id')->find($data['user_id']);
            if (empty($res2['fans_id'])) {
                $row2 = json_encode([0 => $data['member_id']]);
            } else {
                $arr = json_decode($res2['fans_id'], true);
                array_push($arr, $data['member_id']);
                $row2 = json_encode($arr);
            }
            $res1 = $member->where('id', $data['member_id'])->update(['friends_id' => $row1]);
            $res2 = $admin->where('id', $data['user_id'])->update(['fans_id' => $row2]);
        }
        //最后,返回当前用户关注的人数
        res(null, '关注成功');
    }


    /**
     *  短信发送
     */
    public function Sms(Request $request, Redis $redis)
    {
        //过滤
        if (!$request->isMethod('get')) {
            res(null, '非法的请求数据', 'fail', 106);
        }
        $role = [
            'phone' => 'required|regex:/1[3456789]\d{9}$/',
        ];
        $message = [
            'phone.required' => '手机号码不能为空！',
            'phone.regex' => '手机号码格式不正确！',
        ];
        $data = $request->only('phone');
        $data['phone'] = empty($data['phone']) ? null : (float)$data['phone'];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //生成验证码并发送短信
        $to = $data['phone'];//要发送的手机号码
        $rand = rand(1000, 10000);//生成随机验证码
        $phonecode = $rand;//手机验证码
        $alisms = app('alisms.note');
        $res = $alisms->send('register', $to, ['code' => $phonecode]);//code 为模板中的变量名
//        返回发送状态
        if ($res === 'isv.BUSINESS_LIMIT_CONTROL---------触发分钟级流控Permits:1') {
            res(null, '每分钟只能发送一次验证码', 'fail', 100);
        } elseif ($res === 'isv.BUSINESS_LIMIT_CONTROL---------触发小时级流控Permits:5') {
            res(null, '每小时只能发送5次验证码', 'fail', 100);
        } elseif ($res === "isv.BUSINESS_LIMIT_CONTROL---------触发天级流控Permits:10") {
            res(null, '每天只能发送10次验证码', 'fail', 100);
        } elseif ($res === true) {
            Redis::setex($to, 60, $phonecode);
            res(null, '发送成功');
        } else {
            res(null, '网络繁忙,请稍后再试', 'fail', 100);
        }
    }

    /**
     *  会员注册
     */
    public function MemberReg(Request $request, Member $member, Redis $redis, Admin $admin)
    {
        //过滤
        if (!$request->isMethod('get')) {
            res(null, '非法的请求数据', 'fail', 106);
        }
        $res2 = $admin->select('id', 'fans_id')->get();//找出所有官方的id,再找出他的粉丝
        $role = [
            'phone' => 'required|unique:member|regex:/1[3456789]\d{9}$/',//
            'password' => 'required|between:6,16',
            'smskey' => 'required',
        ];
        $message = [
            'phone.required' => '手机号码不能为空！',
            'phone.regex' => '手机号码格式不正确！',
            'phone.unique' => '手机号码已被注册！',
            'password.required' => '密码不能为空！',
            'password.between' => '密码应为6-16位！',
            'smskey.required' => '验证码不能为空！',
        ];
        $data = $request->only('phone', 'password', 'smskey');
        $data['phone'] = empty($data['phone']) ? null : (float)$data['phone'];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        } else if (empty(Redis::get($data['phone'])) || (Redis::get($data['phone']) != $data['smskey'])) {
            //判断验证码是否过期或验证失败
            res(null, '验证码错误或已过期', 'fail', 108);
        }
        //数据调整
        $data['password'] = bcrypt($data['password']);
        $data['api_token'] = str_random(60);
        //默认关注官方,先找出所有管理员的id,将id存入
        $res2 = $admin->select('id', 'fans_id')->get();//找出所有官方的id,再找出他的粉丝
        foreach ($res2 as $k => $v) {
            $arr[] = 'a' . $v['id'];
        }
        //默认关注所有官方的人
        $data['friends_id'] = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
         //默认昵称为该用户电话号码
        $data['nickname'] = empty($data['phone']) ? null : (float)$data['phone'];
        //注册
        $res = $member->create($data);
        if ($res->id) {
            //将用户注册后的id,存入所有官方里的人的粉丝中
            foreach ($res2 as $k => $v) {
                if (!empty($v['fans_id'])) {
                    //如果不为空,需要先解开
                    $fans = json_decode($v['fans_id'], true);
                } else {
                    $fans = [];
                }
                //将当前的管理员的粉丝刷新
                array_push($fans, $res->id);
                $fans = json_encode($fans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $re = $admin->where('id', $v['id'])->update(['fans_id' => $fans]);
                $fans = [];
            }
            $data = [
                'api_token' => $data['api_token'],
                'member_id' => $res->id
            ];
            res($data, '注册成功');
        }
        res(null, '网络繁忙', 'fail', 100);
    }

    /**
     * 个人资料->首页
     */
    public function my_profile(Request $request, Member $member)
    {
        $data = $request->only("member_id");
        $role = [
            'member_id' => 'required | exists:member,id',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'member_id.exists' => '用户不合法！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //找出用户的昵称,性别,城市,手机号码,(收货地址)
        $res = $member->select('nickname', 'sex', 'city', 'phone', 'address')->find($data['member_id'])->toArray();
        $address = empty($res['address']) ? null : json_decode($res['address'], true);
        $res['nickname'] = empty($res['nickname']) ? $res['phone'] : $res['nickname'];
        $res['address'] = empty($address) ? $address : $address['address'];
        res($res);
    }

    /**
     * 单个用户删除
     */
    public function destroy($id)
    {
        $member = new Member();
        $member = $member->find($id);
        $res = $member->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

}
