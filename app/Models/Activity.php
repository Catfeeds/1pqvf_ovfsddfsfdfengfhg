<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    //活动
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'activity';
    protected $primaryKey = 'id';
    protected $fillable = ['id','note','title','img_url','content','actMall_id','actMall_num','start_at','end_at','man_num','top_img_url','status'];

    //活动表和奖品表的关系 多对一
    public function actmall(){
        return $this->belongsTo(\App\Models\ActMall::class,'actMall_id','id');
    }

}