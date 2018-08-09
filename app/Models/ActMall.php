<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActMall extends Model
{
    //活动商城(赛事商城)(奖品)
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'actMall';
    protected $primaryKey = 'id';
    protected $fillable = ['id','goods_name','note','price','img_url','act_num'];

}