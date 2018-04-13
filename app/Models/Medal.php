<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medal extends Model
{
    //奖章
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'medal';
    protected $primaryKey = 'id';
    protected $fillable = ['id','medal_url','note','type','action','content','rewards','pid'];
}
