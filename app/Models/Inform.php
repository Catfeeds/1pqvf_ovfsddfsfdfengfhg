<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
class Inform extends Model
{
    //通知表

    protected $table = 'inform';
    protected $primaryKey = 'id';
    protected $fillable = ['id','content','title','inform_url','push_time'];

}
