<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActOneFlag extends Model
{
    use SoftDeletes;
    //旗子坐标
    protected $dates = ['deleted_at'];
    protected $table = 'act_one_flag';
    protected $primaryKey = 'id';
    protected $fillable = ['id','latitude','lng','lat','status','flag_name','flag_lv','pic_id','content','note','act_id','member_id'];

    /**
     * 获取相应活动下的所有旗子的信息（数组化）
     * $act_id：活动id
     */
    function grt_all_flag($act_id,$status){
        $latitude = $this->select('id','latitude','flag_name','content','flag_lv','pic_id')
            ->where('act_id',$act_id)
            ->where('status',$status)
            ->get();
        if (empty($latitude)) {
            res(null, '网络繁忙,请重新请求', 'fail', 201);
        }
        //坐标转数组
        $arr_ll = [];
        $lng_lat = [];
        foreach ($latitude as $k => $v){
            $lng_lat[$k]['id'] = $v['id'];
            $arr_ll[$k] = json_decode($v['latitude'],true);
            $lng_lat[$k]['lng'] = $arr_ll[$k]['lng'];
            $lng_lat[$k]['lat'] = $arr_ll[$k]['lat'];
            $lng_lat[$k]['flag_name'] = $v['flag_name'];
            $lng_lat[$k]['flag_lv'] = $v['flag_lv'];
            $lng_lat[$k]['content'] = json_decode($v['content'],true);
            $lng_lat[$k]['pic_id'] = $v['pic_id'];
        }
        return $lng_lat;
    }
    /**
     * 获取某个旗子的所有信息的一维数组
     */
    function get_a_flag($flg_id){
        $a_flag = $this->select('id','latitude','flag_name','content','pic_id','flag_lv')
            ->where('id',$flg_id)
            ->first();
        if (empty($a_flag)) {
            res(null, '网络繁忙,请重新请求', 'fail', 201);
        }
        //转数组
        $flag_info['id'] = $a_flag['id'];
        $flag_info['lng'] = json_decode($a_flag['latitude'],true)['lng'];
        $flag_info['lat'] = json_decode($a_flag['latitude'],true)['lat'];
        $flag_info['flag_name'] = $a_flag['flag_name'];
        $flag_info['content'] = json_decode($a_flag['content'],true);
        $flag_info['pic_id'] = $a_flag['pic_id'];
        $flag_info['flag_lv'] = $a_flag['flag_lv'];
        return $flag_info;
    }

}


