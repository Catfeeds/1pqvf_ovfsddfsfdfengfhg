<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\TakeFlag;
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class TakeFlagController extends Controller
{

    /**
     *  查找周边的旗子
     */
    public function select_takefalg(Request $request)
    {
        $data = $request->only('lat', 'lng');
        $role = [
            'lat' => 'required',
            'lng' => 'required',
        ];
        $message = [
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
//        $arr = bd_encrypt($data['lat'], $data['lng']);//转为百度的经纬度
//        $arr = $this->rand_take($data);//根据百度的经纬度,生成随机的经纬度
        $arr = $this->returnSquarePoint($data['lng'],$data['lat']);//根据百度的经纬度,生成随机的经纬度
        if (empty($arr)) {
            res(null, '网络繁忙,请重新请求', 'fail', 201);
        }
        $row = [];
        foreach ($arr as $k => $v) {
            if (count($row) >= 7) {
                break;
            }
            //将四个角的坐标,重新模糊
            $res = rand_latitude($v['lng'],$v['lat'],1);
            $row[] = [
                'lat'=>$res[0]['lat'],
                'lng'=>$res[0]['lng']
            ];
        }
        res($row);
    }

    private function returnSquarePoint($lng, $lat,$distance = 500)
    {
        $earthdata=6371000;//地球半径，平均半径为6371km
        $dlng =  2 * asin(sin($distance / (2 * $earthdata)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance/$earthdata;
        $dlat = rad2deg($dlat);
        $arr=array(
            'left_top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
            'right_top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
            'left_bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
            'right_bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
        );
        return $arr;
    }

    /*
     * 生成旗子
     */
    public function rand_take($arr, $add = [], $limit = 0, $n = 7)
    {
        //生成
        if (!empty($add)) {
            $add2 = $this->rand_latitude($arr['lng'], $arr['lat'], $n);
            foreach ($add2 as $k => $v) {
                $add[] = adjust($v);
            }
        } else {
            $add = $this->rand_latitude($arr['lng'], $arr['lat'], $n);
            foreach ($add as $k => $v) {
                $add[$k] = adjust($v);
            }
        }
        //判断距离\
        foreach ($add as $k => $v) {
            //现将其经纬度处理
            $ns = getDistance($arr['lat'], $arr['lng'], $v['lat'], $v['lng']);
            if ($ns > 500) {
                unset($add[$k]);
            }
        }
        if (count($add) < $n) {
            if ($limit >= 3) {
                return $add;
            }
            //递归调用自己,将用户的位置,和已生成的add传递
            $add = $this->rand_take($arr, $add, $limit + 1);
        }
        return $add;
    }

    /*
     * 生成五百米内的经纬度
     */
    function rand_latitude($lng, $lat, $n)
    {

        $array = array(0, 1);
        $i = rand(0, 1);
        for ($k = 0; $k < $n; $k++) {
            if ($array[$i] == 0) {
                //如果是0,则增
                $lng_max = $lng + 0.01;
                $lat_max = $lat + 0.001;
                $rl = mt_rand() / mt_getrandmax();
                $arr[$k]['lng'] = ($lng + ($rl * ($lng_max - $lng)));
                $arr[$k]['lat'] = ($lat + ($rl * ($lat_max - $lat)));
                return $arr;
            }
            //否则,则减
            $lng_min = $lng - 0.01;
            $lat_min = $lat - 0.001;
            $rl = mt_rand() / mt_getrandmax();
            $arr[$k]['lng'] = ($lng + ($rl * ($lng - $lng_min)));
            $arr[$k]['lat'] = ($lat + ($rl * ($lat - $lat_min)));
        }
        return $arr;
    }

    /*
     * 获取旗子
     */
    public function get_takefalg(Request $request, TakeFlag $takeFlag)
    {
        $data = $request->only('member_id');
        //旗子数量+1
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
        //查找
        $res = $takeFlag->select('flag_num')->where('member_id', $data['member_id'])->first();
        if (empty($res['flag_num']) || $res['flag_num'] == 0) {
            $flag_num = 1;
        } else {
            $flag_num = $res['flag_num'] + 1;
        }
        $res = $takeFlag->select()->where('member_id', $data['member_id'])->update(['flag_num' => $flag_num]);
        if ($res) {
            res(null, '获取成功');
        }
        res(null, '网络繁忙', 'fail', 100);
    }

    public function index()
    {
        return view('admin.takeflag.index');
    }

    public function ajax_list(Request $request, TakeFlag $takeFlag)
    {
        if ($request->ajax()) {
            $data = $takeFlag->with('member')->select('id', 'member_id', 'flag_num', 'status', 'award', 'delivery')->get();//address
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

    public function showadd($member_id)
    {
        $member = new Member();
        $res = $member->select('address')->find($member_id);
        $data['info'] = $res['address'];
        return view('admin.takeflag.showadd', $data);
    }

    /*
     * 发货
     */
    public function send($meber_id)
    {
        //根据传递过来的用户id,去健康达人中寻找,
        $takeFlag = new TakeFlag();
        $res = $takeFlag->where('member_id', $meber_id)->select('status', 'delivery')->first();
        //已领奖
        if ($res['status'] == 3) {
            //判断是否已经发货
            if (empty($res['delivery'])) {
                $res = $takeFlag->where('member_id', $meber_id)->select()->update(['delivery' => date('Y-m-d H:i:s')]);
                if ($res) {
                    return ['status' => 'success', 'msg' => '成功'];
                }
            }
        }
        return ['status' => 'fail', 'msg' => '用户还没点击领奖,或用户的目标未达成'];
    }

    /*
     * 取消发货
     */
    public function undo($meber_id)
    {
        //根据传递过来的用户id,去健康达人中寻找,
        $takeFlag = new TakeFlag();
        $res = $takeFlag->where('member_id', $meber_id)->select('delivery')->first();
        //已领奖
        //判断是否已经发货
        if (!empty($res['delivery'])) {
            $res = $takeFlag->where('member_id', $meber_id)->select()->update(['delivery' => null]);
            if ($res) {
                return ['status' => 'success', 'msg' => '成功'];
            }
        }
        return ['status' => 'fail', 'msg' => '还未发货'];
    }

}
