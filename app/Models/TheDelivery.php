<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TheDelivery extends Model
{
    // 积分商品发货
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'thedelivery';
    protected $primaryKey = 'id';
    protected $fillable = ['id','from_user','order_sn','itm_id','atm_id','to_member_id','to_phone','to_address','to_zip','send_at','logistics','logistics_sn','td_status','postscript','created_at'];

    //积分商品发货 和积分商品表的关系
    function intmall(){
        return $this->belongsTo(\App\Models\IntMall::class,'itm_id','id');
    }
}
