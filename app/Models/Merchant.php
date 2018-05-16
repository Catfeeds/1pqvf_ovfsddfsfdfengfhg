<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    //商家
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'merchant';
    protected $primaryKey = 'id';
    protected $fillable = ['id','appraise_n','nickname','ification_id','address','latitude','img_url','avatar','labelling','disabled_at','store_image'];

    protected $casts = [
        'latitude' => 'array',
    ];

}
