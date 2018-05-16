<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    //优惠券
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'coupon';
    protected $primaryKey = 'id';
    protected $fillable = ['id','note','get_addr','cp_id','merchant_id','action','content','start_at','end_at','create_at','price','status','member_id','picture_id','latitude','address'];

    protected $casts = [
        'latitude' => 'array',
    ];

    //优惠券和优惠券图片的关系 多对一
    function prcture(){
        // belongsTo 多对一(从属)
        return $this->belongsTo(\App\Models\Picture::class,'picture_id','id');
    }
    //优惠券和商家的关系 多对一
    function merchant(){
        // belongsTo 多对一(从属)
        return $this->belongsTo(\App\Models\Merchant::class,'merchant_id','id');
    }
    //优惠券和用户的关系 多对一
    function member(){
        // belongsTo 多对一(从属)
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }
}
