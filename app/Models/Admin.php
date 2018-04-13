<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
//支持auth授权
use Illuminate\Foundation\Auth\User as Authenticatable;
class Admin extends Authenticatable
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $fillable = ['id','avatar','username','password','note','email','disabled_at','friends_id','fans_id'];

}