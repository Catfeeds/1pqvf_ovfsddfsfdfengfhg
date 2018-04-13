<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\Picture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{

    public function index()
    {
        return view('admin.coupon.index');
    }

    public function create(Request $request, Merchant $merchant, Picture $picture)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        $data['pictureInfo'] = $picture->select('id', 'action', 'merchant_id', 'price')->get();
        return view('admin.coupon.create', $data);
    }

    public function store(Request $request, Coupon $coupon)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('merchant_id', 'note', 'type', 'start_at', 'end_at', 'action', 'price', 'picture_id', 'cr_type', 'address1', 'address2');//latitude经纬度需要通过百度地图查询
        $role = [
            'merchant_id' => 'required',
            'type' => 'required',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|after_or_equal:start_at',
            'action' => 'required',
            'price' => 'required|numeric',
            'picture_id' => 'required|numeric',
            'cr_type' => 'required',
            'note' => 'required',
        ];

        $message = [
            'merchant_id.required' => '所属商家不能为空！',
            'type.required' => '优惠券类型不能为空！',
            'start_at.date' => '开始时间类型错误！',
            'end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'action.required' => '优惠方式不能为空！',
            'price.required' => '优惠价格不能为空！',
            'price.numeric' => '优惠价格不合法！',
            'picture_id.required' => '优惠券图片错误！',
            'picture_id.numeric' => '优惠券图片错误！',
            'cr_type.required' => '生成地区类型不能为空！',
            'note.required' => '描述不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        //否则,则验证上传的excel表是否合法,录入信息
        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //判断是否无期限
        if (!$data['type'] == 1) {
            $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));
            $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));
        } else {
            unset($data['start_at']);
            unset($data['end_at']);
            //获取时,+7天为结束时间
        }
        if ($data['cr_type'] == 1) {
            //按商家,删除区域
            $type = 1;
            if (empty($data['address2'])) {
                return ['status' => 'fail', 'msg' => '生成地区不能为空'];
            }
            //根据address2(获取)
            $data['address'] = $data['address2'];
        } else {
            $type = 2;
            if (empty($data['address1'])) {
                return ['status' => 'fail', 'msg' => '生成地区不能为空'];
            }
            $data['address'] = $data['address1'];
        }
        $latitude = $this->get_latitude($type, $data['address'], 1);
        switch ($latitude) {
            case 2:
                return ['status' => 'fail', 'msg' => '该地址不存在或网络异常,请稍后再试'];
                break;
            case 3:
                return ['status' => 'fail', 'msg' => '该地区所标注为区县,请选择以区域生成类型'];
                break;
            case 4:
                return ['status' => 'fail', 'msg' => '该地址不存在'];
                break;
        }
        //如果唯一,则格式化
        $data['latitude'] = $latitude[0];//json_encode($latitude,JSON_UNESCAPED_UNICODE);
        unset($data['address1']);
        unset($data['address2']);
        //调整时间
        if (!empty($data['start_at'])) {
            if ($data['start_at'] == '1970-01-01 00:00:00' || $data['end_at'] == '1970-01-01 00:00:00') {
                return ['status' => 'fail', 'msg' => '时间格式不合法'];
            }
        }
        //生成优惠券编号  后期优惠券编号要拼接区号
        $code = $this->code();
        $data['cp_id'] = $code;
        $data['status'] = 1;
        //根据最后的经纬度,将所在地区重新调整
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        $url = "http://api.map.baidu.com/geocoder/v2/?location={$data['latitude']['lat']},{$data['latitude']['lng']}&output=json&pois=0&ak={$ak}";
        $addr = file_get_contents($url);
        $addr = json_decode($addr, true);
        $data['address'] = $addr['result']['addressComponent']['district'];
        $data['latitude'] = adjust($data['latitude']);
        $res = $coupon->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    /**
     *  获取随机经纬度
     */
    public function get_latitude($type, $address, $n)
    {
        if ($type == 1) {
            $latitude = Latitude_and_longitude($address, $n);
            switch ($latitude) {
                case 2:
                    return 2;;
                    break;
                case 3:
                    return 3;
                    break;
                case 4:
                    return 4;
                    break;
            }
        } else {
            $latitude = Obtain_a_single_warp($address, $n);
        }
        return $latitude;
    }

    /*
     * 优惠券编号
     */
    private function code()
    {
        $code = date('YmdHis');
        $res = Redis::get('code_number');
        if ($res && $res < 999999) {
            $number = ++$res;
        } else {
            $number = 1;
        }
        Redis::set('code_number', $number);
        $number = str_pad($number, 6, '0', STR_PAD_LEFT);
        return $code . $number;
    }

    /**
     *  批量添加优惠券
     */
    public function creates(Request $request, Merchant $merchant, Picture $picture)
    {
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        //找出目前所有商家,提供手工批量录入,和excel导入
        $data['pictureInfo'] = $picture->select('id', 'action', 'merchant_id', 'price')->get();
        return view('admin.coupon.creates', $data);
    }

    /**
     * 批量优惠券入库
     */
    public function stores(Request $request, Coupon $coupon, Excel $excel)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('type3', 'note', 'note2', 'editorValue', 'number', 'excel', 'merchant_id', 'type', 'start_at', 'end_at', 'action', 'price', 'picture_id', 'cr_type', 'address1', 'address2', 'address3', 'address4', 'merchant_id2', 'picture_id2', 'type2', 'start_at2', 'end_at2', 'action2', 'price2');
        //如果选择的是手工录入
        if ($data['type3'] == 1) {
            $role = [
                'merchant_id' => 'required',
                'type' => 'required',
                'start_at' => 'nullable|date',
                'end_at' => 'nullable|after_or_equal:start_at',
                'action' => 'required',
                'price' => 'required|numeric',
                'picture_id' => 'required|numeric',
                'cr_type' => 'required',
                'note' => 'required',
            ];

            $message = [
                'merchant_id.required' => '所属商家不能为空！',
                'type.required' => '优惠券类型不能为空！',
                'start_at.date' => '开始时间类型错误！',
                'end_at.after_or_equal' => '结束时间必须是开始时间之后！',
                'action.required' => '优惠方式不能为空！',
                'price.required' => '优惠价格不能为空！',
                'price.numeric' => '优惠价格不合法！',
                'picture_id.required' => '优惠券图片错误！',
                'picture_id.numeric' => '优惠券图片错误！',
                'cr_type.required' => '生成类型不能为空！',
                'note.required' => '描述不能为空！',
            ];
            $validator = Validator::make($data, $role, $message);
            //否则,则验证上传的excel表是否合法,录入信息
            //如果验证失败,返回错误信息
            if ($validator->fails()) {
                return ['status' => 'fail', 'msg' => $validator->messages()->first()];
            }
            //判断是否无期限
            if (!$data['type'] == 1) {
                $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));
                $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));
            } else {
                unset($data['start_at']);
                unset($data['end_at']);
                //获取时,+7天为结束时间
            }
            if ($data['cr_type'] == 1) {
                //按商家,删除区域
                $type = 1;
                if (empty($data['address2'])) {
                    return ['status' => 'fail', 'msg' => '生成地区不能为空'];
                }
                $data['address'] = $data['address2'];
            } else {
                $type = 2;
                if (empty($data['address1'])) {
                    return ['status' => 'fail', 'msg' => '生成地区不能为空'];
                }
                $data['address'] = $data['address1'];
            }
            unset($data['address1']);
            unset($data['address2']);
            //调整时间
            if (!empty($data['start_at'])) {
                if ($data['start_at'] == '1970-01-01 00:00:00' || $data['end_at'] == '1970-01-01 00:00:00') {
                    return ['status' => 'fail', 'msg' => '时间格式不合法'];
                }
            }
            //使用说明
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
            //生成优惠券编号
            $data['status'] = 1;
            $number = $data['number'] < 1 ? 1 : $data['number'];
            //生成经纬度
            $latitude = $this->get_latitude($type, $data['address'], $data['number']);
            switch ($latitude) {
                case 2:
                    return ['status' => 'fail', 'msg' => '该地址不存在或网络异常,请稍后再试'];
                    break;
                case 3:
                    return ['status' => 'fail', 'msg' => '该地区所标注为区县,请选择以区域生成类型'];
                    break;
                case 4:
                    return ['status' => 'fail', 'msg' => '该地址不存在'];
                    break;
            }
            $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
            for ($i = 0; $i < $number; $i++) {
                $code = $this->code();
                $data['cp_id'] = $code;
                $data['latitude'] = $latitude[$i];

                //根据最后的经纬度,将所在地区重新调整
                if ($data['cr_type'] == 1) {
                    $url = "http://api.map.baidu.com/geocoder/v2/?location={$data['latitude']['lat']},{$data['latitude']['lng']}&output=json&pois=0&ak={$ak}";
                    $addr = file_get_contents($url);
                    $addr = json_decode($addr, true);
                    $data['address'] = $addr['result']['addressComponent']['district'];
                }
                //调整经纬度长度
                $data['latitude'] = adjust($data['latitude']);
                $res = $coupon->create($data);
            }
            if ($res->id) {
                return ['status' => 'success', 'msg' => '添加成功'];
            }
            return ['status' => 'fail', 'msg' => '添加失败'];
        } else {
            $role = [
                'excel' => 'required',
                'merchant_id2' => 'required',
                'type2' => 'required',
                'start_at2' => 'nullable|date',
                'end_at2' => 'nullable|after_or_equal:start_at',
                'action2' => 'required',
                'price2' => 'required|numeric',
                'picture_id2' => 'required|numeric',
                'address3' => 'required',
                'address4' => 'required',
                'note2' => 'required',
            ];

            $message = [
                'excel.required' => '表格不能为空！',
                'merchant_id2.required' => '所属商家不能为空！',
                'type2.required' => '优惠券类型不能为空！',
                'start_at2.date' => '开始时间类型错误！',
                'end_at2.after_or_equal' => '结束时间必须是开始时间之后！',
                'action2.required' => '优惠方式不能为空！',
                'price2.required' => '优惠价格不能为空！',
                'price2.numeric' => '优惠价格不合法！',
                'picture_id2.required' => '优惠券图片错误！',
                'picture_id2.numeric' => '优惠券图片错误！',
                'address3.required' => '生成地区不能为空！',
                'address4.required' => '生成区域不能为空！',
                'note2.required' => '描述不能为空！',
            ];
            $validator = Validator::make($data, $role, $message);
            //否则,则验证上传的excel表是否合法,录入信息
            //如果验证失败,返回错误信息
            if ($validator->fails()) {
                return ['status' => 'fail', 'msg' => $validator->messages()->first()];
            }

            //判断是否无期限
            if (!$data['type'] == 1) {
                $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));
                $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));
            } else {
                unset($data['start_at']);
                unset($data['end_at']);
                //获取时,+7天为结束时间
            }
            //调整时间
            if (!empty($data['start_at'])) {
                if ($data['start_at'] == '1970-01-01 00:00:00' || $data['end_at'] == '1970-01-01 00:00:00') {
                    return ['status' => 'fail', 'msg' => '时间格式不合法'];
                }
            }
            //数据调整
            $data2['status'] = 1;
            $data2['merchant_id'] = $data['merchant_id2'];
            $data2['type'] = $data['type2'];
            $data2['start_at'] = $data['start_at2'];
            $data2['end_at'] = $data['end_at2'];
            $data2['action'] = $data['action2'];
            $data2['price'] = $data['price2'];
            $data2['picture_id'] = $data['picture_id2'];
            $data2['address'] = $data['address4'];
            $data2['note'] = $data['note2'];
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
            $data2['content'] = json_encode($data['editorValue'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $data2['excel'] = $data['excel'];
            //验证excel
            $res = $this->Importexcel($data2['excel']);//
            $x = count($res);//要生成的数量(excel中)
            $one = ceil($x / 2);
            $to = $x - $one;
            $latitude = $this->get_latitude(1, $data['address3'], $one);
            switch ($latitude) {
                case 2:
                    return ['status' => 'fail', 'msg' => '该地址不存在或网络异常,请稍后再试'];
                    break;
                case 3:
                    return ['status' => 'fail', 'msg' => '该地区所标注为区县,请选择以区域生成类型'];
                    break;
                case 4:
                    return ['status' => 'fail', 'msg' => '该地址不存在'];
                    break;
            }
            $arr1 = $latitude;
            //二次投放  地区
            $latitude = $this->get_latitude(2, $data2['address'], $to);
            switch ($latitude) {
                case 2:
                    return ['status' => 'fail', 'msg' => '该地址不存在或网络异常,请稍后再试'];
                    break;
                case 3:
                    return ['status' => 'fail', 'msg' => '该地区所标注为区县,请选择以区域生成类型'];
                    break;
                case 4:
                    return ['status' => 'fail', 'msg' => '该地址不存在'];
                    break;
            }
            $arr2 = $latitude;
            $as = count($arr1);
            for ($i = 0; $i < count($arr2); $i++) {
                $arr1[$as] = $arr2[$i];
                $as++;
            }
            $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
            foreach ($res as $k => $v) {
                $coupon->beginTransaction;
                try {
                    //因两次投放区域可能存在不一致,需要单次对其经纬度进行区域划分
                    $data2['latitude'] = adjust($arr1[$k]); //
                    //再次查询所在地址
                    $url = "http://api.map.baidu.com/geocoder/v2/?location={$data2['latitude']['lat']},{$data2['latitude']['lng']}&output=json&pois=0&ak={$ak}";
                    $addr = file_get_contents($url);
                    $addr = json_decode($addr, true);
                    $data2['address'] = $addr['result']['addressComponent']['district'];
                    $data2['cp_id'] = $res[$k];
                    $sta = $coupon->create($data2);
                } catch (\Exception $e) {
                    if ($e->getCode() == 23000) {
                        return ['status' => 'fail', 'msg' => '添加失败,存在重复优惠券编号' . $e->getTrace()[0]['args'][1][0]];
                    }
                    return ['status' => 'fail', 'msg' => '添加失败,回滚'];
                }
            }
//           生成编号
            if ($sta->id) {
                return ['status' => 'success', 'msg' => '添加成功'];
            }
            return ['status' => 'fail', 'msg' => '添加失败'];
        }
    }

    /*
     * 表格处理
     */
    public function Importexcel($files)
    {
        $res = [];
        Excel::load($files, function ($reader) use (&$res) {
            $reader = $reader->getSheet(0);
            $res = $reader->toArray();
        });
        $arr = array();
        for ($i = 0; $i < count($res); $i++) {
            if ($i == 0) {
                continue;
            }
            $arr[$i - 1] = $res[$i][1];
        }
        return $arr;
    }

    public function ajax_list(Request $request, Coupon $coupon)
    {
        if ($request->ajax()) {
            //分页 //{$search['value']}  ,pq_coupon.start_at,pq_coupon.end_at
            $page = $request->get('start') / $request->get('length') + 1;//页码
            $search = $request->get('search');//like
            $data = $coupon
                ->with(['prcture' => function ($query) {
                    $query->select('id', 'picture_url');
                }])
                ->with(['merchant' => function ($query) {
                    $query->select('id', 'nickname');
                }])
                ->with(['member' => function ($query) {
                    $query->select('id', 'nickname');
                }])
                ->when($search, function ($coupon, $search) {
                    $coupon->whereRaw("concat(note,price,address) like '%{$search['value']}%'");
                })
                ->select('id', 'note', 'picture_id', 'latitude', 'address', 'cp_id', 'merchant_id', 'action', 'start_at', 'end_at', 'create_at', 'price', 'status', 'member_id')
                ->paginate($request->get('length'), null, null, $page)->toArray();
            $cnt = $data['total'];//总记录
            $info = [
                'draw' => $request->get('draw'),
                'recordsTotal' => $cnt,
                'recordsFiltered' => $cnt,
                'data' => $data['data'],
            ];
            return $info;
        }
    }

    public function edit(Coupon $coupon, Merchant $merchant, Member $member)
    {
        $data['couponInfo'] = $coupon;
        $data['merchantInfo'] = $merchant->select('id', 'nickname')->get();
        return view('admin.coupon.edit', $data);
    }

    public function update(Request $request, Coupon $coupon)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('cp_id', 'note', 'merchant_id', 'type', 'start_at', 'end_at', 'action', 'price', 'status');

        $role = [
            'cp_id' => 'nullable|unique:coupon,cp_id,' . $coupon->id,
            'merchant_id' => 'required',
            'type' => 'required',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|after_or_equal:start_at',
            'action' => 'required',
            'price' => 'required|numeric',
            'status' => 'nullable|numeric',
        ];

        $message = [
            'cp_id.unique' => '优惠券编号已存在！',
            'merchant_id.required' => '所属商家不能为空！',
            'type.required' => '优惠券类型不能为空！',
            'start_at.date' => '开始时间类型错误！',
            'end_at.after_or_equal' => '结束时间必须是开始时间之后！',
            'action.required' => '优惠方式不能为空！',
            'price.required' => '优惠价格不能为空！',
            'price.numeric' => '优惠价格不合法！',
            'status.required' => '使用状态不能为空！',
            'status.numeric' => '使用状态不合法！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        //判断是否无期限
        if (!$data['type'] == 1) {
            $data['start_at'] = date('Y-m-d H:i:s', strtotime($data['start_at']));
            $data['end_at'] = date('Y-m-d H:i:s', strtotime($data['end_at']));
        } else {
            unset($data['start_at']);
            unset($data['end_at']);
            //获取时,+7天为结束时间
        }
        //调整时间
        if (!empty($data['start_at'])) {
            if ($data['start_at'] == '1970-01-01 00:00:00' || $data['end_at'] == '1970-01-01 00:00:00') {
                return ['status' => 'fail', 'msg' => '时间格式不合法'];
            }
        }
        // 更新数据
        $res = $coupon->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $coupon = new Coupon();
        $coupon = $coupon->find($id);
        $res = $coupon->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /*
     * 周边优惠券请求接口
     */
    public function select_coupon(Request $request, Coupon $coupon, Picture $picture, Merchant $merchant)
    {
        $role = [
            'lat' => 'required',
            'lng' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'lat.required' => '北纬不能为空！',
            'lng.required' => '东经不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        $data = $request->only('lat', 'lng', 'member_id');
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //查询其所在地区
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        $url = "http://api.map.baidu.com/geocoder/v2/?location={$data['lat']},{$data['lng']}&output=json&pois=0&ak={$ak}";
        $addr = file_get_contents($url);
        $addr = json_decode($addr, true);
        $district = $addr['result']['addressComponent']['district'];
        $res = $coupon->where('address', $district)->select('id', 'latitude')->whereNull('member_id')->get();// where('lat'< xx)
        if (empty($res[0])) {
            res(null, '该地区[' . $district . ']周围没有优惠券,去其他地方看看吧', 'success', 201);
        }
        $distance = 500;//要找多少米内的//0
        //将找出来的所有数据,每一条都跟用户所在的经纬度进行匹配
        $as = 0;
        $merchant_row = [];
        foreach ($res as $k => $v) {
//            $juli = getDistance($data['lat'], $data['lng'], $v['latitude']['lat'], $v['latitude']['lng']);//xx  xx
            $juli = GetDistance2($data['lat'], $data['lng'], $v['latitude']['lat'], $v['latitude']['lng']);//xx  xx
            if ($juli <= $distance) {
                //如果距离满足,重新查询
                $row = $coupon->select('cp_id', 'merchant_id', 'start_at', 'end_at', 'action', 'price', 'merchant_id', 'note')->find($v['id']);
                //排除相同商家
                if (in_array($row['merchant_id'], $merchant_row)) {
                    //已经有了,跳过本次循环
                    continue;
                } else {
                    //还没有,存入
                    $merchant_row[] = $row['merchant_id'];
                }
                $arr[$as]['coupon_id'] = $v['id'];
                $arr[$as]['coupon_code'] = $row['cp_id'];
                $latitude = adjust($v['latitude']);
                $arr[$as]['lat'] = $latitude['lat'];
                $arr[$as]['lng'] = $latitude['lng'];
                //修改
                $re = $merchant->select('avatar', 'nickname', 'appraise_n', 'img_url')->where('id', $row['merchant_id'])->first();
                $arr[$as]['merchant_logo'] = $request->server('HTTP_HOST') . '/' . $re['avatar'];//商家的logo
                $arr[$as]['merchant_background_img'] = $request->server('HTTP_HOST') . '/' . $re['img_url'];//商家的logo
                $arr[$as]['nickname'] = $re['nickname'];//商家的名称
                $arr[$as]['appraise_n'] = $re['appraise_n'];//商家的评价星级
                //优惠券的有效期和组装
                $arr[$as]['start_at'] = $row['start_at'];
                $arr[$as]['end_at'] = $row['end_at'];
                //组装hot和end
                if (!empty($arr[$as]['start_at']) && !empty($arr[$as]['end_at'])) {//开始和结束时间不为空,说明有时间,不是无期限的,不需要给出临时的假时间
                    $time = time();
                    if (strtotime($arr[$as]['end_at']) > $time) {
                        //结束时间,大于当前时间
                        $arr[$as]['status'] = 'HOT';
                    } else {
                        $arr[$as]['status'] = 'END';
                    }
                } else {
                    $arr[$as]['status'] = 'HOT';
                    //时间为空,给出一个假的时间
                    $arr[$as]['start_at'] = date('Y-m-d 00:00:00', time());
                    $arr[$as]['end_at'] = date('Y-m-d H:i:s', strtotime('+7 days') - 60);//减去60秒
                }
                //优惠券的note组装 直接取出描述字段
                if ($row['action'] == 1) {
                    $arr[$as]['note'] = $row['price'] . '元' . $row['note'];
                } else {
                    $arr[$as]['note'] = $row['price'] . '折' . $row['note'];
                }
                $as++;
            }
        }
        if (empty($arr)) {
            res(null, '周围' . $distance . '米内没有优惠券', 'success', 201);
        }
        //返回之前,先存入
        /*      if( empty(Redis::get($data['member_id'].'coupon')) ){
                  //为空,是第一次请求
                  Redis::setex($data['member_id'].'coupon', 3600, json_encode($arr,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ));//存入
              }else{
                  //不为空,说明是第二次请求地址
                  $ros = Redis::get($data['member_id'].'coupon');//取出,
                  $ros = json_decode( $ros,true );
                  //先去重,将arr里的优惠券id存入一个临时数组,作为值,一旦这个id在缓存中出现,删除缓存里的键值
                  foreach ( $arr as $k=>$v ){
                      $temp[] = $v['coupon_id'];
                  }
                  //去重,如果缓存中的优惠券,在查出来的结果中,删除
                  foreach ( $ros as $k=>$v ){
                      if( in_array( $v['coupon_id'],$temp ) ){
                          unset($ros[$k]);
                      }
                  }
                  if( !empty($ros) ){
                      //在匹配下,ros中,距离用户当前位置的距离,如果大于500米,则删除
                      $member_addr = bd_encrypt( $data['lat'],$data['lng'] );
                      foreach ( $ros as $k=>$v ){
                          $juli = getDistance($member_addr['lat'], $member_addr['lng'], $v['lat'], $v['lng']);//xx  xx
                          if( $juli >= 1000 ){ //如果上一次请求的,跟用户当前位置,超过五百米,则删除
                              unset($ros[$k]);
                          }
                      }
                      if( !empty($ros) ) {
                          //不为空,缓存中还有数据,拼接
                          foreach ( $ros as $k=>$v ){
                              $arr[] = $v;//拼接进去
                          }
                      }
                      //不为空,缓存中还有数据,拼接
      //                foreach ( $ros as $k=>$v ){
      //                    $arr[] = $v;//拼接进去
      //                }
                  }
                  //将最新的存入
                  Redis::setex($data['member_id'].'coupon', 3600, json_encode($arr,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES ));//存入
              }  */
        res($arr);
    }

    /**
     * 查看优惠券详情页面
     */
    public function show_coupon(Request $request, Member $member, Coupon $coupon, Merchant $merchant)
    {
        $data = $request->only('coupon_id', 'lat', 'lng', 'member_id');
        $role = [
            'lat' => 'required',
            'coupon_id' => 'required',
            'lng' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'lat.required' => '北纬不能为空！',
            'coupon_id.required' => '优惠券id不能为空！',
            'lng.required' => '东经不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //根据优惠券的id,查找优惠券的详情
        $res1 = $coupon->select('merchant_id', 'content')->find($data['coupon_id']);
        //判断该商家是否已经收藏
        $res2 = $member->select('merchant_id')->find($data['member_id']);
        $is_collection = 2;//没有收藏
        if (!empty($res2['merchant_id'])) {
            //不为空
            $merchant_id = json_decode($res2['merchant_id'], true);
            $is_collection = in_array($res1['merchant_id'], $merchant_id) ? 1 : 2;
        }
        //返回商家的地址(详细地址),返回商家的使用说明,返回距离
        $res3 = $merchant->select('address', 'latitude')->find($res1['merchant_id']);
        $latitude = bd_encrypt($data['lat'], $data['lng']);
        $distance = getDistance($latitude['lat'], $latitude['lng'], $res3['latitude']['lat'], $res3['latitude']['lng']);
        $content = json_decode($res1['content'], true);
        $arr = [
            'merchant_id' => $res1['merchant_id'],
            'address' => $res3['address'],
            'distance' => $distance,
            'is_collection' => $is_collection,//1=已经收藏,2=没有收藏
            'content' => $content
        ];
        res($arr);
    }

    /*
     * 得到优惠券
     */
    public function get_coupon(Request $request, Coupon $coupon, Member $member)
    {
        $role = [
            'member_id' => 'required',
            'coupon_id' => 'required',
        ];
        $message = [
            'member_id.required' => '用户id不能为空！',
            'coupon_id.required' => '优惠券id不能为空！',
        ];
        $data = $request->only('member_id', 'coupon_id');
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //先判断这张券是否有被人领取
        $res = $coupon->select('member_id', 'start_at', 'end_at')->find($data['coupon_id']);
        if (empty($res['member_id'])) {
            //等下解封
            DB::transaction(function () use ($coupon, $member, $data, $res) {
                //判断时间是否未空,为空则为无期限
                if (empty($res['start_at']) || empty($res['end_at'])) {
                    //无限期
                    $start_at = date('Y-m-d H:i:s');//开始时间
                    $end_at = date('Y-m-d H:i:s', strtotime($start_at . '+1month'));
                    $res1 = $coupon->select()->where('id', $data['coupon_id'])->update(['member_id' => $data['member_id'], 'start_at' => $start_at, 'end_at' => $end_at, 'create_at' => $start_at]);
                } else {
                    $create_at = date('Y-m-d H:i:s');//优惠券获取时间
                    $res1 = $coupon->select()->where('id', $data['coupon_id'])->update(['member_id' => $data['member_id'], 'create_at' => $create_at]);
                }
                $res = $member->select('coupon_id')->where('id', $data['member_id'])->first();
                if (empty($res['coupon_id'])) {
                    //如果为空,则直接加入
                    $coupon_id = [0 => $data['coupon_id']];
                    $coupon_id = json_encode($coupon_id);
                } else {
                    //不为空,找出来,然后加入
                    $arr = $res['coupon_id'];
                    array_push($arr, $data['coupon_id']);
                    $coupon_id = json_encode($arr);
                }
                $res2 = $member->select()->where('id', $data['member_id'])->update(['coupon_id' => $coupon_id]);
                if ($res1 && $res2) {
                    DB::commit();
                    res(null, '成功');
                }
                DB::rollback();//事务回滚
                res(null, '失败', 'fail', 100);
            });
        }
        res(null, '来晚一步,优惠券已经被人抢走啦', 'fail', 105);
    }

    /*
     * 使用优惠券
     */
    public function consume(Request $request, Coupon $coupon)
    {
        $data = $request->only('coupon_id', 'member_id');
        $role = [
            'coupon_id' => 'required',
            'member_id' => 'required',
        ];
        $message = [
            'coupon_id.required' => '优惠券id不能为空！',
            'member_id.required' => '用户id不能为空！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //判断这张优惠券是否已经使用
        $res = $coupon->select('cp_id', 'status', 'start_at', 'end_at')->where('id', $data['coupon_id'])->where('member_id', $data['member_id'])->first();
        if (empty($res['cp_id'])) {
            res(null, '非法操作,该优惠券与用户不匹配', 'fail', 106);
        }
        if ($res['status'] == 1) {
            if (time() > strtotime($res['start_at'])) {
                res(null, '优惠券不在使用期限内', 'fail', 104);
            } elseif (time() > strtotime($res['end_at'])) {
                $re = $coupon->where('id', $data['coupon_id'])->update(['status' => 3]);
                res(null, '优惠券不在有效期', 'fail', 104);
            }
            //可以使用
            $res2 = $coupon->select()->where('id', $data['coupon_id'])->update(['status' => 2]);
            if ($res2) {
                $data = ['code' => $res['cp_id']];
                res($data, '成功');
            }
            res(null, '失败', 'fail', 100);
        }
        res(null, '优惠券已被使用或超过有效期', 'fail', 100);
    }

    /*
     * 查看我的优惠券(未使用)
     */
    public function available(Request $request, Member $member, Coupon $coupon, Picture $picture)
    {
        //返回一张图片,有效期,id
        $data = $request->only('member_id');
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
        $res = $member->select('coupon_id')->where('id', $data['member_id'])->first();
        if (empty($res['coupon_id'])) {
            res(null, '用户还没有获得优惠券', 'success', 201);
        }
        //先找出用户拥有的优惠券,并且状态为未使用的
        $res = $coupon->select('id', 'picture_id', 'start_at', 'end_at')->where('status', 1)->whereIn('id', $res['coupon_id'])->get();
        if (empty($res[0])) {
            res(null, '用户没有未使用的优惠券了', 'success', 201);
        }
        foreach ($res as $k => $v) {
            //如果
            if (time() >= strtotime($v['end_at'])) {//2-04 2-03
                //已过期,更新状态后再返回
                $re = $coupon->where('id', $v['id'])->update(['status' => 3]);
                //跳过本次循环
                continue;
            }
            $res2 = $picture->select('picture_url')->where('id', $v['picture_id'])->first();
            $arr[$k]['coupon_id'] = $v['id'];
            $arr[$k]['coupon_img_url'] = $request->server('HTTP_HOST') . '/' . $res2['picture_url'];
            $arr[$k]['start_at'] = $v['start_at'];
            $arr[$k]['end_at'] = $v['end_at'];
        }
        res($arr);
    }

    /*
     * 查看我的优惠券(已过期)
     */
    public function expired(Request $request, Member $member, Coupon $coupon, Picture $picture)
    {
        //返回一张图片,有效期,id
        $data = $request->only('member_id');
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
        $res = $member->select('coupon_id')->where('id', $data['member_id'])->first();
        if (empty($res['coupon_id'])) {
            res(null, '用户还没有获得优惠券', 'success', 201);
        }
        //先找出用户拥有的优惠券
        $res = $coupon->select('id', 'picture_id', 'start_at', 'end_at', 'status')->where('status', '!=', 2)->where('status', '!=', 4)->whereIn('id', $res['coupon_id'])->get();
        if (empty($res[0])) {
            res(null, '用户没有已过期的优惠券了', 'success', 201);
        }
        foreach ($res as $k => $v) {
            //如果是未使用,先判断是否过期,如果过期,更新之后,再把这条数据加入
            if ($v['status'] === 1) {
                if (time() >= strtotime($v['end_at'])) {//2-04 2-03
                    //已过期,更新状态后,这条数据也要插入到已过期之中
                    $re = $coupon->where('id', $v['id'])->update(['status' => 3]);
                    $res2 = $picture->select('picture_url')->where('id', $v['picture_id'])->first();
                    $arr[] = [
                        'coupon_id' => $v['id'],
                        'coupon_img_url' => $request->server('HTTP_HOST') . '/' . $res2['picture_url'],
                        'start_at' => $v['start_at'],
                        'end_at' => $v['end_at'],
                    ];
                }
            } elseif ($v['status'] === 3) {
                //找出所有已过期,放入数组
                $res2 = $picture->select('picture_url')->where('id', $v['picture_id'])->first();
                $arr[] = [
                    'coupon_id' => $v['id'],
                    'coupon_img_url' => $request->server('HTTP_HOST') . '/' . $res2['picture_url'],
                    'start_at' => $v['start_at'],
                    'end_at' => $v['end_at'],
                ];
            }
        }
        res($arr);
    }

    /**
     * 查看我的优惠券(已使用)
     */
    public function has_been_used(Request $request, Member $member, Coupon $coupon, Picture $picture)
    {
        $data = $request->only('member_id');
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
        $res = $member->select('coupon_id')->where('id', $data['member_id'])->first();
        if (empty($res['coupon_id'])) {
            res(null, '用户还没有获得优惠券', 'success', 201);
        }
        //先找出用户拥有的优惠券
        $res = $coupon->select('id', 'picture_id', 'start_at', 'end_at', 'status')->where('status', 2)->whereIn('id', $res['coupon_id'])->get();
        if (empty($res[0])) {
            res(null, '用户没有已使用的优惠券了', 'success', 201);
        }
        foreach ($res as $k => $v) {
            $res2 = $picture->select('picture_url')->where('id', $v['picture_id'])->first();
            $arr[$k]['coupon_id'] = $v['id'];
            $arr[$k]['coupon_img_url'] = $request->server('HTTP_HOST') . '/' . $res2['picture_url'];
            $arr[$k]['start_at'] = $v['start_at'];
            $arr[$k]['end_at'] = $v['end_at'];
        }
        res($arr);
    }
}
