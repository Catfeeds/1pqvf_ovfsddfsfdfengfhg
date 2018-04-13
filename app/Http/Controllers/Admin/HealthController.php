<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\Member;
use App\Models\Merchant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Models\Health;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{

    public function index()
    {
        return view('admin.health.index');
    }

    public function ajax_list(Request $request, Health $health)
    {
        if ($request->ajax()) {
            $data = $health->with('member')->select('id', 'member_id', 'total', 'status', 'award', 'delivery')->get();//address
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
        return view('admin.health.showadd', $data);
    }

    /*
     * 发货
     */
    public function send($meber_id)
    {
        //根据传递过来的用户id,去健康达人中寻找,
        $health = new Health();
        $res = $health->where('member_id', $meber_id)->select('status', 'delivery')->first();
        //已领奖
        if ($res['status'] == 3) {
            //判断是否已经发货
            if (empty($res['delivery'])) {
                $res = $health->where('member_id', $meber_id)->select()->update(['delivery' => date('Y-m-d H:i:s')]);
                if ($res) {
                    return ['status' => 'success', 'msg' => '成功'];
                }
            }
        }
        return ['status' => 'fail', 'msg' => '用户还没点击领奖,或用户的目标未达成'];
    }

    /**
     * 取消发货
     */
    public function undo($meber_id)
    {
        //根据传递过来的用户id,去健康达人中寻找,
        $health = new Health();
        $res = $health->where('member_id', $meber_id)->select('delivery')->first();
        //判断是否已经发货
        if (!empty($res['delivery'])) {
            $res = $health->where('member_id', $meber_id)->select()->update(['delivery' => null]);
            if ($res) {
                return ['status' => 'success', 'msg' => '成功'];
            }
        }
        return ['status' => 'fail', 'msg' => '还未发货'];
    }
}
