<?php
use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Comment;
class CommentTableSeeder extends Seeder
{
    /**d
     * Run the database seeds.
     *  评论表
     * @return void
     */
    public function run(Comment $comment)
    {
        $comment -> truncate();//重置
        //实例化factory类
        $comment->insert([
            'dy_id'=>1,//只能属于动态或者话题其中之一,
            'to_id'=>null,
            'member_id'=>1,
            'content'=>'nice!',
            'created_at'=>( date('Y-m-d H:i:s',time()) ),
        ]);
    }
}

