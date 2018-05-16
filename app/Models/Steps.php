<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;


class Steps extends Model
{
    //步数表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'steps';
    protected $primaryKey = 'id';
    protected $fillable = ['id','mem_id','steps','sports_t','created_at','disabled_at'];

    public function getMemberName()
    {
        return $this->belongsTo(\App\Models\Member::class, 'mem_id');
    }
}
