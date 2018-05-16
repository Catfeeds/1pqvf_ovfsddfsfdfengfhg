<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TheDelivery extends Model
{
    // 积分商品发货
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'theDelivery';
    protected $primaryKey = 'id';
    protected $fillable = ['id','member_id','intmall_id','delivery_time','logistics','Order','created_at'];

    //积分商品发货 和用户表的关系
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }
    //积分商品发货 和积分商品表的关系
    function intmall(){
        return $this->belongsTo(\App\Models\IntMall::class,'intmall_id','id');
    }
}
