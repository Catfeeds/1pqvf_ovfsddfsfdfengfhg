<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CnLatLngBag extends Model
{
    protected $table = 'cn_lat_lng_bag';
    protected $primaryKey = 'id';
    protected $fillable = ['id','uuid','abcode','lng','lat','formatted_address', 'province','city','district','town','created_at','updated_at'];

    //多对一
//    function CnProCity(){ return $this->belongsTo(\App\Models\CnProCity::class,'adcode','adcode');
////    }
//
    /**
     * 在深圳范围内生成 $n * 350 条平均分布的坐标
     *$adr_arr
     * 生成 $n * 350 条平均分布的坐标
     * 生成预编码库
     */
    function creates_sz_loc($n){
        $arr = make_sz_long_lat($n);
        $num_uuid = count($arr);
        $arr_uuid = create_unique_max_8bit_int($num_uuid);//唯一编码数组
        $ak = 'fSTUrykGGBg5guFLt2RSaQpaPIZvFzPd';
        DB::transaction(function () use ($arr,$arr_uuid,$ak) {
            foreach ($arr as $k => $v){
                $arr_formatted = get_address_component($ak,$v['lng'],$v['lat']);
                $arr_tf[] = $this->create([
                    'uuid' => $arr_uuid[$k],
                    'lng' => $v['lng'],
                    'lat' => $v['lat'],
                    'formatted_address' => $arr_formatted['result']['formatted_address'],
                    'district' => $arr_formatted['result']['addressComponent']['district'],
                    'adcode' => $arr_formatted['result']['addressComponent']['adcode'],
                    'province' => $arr_formatted['result']['addressComponent']['province'],
                    'city' => $arr_formatted['result']['addressComponent']['city'],
//                    'cr_adr_code' =>
                ]);
            }

            DB::commit();
            return date("H:i:s");
        });

    }


}
