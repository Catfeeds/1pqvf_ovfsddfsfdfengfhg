<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntMall extends Model
{
    //积分商城
    use SoftDeletes;
    public $timestamps = false;
    protected $dates = ['deleted_at'];
    protected $table = 'intMall';
    protected $primaryKey = 'id';
    protected $fillable = ['id','trade_name','img_url','trade_num','integral_price','rmb_price'];

}