<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'id';
    protected $fillable = ['id','cp_cate_id','start_at','end_at','uuid','status','rewarded_lat','rewarded_lng','content','create_at','member_id','lng','lat','note','province','city','district','cp_number','adcode'];

    //优惠券和优惠券图片的关系 多对一
    function coupon_category(){
        // belongsTo 多对一(从属)
        return $this->belongsTo(\App\Models\CouponCategory::class,'cp_cate_id','id');
    }
    //优惠券和用户的关系 多对一
    function member(){
        // belongsTo 多对一(从属)
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }
    //商家和每张优惠券   远层的一对多 用于查询商家拥有哪些优惠券
    public function coupon_merchant()
    {
        return $this->hasManyThrough(\App\Models\CouponCategory::class,\App\Models\Merchant::class, 'cp_cate_id', 'merchant_id', 'id');
    }
}
