<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    //反馈
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'feedback';
    protected $primaryKey = 'id';
    //创建时间即为发布时间
    protected $fillable = ['id','inform_url','cation','member_id','content','contact','handling','created_at'];
    //反馈表和用户的关系 多对一
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }
}
