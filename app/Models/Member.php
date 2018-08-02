<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Member extends Authenticatable implements JWTSubject
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



    /**
     * 获取将存储在JWT的主题声明中的标识符。
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * 返回一个键值数组，其中包含要添加到JWT的任何自定义声明。
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}