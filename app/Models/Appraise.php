<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appraise extends Model
{
    //评价表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'appraise';
    protected $primaryKey = 'id';
    protected $fillable = ['id','img_url','content', 'mer_id', 'mem_id', 'appraise', 'created_at', 'disabled_at'];

    //评论表和用户的关系 多对一
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'mem_id','id');
    }
    //评价跟商家的关系 多对一
    function merchant(){
        return $this->belongsTo(\App\Models\Merchant::class,'mer_id','id');
    }
}