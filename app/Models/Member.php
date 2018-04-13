<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    //用户
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'member';
    protected $primaryKey = 'id';
    protected $fillable = ['id','is_admin','integral_swap','steps','phone','api_token','password','nickname','avatar','city','sex','age','height','weight','address','integral','qr_code','medal','friends_id','fans_id','attention_id','merchant_id','collection_coupon_id','coupon_id','tesco','disabled_at'];

    protected $casts = [
        'attention_id' => 'array',
        'collection_coupon_id' => 'array',
        'coupon_id' => 'array',
        'steps' => 'array',
        'medal'=> 'array',
    ];

}