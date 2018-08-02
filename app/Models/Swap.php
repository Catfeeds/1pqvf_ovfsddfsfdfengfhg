<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Swap extends Model
{
    //互换
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'swap';
    protected $primaryKey = 'id';
    protected $fillable = ['id','coupon_id','member_id','status','integral','exc_mem_id'];

    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }

    function coupon(){
        return $this->belongsTo(\App\Models\Coupon::class,'coupon_id','id');
    }
}