<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    //话题
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'topic';
    protected $primaryKey = 'id';
    protected $fillable = ['id','admin_id','subject_id','nice_num','content','img_url','member_id','subjec_catename','created_at','addres'];

    //查询发布的管理员
    function admin(){
        return $this->belongsTo(\App\Models\Admin::class,'admin_id','id');
    }

    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }

    //查询所属的分类
    function subject(){
        return $this->belongsTo(\App\Models\Subject::class,'subject_id','id');
    }
}
