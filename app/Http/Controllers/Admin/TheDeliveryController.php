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
            $data = $thedelivery
                ->leftJoin('member','member.id','=','thedelivery.to_member_id')
                ->leftJoin('intmall','intmall.id','=','thedelivery.itm_id')
                ->leftJoin('actmall','actmall.id','=','thedelivery.atm_id')
                ->select('thedelivery.id', 'thedelivery.to_member_id', 'thedelivery.itm_id','thedelivery.atm_id',
                    'thedelivery.send_at', 'thedelivery.logistics', 'thedelivery.order_sn', 'thedelivery.created_at',
                    'thedelivery.postscript','member.nickname AS to_member','member.phone AS phone','member.address AS member_info',
                    'intmall.trade_name','actmall.goods_name')->get();
            foreach ($data as $k => $v){
                $data[$k]['member_info'] =  json_decode($v['member_info'],true)['address'];
            }
//            dump(obj_arr($data));die();
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
//        dump($id);die();
        //接收到id,然后查询用户的id是多少
        $theDelivery = new TheDelivery();
        $data = $theDelivery->leftJoin('member','member.id','=','thedelivery.to_member_id')
            ->leftJoin('intmall','intmall.id','=','thedelivery.itm_id')
            ->leftJoin('actmall','actmall.id','=','thedelivery.atm_id')
            ->select( 'thedelivery.id','thedelivery.to_member_id', 'thedelivery.itm_id','thedelivery.atm_id', 'thedelivery.send_at', 'thedelivery.logistics', 'thedelivery.order_sn', 'thedelivery.created_at','thedelivery.postscript','member.nickname AS to_member','member.phone AS phone','member.address AS to_info','intmall.trade_name','actmall.goods_name')->find($id);
        $data['flag'] = empty($data['send_at']) ? 1 : 2;



//         dump(obj_arr($data));die();
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
