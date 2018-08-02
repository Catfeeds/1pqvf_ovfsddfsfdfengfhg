<?php

namespace App\Http\Controllers\Admin;

use App\Models\Member;
use App\Models\TheDelivery;
use App\Models\IntMall;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\DB;

class TheDeliveryController extends Controller
{
    public function index()
    {
        return view('admin.thedelivery.index');
    }

    public function ajax_list(Request $request, TheDelivery $thedelivery)
    {
        if ($request->ajax()) {
            $data = $thedelivery->with('member')->with('intmall')->select('id', 'member_id', 'intmall_id', 'delivery_time', 'logistics', 'order_sn', 'created_at')->get();//address
            dump($data);die();
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

    public function edit($id)
    {
        //接收到id,然后查询用户的id是多少
        $theDelivery = new TheDelivery();
        $res = $theDelivery->select('member_id', 'delivery_time', 'logistics', 'order_sn')->find($id);
        if (empty($res['delivery_time'])) {
            //为空,还没发货
            $data['flag'] = 1;
        } else {
            $data['flag'] = 2;//已发货
        }
        $data['id'] = $id;//已发货
        $data['logistics'] = $res['logistics'];
        $data['order_sn'] = $res['order_sn'];
        //查询出地址
        $member = new Member();
        $res = $member->select('address')->find($res['member_id']);
        $data['info'] = $res['address'];
        return view('admin.thedelivery.showadd', $data);
    }

    public function update(Request $request, TheDelivery $theDelivery)
    {

        $data = $request->only('logistics', 'order_sn');
        $role = [
            'logistics' => 'required',
            'order_sn' => 'required',
        ];
        $message = [
            'logistics.required' => '物流不能为空！',
            'order_sn.required' => '快递单号不能为空！',
        ];
        //过滤信息
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            res(null, $validator->messages()->first(), 'fail', 101);
        }
        //已领奖
        $data['delivery_time'] = date('Y-m-d H:i:s', time());
        if (empty($theDelivery['delivery_time'])) {
            $res = $theDelivery->update($data);
            if ($res) {
                return ['status' => 'success', 'msg' => '成功'];
            }
        }
        return ['status' => 'fail', 'msg' => '失败'];
    }

    /*
     * 取消发货
     */
    public function undo($id)
    {
        //接收取消发货,取消发货时间和订单号及物流等
        $thedelivery = new TheDelivery();
        $res = $thedelivery->select('delivery_time')->find($id);
        //已领奖
        if (!empty($res['delivery_time'])) {
            $res = $thedelivery->where('id', $id)->update(['delivery_time' => null, 'order_sn' => null, 'logistics' => null]);
            if ($res) {
                return ['status' => 'success', 'msg' => '成功'];
            }
        }
        return ['status' => 'fail', 'msg' => '失败'];
    }

}
