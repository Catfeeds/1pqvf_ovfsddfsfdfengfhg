<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;


class Rankings extends Model
{
    //排行表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'rankings';
    protected $primaryKey = 'id';
    protected $fillable = ['id','create_at','ranking','type'];

    function activity(){
        return $this->belongsTo(\App\Models\Activity::class,'type','id');
    }

}
