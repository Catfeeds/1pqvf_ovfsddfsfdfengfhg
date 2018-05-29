<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dynamic extends Model
{
    //动态
    protected $dates = ['deleted_at'];
    protected $table = 'dynamic';
    protected $primaryKey = 'id';
    //创建时间即为发布时间
    protected $fillable = ['id','created_at','disabled_at','member_id','nice_num','img_url','content','addres'];

    //查询所有用户
    function member(){
        // belongsTo 多对一(从属)
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }


}
