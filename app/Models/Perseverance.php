<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Foundation\Auth\User as Authenticatable;

class Perseverance extends Model
{
    //附属表 毅力使者
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'perseverance';
    protected $primaryKey = 'id';
    protected $fillable = ['id','member_id','status','punch_d','total_steps','activity_id','address','award','delivery','status2'];

    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }
    //附属表和活动表的关系  一对一

}