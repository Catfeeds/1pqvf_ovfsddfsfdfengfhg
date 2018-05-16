<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appraise;
use App\Models\Coupon;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\Picture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\DB;

class AppraiseController extends Controller
{
    /**
     * 评价表
     */
    public function appraise(Request $request, Appraise $appraise, Merchant $merchant)
    {
        $data = $request->only('member_id', 'merchant_id', 'appraise', 'content', 'img1', 'img2', 'img3', 'img4');
        $role = [
            'member_id' => 'required',
            'merchant_id' => 'required | exists:merchant,id',
            'appraise' => 'required|between:1,5|integer',//星级
            'content' => 'required',
            'img1' => 'nullable|image',
            'img2' => 'nullable|image',
            'img3' => 'nullable|image',
            'img4' => 'nullable|image',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'merchant_id.required' => '商家id不能为空！',
            'merchant_id.exists' => '非法请求！',
            'appraise.required' => '评价不能为空！',
            'appraise.between' => '评价不合法,只能介于1-5之间！',
            'appraise.integer' => '评价不合法,只能介于1-5之间！',
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
        //数据调整
        $data['mem_id'] = $data['member_id'];
        $data['mer_id'] = $data['merchant_id'];
        //先判断该用户有没有对这家店评价过
        $res = $appraise->where('mer_id', $data['mer_id'])->where('mem_id', $data['mem_id'])->first();
        if (!empty($res)) {
            res(null, '已经评价过', 'success', 202);
        }
        //处理图片 只要有一张图片,则进入
        if (!empty($data['img1']) || !empty($data['img2']) || !empty($data['img3']) || !empty($data['img4'])) {
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
                $arr[] = $res;
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
            if (!empty($data['img4'])) {
                $res = uploadpic('img4', 'uploads/img_url');//
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
            $data['img_url'] = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $num = $appraise->where('mer_id', $data['mer_id'])->select('appraise')->get();
        $count = count($num);
        $re = 0;
        for ($i = 0; $i < $count; $i++) {
            $re += $num[$i]['appraise'];
        }
        $re = $re + $data['appraise'];
        $re = ceil($re / ($count + 1));
        if ($re < 1) {
            $re = 1;
        } elseif ($re > 5) {
            $re = 5;
        }
        DB::transaction(function () use ($merchant, $appraise, $data, $re) {
            //将新的星级赋予
            $res1 = $merchant->where('id', $data['mer_id'])->select()->update(['appraise_n' => $re]);
            $res2 = $appraise->create($data);
            if (!$res2->id && !$res1) {
                DB::rollback();//事务回滚
                res(null, '评价失败', 'fail', 100);
            }
            DB::commit();
            res(null, '评价成功');
        });
    }

    /**
     *商家评价-> 未评价
     */
    public function no_evaluation(Request $request, Merchant $merchant, Picture $picture, Member $member, Coupon $coupon, Appraise $appraise)
    {
        $data = $request->only('member_id', 'lat', 'lng');
        $role = [
            'member_id' => 'required',
            'lat' => 'required',
            'lng' => 'required'
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $member->select('coupon_id')->find($data['member_id']);
        if (empty($res1['coupon_id'])) {
            res(null, '该用户还没获得任何优惠券', 'fail', 201);
        }
        //根据用户拥有的优惠券字段,找出对应的已经使用的优惠券
//        $res2 = $coupon->select(DB::Raw('DISTINCT(merchant_id)'))->whereIn('id',$res1['coupon_id'])->where('status',2)->get(); //可以,但是查出来的不是所有的
        $res2 = $coupon->select('merchant_id')->whereIn('id', $res1['coupon_id'])->where('status', 2)->groupBy('merchant_id')->get();//这条最棒,关闭了严格模式
        if (empty($res2[0])) {
            res(null, '用户还没使用过任何优惠券', 'fail', 201);
        }
        foreach ($res2 as $key => $val) {
            $arr[] = $val['merchant_id'];
        }
        foreach ($arr as $k => $v) {
            $res = $appraise->where('mer_id', $v)->where('mem_id', $data['member_id'])->select('id')->first();
            if (!empty($res['id'])) {
                //此商家已经评价过了
                continue;
            }
            $arr2[] = $v;
        }
        if (empty($arr2)) {
            //没有可以评价的了
            res(null, '没有可以评价的商家了', 'fail', 201);
        }
        //找出这些商家的封面图,昵称,标签,id,经纬度,
        $res = $merchant->select('store_image', 'nickname', 'labelling', 'id', 'latitude')->whereIn('id', $arr2)->get();
        $latitude = bd_encrypt($data['lat'], $data['lng']);
        $arr = [];
        foreach ($res as $k => $v) {
            //经纬度换算为距离
            $distance = getDistance($latitude['lat'], $latitude['lng'], $v['latitude']['lat'], $v['latitude']['lng']);
            $arr[] = [
                'merchant_id' => $v['id'],//id
                'store_image' => $request->server('HTTP_HOST') . '/' . $v['store_image'],//店铺图片
                'nickname' => $v['nickname'],//昵称
                'distance' => $distance,//距离(米)
                'labelling' => $v['labelling'],//标签
            ];
        }
        res($arr);
    }

    /**
     * 商家打星,已评价页面
     */
    public function have_evaluation(Request $request, Appraise $appraise, Merchant $merchant)
    {
        $data = $request->only('member_id', 'lat', 'lng');
        $role = [
            'member_id' => 'required',
            'lat' => 'required',
            'lng' => 'required'
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //找出评价的星级,商家的id
        $res1 = $appraise->select('mer_id', 'appraise')->where('mem_id', $data['member_id'])->get();
        if (empty($res1[0])) {
            res(null, '用户还没有评价过的商家', 'success', 201);
        }
        $latitude = bd_encrypt($data['lat'], $data['lng']);
        foreach ($res1 as $k => $v) {
            //根据评价表里的商家id,找出昵称,封面,商家的位置(计算距离),labelling
            $res2 = $merchant->select('nickname', 'store_image', 'latitude', 'labelling')->find($v['mer_id']);
            //计算距离
            $distance = getDistance($latitude['lat'], $latitude['lng'], $res2['latitude']['lat'], $res2['latitude']['lng']);
            $arr[] = [
                'store_image' => $request->server('HTTP_HOST') . '/' . $res2['store_image'],//店铺图
                'nickname' => $res2['nickname'],//昵称
                'distance' => $distance,//距离(米)
                'labelling' => $res2['labelling'],//标签
                'appraise' => $v['appraise'],//评价的星级
            ];
        }
        res($arr);
    }

    /**
     * 评价页面(详情)
     */
    public function show_evaluation(Request $request, Merchant $merchant, Picture $picture, Member $member)
    {
        $data = $request->only('merchant_id', 'member_id');
        $role = [
            'merchant_id' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'merchant_id.required' => '商家id不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        $res1 = $merchant->select('avatar', 'img_url', 'address', 'nickname','appraise_n')->find($data['merchant_id']);
        //找出当前商家,拥有几种优惠券(有多少种优惠券图片,就有多少个活动)
        $num = count($picture->select('id')->where('merchant_id', $data['merchant_id'])->get());
        //再查看用户是否关注了此商家
        $res2 = $member->select('merchant_id')->find($data['member_id']);
        $is_collection = 2;//没有收藏
        if (!empty($res2['merchant_id'])) {
            //不为空
            $merchant_id = json_decode($res2['merchant_id'], true);
            //并且当前查看的商家不在集合中
            $is_collection = in_array($data['merchant_id'], $merchant_id) ? 1 : 2;
        }
        $arr = [
            'img_url' => $request->server('HTTP_HOST') . '/' . $res1['img_url'],//封面
            'avatar' => $request->server('HTTP_HOST') . '/' . $res1['avatar'],//头像
            'nickname' => $res1['nickname'],//店名
            'address' => $res1['address'],//店名
            'num' => $num,//该商家的优惠活动的数量
            'appraise_n' => $res1['appraise_n'],//该商家的星级
            'is_collection' => $is_collection  //该商家是否已经收藏 1=已收藏,2=未收藏
        ];
        res($arr);
    }

    /**
     *公共的图片上传接口
     */
    public function uploads(Request $request)
    {
        $key_name = $request->all();
        foreach ($key_name as $k => $v) {
            if ($k != 'api_token') {
                $key_name = $k;
            }
        }
        $targetDir = 'uploads/' . $key_name; //上传临时地址,可能要更改
        $uploadDir = 'uploads/' . $key_name; //上传地址
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        $fileName = $request->file();//->getClientOriginalName()
        $fileName = $fileName[$key_name]->getClientOriginalName();
        $fileInfo = pathinfo($fileName);
        $extension = $fileInfo['extension'];
        $fileName = 'img' . time() . rand(100000, 999999) . '.' . $extension;//$file->getClientOriginalName();
        $filePath = $targetDir . '/' . $fileName;
        $uploadPath = $uploadDir . '/' . $fileName;
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                res(null, '失败', 'fail', 101);
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
            res(null, '失败', 'fail', 101);
        }
        if (!empty($_FILES)) {
            if ($_FILES[$key_name]["error"] || !is_uploaded_file($_FILES[$key_name]["tmp_name"])) {
                res(null, '失败', 'fail', 101);
            }
            if (!$in = @fopen($_FILES[$key_name]["tmp_name"], "rb")) {
                res(null, '失败', 'fail', 101);
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                res(null, '失败', 'fail', 101);
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
        res(['url' => $uploadPath], '成功');
    }

    public function index()
    {
        return view('admin.appraise.index');
    }

    public function ajax_list(Request $request, Appraise $appraise)
    {

        if ($request->ajax()) {
            $data = $appraise->with('member')->with('merchant')->select('id', 'img_url', 'content', 'mer_id', 'mem_id', 'appraise', 'created_at')->get();
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

    public function edit(Appraise $appraise)
    {
        $data['appraiseInfo'] = $appraise;
        return view('admin.appraise.edit', $data);
    }

    public function update(Request $request, Appraise $appraise, Merchant $merchant)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('content', 'appraise', 'mer_id');
        //后台只允许修改评论的内容
        $role = [
            'content' => 'required',
            'mer_id' => 'required',
            'appraise' => 'required|between:1,5|integer',
        ];
        $message = [
            'content.required' => '评价内容不能为空！',
            'appraise.required' => '评价星级不能为空！',
            'mer_id.required' => '被评价的商家找不到(数据出错)！',
            'appraise.between' => '评价不合法,只能介于1-5之间！',
            'appraise.integer' => '评价不合法,只能介于1-5之间！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //调整总星级
        $num = $appraise->where('mer_id', $data['mer_id'])->select('appraise')->get();
        $count = count($num);
        $re = 0;
        for ($i = 0; $i < $count; $i++) {
            $re += $num[$i]['appraise'];
        }
        $re = $re + $data['appraise'];
        $re = ceil($re / ($count + 1));
        if ($re < 1) {
            $re = 1;
        } elseif ($re > 5) {
            $re = 5;
        }
        // 更新数据
        $res = $appraise->update($data);
        if ($res) {
            //更新商家的星级
            $merchant->where('id', $data['mer_id'])->select()->update(['appraise_n' => $re]);
            //更新商家的星级
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $appraise = new Appraise();
        $appraise = $appraise->find($id);
        $res = $appraise->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }
}
