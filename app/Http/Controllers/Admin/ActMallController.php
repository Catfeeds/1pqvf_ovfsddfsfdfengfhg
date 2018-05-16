<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity;
use App\Models\ActMall;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class ActMallController extends Controller
{

    public function index()
    {
        return view('admin.actmall.index');
    }

    public function create( )
    {
        return view('admin.actmall.create');
    }

    public function store(Request $request, ActMall $actmall)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('note', 'price', 'img_url', 'act_num');
        $role = [
            'note' => 'required',
            'price' => 'required|integer',
            'img_url' => 'required|image',
            'act_num' => 'required|integer'
        ];
        $message = [
            'note.required' => '商品名称/简介不能为空！',
            'price.required' => '商品价格不能为空！',
            'price.integer' => '商品价格必须大于0！',
            'img_url.required' => '商品图片不能为空',
            'img_url.image' => '商品图片格式合法,必须是 jpeg、bmp、jpg、gif、gpeg、png格式',
            'act_num.required' => '商品数量不能为空',
            'act_num.integer' => '商品数量必须大于0',
        ];
        $validator = Validator::make($data, $role, $message);

        //如果验证失败,返回错误信息
        if ($validator->fails()) {
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        if (!empty($data['img_url'])) {
            $res = uploadpic('img_url', 'uploads/img_url');//
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
            $data['img_url'] = $res; //把得到的地址给picname存到数据库
        }
        //数据调整
        if( !empty($data['act_num']) && $data['act_num'] < 0 || $data['act_num'] == 0 ){
            $data['act_num'] = 1;
        }
        if( !empty($data['price']) && $data['price'] < 0 || $data['price'] == 0 ){
            $data['price'] = 1;
        }
        //入库
        $res = $actmall->create($data);
        if ($res->id) {
            return ['status' => 'success', 'msg' => '添加成功'];
        }
        return ['status' => 'fail', 'msg' => '添加失败'];
    }

    public function ajax_list(Request $request, ActMall $actmall)
    {
        if ($request->ajax()) {
            $data = $actmall->select('id', 'note', 'price', 'img_url','act_num')->get();
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

    public function edit(ActMall $actmall)
    {
        $data['actmallInfo'] = $actmall;
        return view('admin.actmall.edit', $data);
    }

    public function update(Request $request, ActMall $actmall)
    {
        if (!$request->ajax()) {
            return ['status' => 'fail', 'error' => '非法的请求类型'];
        }
        $data = $request->only('note', 'price', 'img_url', 'act_num');
        $role = [
            'price' => 'nullable|integer',
            'img_url' => 'nullable|image',
            'act_num' => 'nullable|integer'
        ];
        $message = [
            'price.integer' => '商品价格必须大于0！',
            'img_url.image' => '商品图片格式合法,必须是 jpeg、bmp、jpg、gif、gpeg、png格式！',
            'act_num.integer' => '商品数量必须大于0！',
        ];
        $validator = Validator::make($data, $role, $message);
        if ($validator->fails()) {
            // 验证失败！
            return ['status' => 'fail', 'msg' => $validator->messages()->first()];
        }
        if( !empty($data['img_url']) ){
            $res = uploadpic('img_url','uploads/img_url');
            switch ($res){
                case 1: return  ['status' => 'fail', 'msg' => '图片上传失败'];
                case 2: return  ['status' => 'fail', 'msg' => '图片不合法'];
                case 3: return  ['status' => 'fail', 'msg' => '图片后缀不对'];
                case 4: return  ['status' => 'fail', 'msg' => '图片储存失败'];
            }
            $data['img_url'] = $res; //把得到的地址给picname存到数据库
            //删除原图
            $ress = $actmall->img_url;
            if( !empty($ress) ){
                unlink($ress);
            }
        }
        //数据调整
        if( !empty( $data['act_num'] ) && $data['act_num'] < 0 || $data['act_num'] == 0 ){
            $data['act_num'] = 1;
        }
        if( !empty($data['price']) && $data['price'] < 0 || $data['price'] == 0 ){
            $data['price'] = 1;
        }
        foreach ( $data as $k=>$v ){
            if( empty( $v )  ){
                unset( $data[$k] );
            }
        }
        // 更新数据
        $res = $actmall->update($data);
        if ($res) {
            return ['status' => 'success', 'msg' => '修改成功'];
        } else {
            return ['status' => 'fail', 'code' => 3, 'error' => '修改失败！'];
        }
    }

    public function destroy($id)
    {
        $actmall = new ActMall();
        $actmall = $actmall->find($id);
        $res = $actmall->delete();
        if ($res) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'fail', 'error' => '删除失败！'];
        }
    }

    /*
     * 我的->积分商城->赛事奖品
     */
    public function Activity_prizes( Request $request, ActMall $actMall ){
        //直接返回所有赛事奖品价格,简介,图片
        $data = $actMall->select( 'id','price','note','img_url'  )->get()->toArray();
        foreach ( $data  as $k=>$v ){
            $arr[$k]['activity_prizes_id'] =   $v['id'];
            $arr[$k]['price'] =   $v['price'];
            $arr[$k]['note'] =   $v['note'];
            $arr[$k]['img_url'] =   $request->server('HTTP_HOST').'/'.$v['img_url'];
        }
        res( $arr );
    }
}
