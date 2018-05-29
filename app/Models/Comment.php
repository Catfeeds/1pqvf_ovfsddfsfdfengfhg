<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //评论表
    protected $table = 'comment';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'member_id','dy_id', 'to_id', 'parent_id','p_mid','content'];

    //评论表和用户的关系 多对一
    function member()
    {
        return $this->belongsTo(\App\Models\Member::class, 'member_id', 'id');
    }
    //评论表和动态的关系 多对一
    function dynamic()
    {
        return $this->belongsTo(\App\Models\Dynamic::class, 'dy_id', 'id');
    }

    //评论表和话题的关系 多对一
    function topic()
    {
        return $this->belongsTo(\App\Models\Topic::class, 'to_id', 'id');
    }

    public function childComments()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

    public function allChildrenComments()
    {
        return $this->childComments()->with('allChildrenComments');
    }

    /****************************** 递归***************************************************************************/

    /**
     * 传递父级分类ID返回所有子分类ID（不含父id）
     * @param [type] $cate 要递归的数组
     * @param [type] $pid 父级分类ID
     * @return [type]  [description]
     */
    static public function getChildrenId($cate, $pid)
    {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['parent_id'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, self::getChildrenId($cate, $v['id']));
            }
        }
        return $arr;
    }

    /**
     * 传递所有父级id数组集合 返回其本身及其后代id的一维数组（含父id）
     * @param [array] $cate 要递归的数组
     * @param [array] $p_ids 父级分类ID的二维数组例如：array:3 [0 => array:1 ["id" => 4]1 => array:1 ["id" => 6]2 => array:1 ["id" => 8]]
     * @return [type]  [description]
     */
    static public function getChildrenIds($cate, $p_ids)
    {
        $arr = array();
        foreach ($p_ids as $val){
            $co_id = array($val['id']);
            $arr =array_merge($co_id, array_merge($arr,self::getChildrenId($cate,$val['id'])));
        }
        return $arr;
    }


}
?>



