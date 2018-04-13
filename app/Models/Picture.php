<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;


class Picture extends Model
{
    //优惠券图片表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'picture';
    protected $primaryKey = 'id';
    protected $fillable = ['id','merchant_id','price','picture_url','deduction_url','action'];

    //优惠券和商家的关系 多对一
    function merchant(){
        return $this->belongsTo(\App\Models\Merchant::class,'merchant_id','id');
    }

}
