<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    //商家
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'merchant';
    protected $primaryKey = 'id';
    protected $fillable = ['id','appraise_n','nickname','ification_id','address','latitude','img_url','avatar','labelling','disabled_at','store_image'];

    protected $casts = [
        'latitude' => 'array',
    ];

    //商家和优惠券分类  一对多
    public function coupon_category(){
        return $this->hasMany(\App\Models\CouponCategory::class,'merchant_id', 'id');
    }

    //商家和每张优惠券   远层的一对多 用于查询商家拥有哪些优惠券
    public function coupon_merchant()
    {
        return $this->hasManyThrough(\App\Models\CouponCategory::class,\App\Models\Merchant::class, 'cp_cate_id', 'merchant_id', 'id');
    }


}
