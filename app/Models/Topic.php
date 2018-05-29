<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    //话题
    protected $dates = ['deleted_at'];
    protected $table = 'topic';
    protected $primaryKey = 'id';
    protected $fillable = ['id','lev_state','member_id','subject_id','nice_num','content','img_url','subjec_catename','created_at','addres'];

    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }

    //查询所属的分类
    function subject(){
        return $this->belongsTo(\App\Models\Subject::class,'subject_id','id');
    }
}
