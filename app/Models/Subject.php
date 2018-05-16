<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    //话题分类
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'subject';
    protected $primaryKey = 'id';
    protected $fillable = ['id','cate_name','img_url','read_num','cate_note'];

}