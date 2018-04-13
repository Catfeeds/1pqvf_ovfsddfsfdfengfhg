<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TakeFlag extends Model
{
    //附属表 夺旗先锋
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'takeFlag';
    protected $primaryKey = 'id';
    protected $fillable = ['id','member_id','flag_num','activity_id','disabled_at','address','status','award','delivery','cost'];

    //夺旗先锋 和用户表的关系
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }

}