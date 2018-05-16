<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Health extends Model
{
    // 附属表 健康达人表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'health';
    protected $primaryKey = 'id';
    protected $fillable = ['id','member_id','target','steps','total','cost','status','activity_id','disabled_at','address','award','delivery'];

    //健康达人表和用户表的关系 多对多
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }

}
