<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
class Ification extends Model
{

    public $timestamps = false;
    public $softDeletes = false;

    protected $table = 'ification';
    protected $primaryKey = 'id';
    protected $fillable = ['id','cate_name'];

}
