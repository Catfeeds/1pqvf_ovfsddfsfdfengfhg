<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    //评论表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'comment';
    protected $primaryKey = 'id';
    protected $fillable = ['id','dy_id','to_id','member_id','content','created_at'];
    //评论表和用户的关系 多对一
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }
    //评论表和动态的关系 多对一
    function dynamic(){
        return $this->belongsTo(\App\Models\Dynamic::class,'dy_id','id');
    }
    //评论表和话题的关系 多对一
    function topic(){
        return $this->belongsTo(\App\Models\Topic::class,'to_id','id');
    }
}