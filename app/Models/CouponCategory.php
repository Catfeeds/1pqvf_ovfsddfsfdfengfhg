<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;


class CouponCategory extends Model
{
    use SoftDeletes;
    //优惠券分类表
    protected $dates = ['deleted_at'];
    protected $table = 'coupon_category';
    protected $primaryKey = 'id';
    protected $fillable = ['id','merchant_id','merchant_name','coupon_name','coupon_type','coupon_money','spend_money','send_start_at','send_end_at','category_status','send_num','picture_url','deduction_url','content'];

    //优惠券和商家的关系 多对一
    function merchant(){
        return $this->belongsTo(\App\Models\Merchant::class,'merchant_id','id');
    }

    //优惠券分类和和优惠券 一对多
    function coupons(){
        return $this->belongsTo(\App\Models\Coupon::class,'cp_cate_id','id');
    }
}
