<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActOne extends Model
{
    //活动一用户数据表
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'act_one';
    protected $primaryKey = 'id';
    protected $fillable = ['id','member_id','has_flag','flag_num','activity_id','address','status','award','delivery','cost'];

    //和用户表的关系
    function member(){
        return $this->belongsTo(\App\Models\Member::class,'member_id','id');
    }

    /**
     * 查询用户已拥有的旗子
     * 返回：拥有旗子的一维数组或null
     */
    function check_has_flg($member_id){
        $has_fls  = $this->select('has_flag')
            ->where('member_id',$member_id)
            ->first();
        if(empty($has_fls['has_flag'])){
            return null;
        }
        $has_fls_ids = json_decode($has_fls['has_flag'],true);
        return $has_fls_ids;

    }


}
