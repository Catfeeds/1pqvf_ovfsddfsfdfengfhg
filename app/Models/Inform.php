<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Inform extends Model
{
    //通知表

    protected $table = 'inform';
    protected $primaryKey = 'id';
    protected $fillable = ['id','to_member','inf_path','push_time','title','content','read'];

    /**
     * 新增'您有一条新评论'通知
     * @param $member_id 即to_member被通知人的id
     * @return bool
     */
    public function msm_inf($member_id)
    {
        is_numeric( $member_id ) or die('发送提醒失败：提供的参数不是数字');
        //查看系统初始化是否存在inf_path=2的数据，
        $has_path_2 = DB::table('inform')->where('inf_path',2)->first();
        //不存在就新增该条数据
        if (empty($has_path_2)){
            DB::table('inform')->insert([
                'inf_path' => '2',
                'title' => '您有一条新回复',
            ]);
        }
        //存在，就获取to_member
        $in_path = DB::table('inform')->select('to_member')->where('inf_path',2)->first();
        $in_path = obj_arr($in_path);
        if(empty($in_path['to_member'])){//如果为空直接插入
            $arr[] = $member_id;
        }else{//不为空弹入
            $arr = json_decode($in_path['to_member'], true);
            if (in_array($member_id, $arr)) {
                return true;
            }
            array_push($arr, $member_id);
        }
        $arr = json_encode($arr);
        $res = DB::table('inform')->where('inf_path',2)->update(['to_member' => $arr]);
        if ($res) {
            return true;
        }
        return false;
    }



}
