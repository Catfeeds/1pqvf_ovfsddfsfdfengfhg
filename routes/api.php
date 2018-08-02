<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
/*
|--------------------------------------------------------------------------
| API服务路由
|--------------------------------------------------------------------------
配置文件定义了prefix' => 'api'
*/
$api = app('Dingo\Api\Routing\Router');
/*****************************验证版本**************************************/
$api->version(['v1','1102'], function ($api) {

    /*****************************所有api服务***********************************/
    $api->group(['namespace' => 'App\Http\Controllers\Api'], function ($api) {//

        $api->post('register', 'UsersController@register');    //用户注册

        /*****************************防翻墙***********************************/
        $api->group(['middleware' => ['auth:api']], function($api) {
//            $api->get('comment/chk_comments', 'CommentController@chk_comments');
            /**
             * 活动一:深大
             */
            $api->get('act_one/slc_flg','ActOneController@slc_flg');//查看所有旗子
            $api->get('act_one/has_flg','ActOneController@has_flg');//查看拥有徽章或旗子
            $api->get('act_one/cl_flg','ActOneController@cl_flg');//点击旗子查看详情
            $api->get('act_one/lt_flg','ActOneController@lt_flg');//点亮旗子
            $api->get('act_one/is_enrol','ActOneController@is_enrol');//查询是否已报名

            /**
             * 活动二：端午，抢粽子
             */
            $api->get('act_zong/slc_zong','ActOneZongController@slc_zong');//查看所有粽子
            $api->get('act_zong/cl_zong','ActOneZongController@cl_zong');//弹窗查看粽子详情
            $api->get('act_zong/lt_zong','ActOneZongController@lt_zong');//获取粽子
            $api->get('act_zong/has_zong','ActOneZongController@has_zong');//查看拥有的粽子
            $api->get('act_zong/reward_zon','ActOneZongController@reward_zon');//点击领奖
            $api->get('act_zong/has_reward','ActOneZongController@has_reward');//查询是否已领奖

//    Route::get('act_zong/add_some_zon','ActOneZongController@add_some_zon')->middleware('auth:api');//生成普通粽子4000个并入库
//    Route::get('act_zong/add_zon','ActOneZongController@add_zon_ave')->middleware('auth:api');//生成一等奖粽子100个并入库
//    Route::get('shf_zon','ActOneZongController@shf_zon')->middleware('auth:api');//打乱所有粽子

        });










    });
});