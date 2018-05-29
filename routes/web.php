<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::any('/','Home\IndexController@index');//设置默认访问控制器方法名

/**
 * 大后台
 */
Route::group(['prefix' => 'admin','namespace' => 'Admin'],function (){
    //用户后台分组
    /*****************接口开始************************/
    Route::match(['get','post'],'check_api_token','MemberController@check_api_token');//验证api_token
    Route::match(['get','post'],'admin/login','MemberController@login')->name('login');//验证api_token
    //用户登录注册管理
    Route::get('sms','MemberController@Sms');//发送短信验证码
    //注册
    Route::get('MemberReg','MemberController@MemberReg');//注册
    Route::get('MemberLogin','MemberController@MemberLogin');//验证登录
    Route::get('UpdPwd','MemberController@UpdPwd');//会员修改密码

     //微信登录
    // Route::get('weixin', 'WeixinController@redirectToProvider');
    // Route::get('wx_callback', 'WeixinController@handleProviderCallback');


    //我的----------------------------
    Route::get('mydata','MemberController@my')->middleware('auth:api');//我的->首页
    Route::get('my_focus','MemberController@my_focus')->middleware('auth:api');//我的->关注列表(我关注的人)
    Route::get('my_fans','MemberController@my_fans')->middleware('auth:api');//我的->关注列表(粉丝)
    Route::get('search_user','MemberController@search_user')->middleware('auth:api');//我的->搜索用户
    Route::get('focus','MemberController@focus')->middleware('auth:api');//我的->关注
    Route::get('show_user_content','MemberController@show_user_content')->middleware('auth:api');//关注详情
    Route::get('cancel_focus','MemberController@cancel_focus')->middleware('auth:api');//我的->取消关注-
    Route::get('nice','MemberController@nice')->middleware('auth:api');//我的->点赞
    Route::get('comments','CommentController@comments')->middleware('auth:api');//我的->发表评论
    Route::get('re_comments','CommentController@re_comments')->middleware('auth:api');//我的->回复评论
    Route::get('del_comments','CommentController@del_comments')->middleware('auth:api');//博主或用户删除评论及回复
    Route::get('query_comment','CommentController@query_comment')->middleware('auth:api');//查询某一条动态/话题下的所有评论
    Route::get('del_dy','DynamicController@del_dy')->middleware('auth:api');//我的->个人动态->删除动态
    Route::post('release_dynamic','DynamicController@release_dynamic')->middleware('auth:api');//我的->个人动态->发布动态
    Route::get('my_profile','MemberController@my_profile')->middleware('auth:api');//我的->个人资料->首页
    Route::get('upd_member','MemberController@member_upd')->middleware('auth:api');//我的->个人资料->修改昵称,性别,城市
    Route::get('upd_member_phone','MemberController@upd_member_phone')->middleware('auth:api');//我的->个人资料->修改手机号码
    Route::get('upd_member_addr','MemberController@upd_member_addr')->middleware('auth:api');//我的->个人资料->更新收货地址
    Route::post('upd_member_avatar','MemberController@upd_member_avatar')->middleware('auth:api');//我的->个人资料->更新头像
    Route::get('medal/select_medal','MedalController@select_medal')->middleware('auth:api');//我的->奖章->首页
    Route::get('rankings/see_day_ranking','RankingsController@see_day_ranking')->middleware('auth:api');//我的->步数排行->日
    Route::get('rankings/see_month_ranking','RankingsController@see_month_ranking')->middleware('auth:api');//我的->步数排行->月
    Route::get('rankings/see_total_ranking','RankingsController@see_total_ranking')->middleware('auth:api');//我的->步数排行->总
    Route::get('merchant/show_merchant','MerchantController@show_merchant')->middleware('auth:api');//我的->收藏商家->首页
    Route::get('merchant/show_merchant_content','MerchantController@show_merchant_content')->middleware('auth:api');//我的->收藏商家->详情
    Route::get('merchant/collection_merchants','MerchantController@collection_merchants')->middleware('auth:api');//我的->收藏商家->收藏
    Route::get('merchant/cancel_collection_merchants','MerchantController@cancel_collection_merchants')->middleware('auth:api');//我的->收藏商家->取消收藏
    Route::get('merchant/cancels_collection_merchants','MerchantController@cancels_collection_merchants')->middleware('auth:api');//我的->收藏商家->取消收藏(批量编辑)
    Route::get('coupon/available','CouponController@available')->middleware('auth:api');//查看我的优惠券(未使用)
    Route::get('coupon/expired','CouponController@expired')->middleware('auth:api');//查看我的优惠券(已过期)
    Route::get('coupon/has_been_used','CouponController@has_been_used')->middleware('auth:api');//查看我的优惠券(已使用)
    Route::get('appraise/have_evaluation','AppraiseController@have_evaluation')->middleware('auth:api');//商家打星(评价)页面,已评价
    Route::get('appraise/no_evaluation','AppraiseController@no_evaluation')->middleware('auth:api');//未评价
    Route::get('appraise/show_evaluation','AppraiseController@show_evaluation')->middleware('auth:api');//评价页面
    Route::post('appraise/appraise','AppraiseController@appraise')->middleware('auth:api');//评价(打星)
    Route::get('convertible','IntMallController@convertible')->middleware('auth:api');//我的->积分->首页(可兑换)
    Route::get('exchange','IntMallController@exchange')->middleware('auth:api');//兑换积分商品
    Route::get('hasChange','IntMallController@hasChange')->middleware('auth:api');//我的->积分商城(已兑换)
    Route::get('ActivityPrizes','ActMallController@Activity_prizes')->middleware('auth:api');//我的->赛事商城(赛事奖品)
    Route::get('record','IntMallController@record')->middleware('auth:api'); //兑换记录
    Route::post('opinion_feedback','FeedbackController@create_feedback')->middleware('auth:api');//意见反馈->反馈
    Route::get('opinion_feedback_record','FeedbackController@opinion_feedback_record')->middleware('auth:api');//反馈记录,可能要修改,如何修改要看页面效果图
    Route::get('activity/my_activity','ActivityController@my_activity')->middleware('auth:api');//查询接口(我的->我的活动页面)
    //安卓下载包 http://www.yixitongda.com/admin/download/android
    Route::get('download/android',function(){
        return response()->download(realpath(base_path('public/android')).'/paoquan1_1.apk', 'paoquan1_1.apk');
    });

//跑券(地图)----------------------------
    //优惠券接口
    Route::get('coupon/select_coupon','CouponController@select_coupon')->middleware('auth:api');//查找周边优惠券
    Route::get('coupon/show_coupon','CouponController@show_coupon')->middleware('auth:api');//查看优惠券详情页面
    //下列两个接口,接口文档在'我的'中
    Route::get('coupon/get_coupon','CouponController@get_coupon')->middleware('auth:api');//获取优惠券
    Route::get('coupon/consume','CouponController@consume')->middleware('auth:api');//使用优惠券

    //消息通知接口
    Route::get('inform/new_alerts','InformController@new_alerts')->middleware('auth:api');//APP启动,查看是否有新的消息通知
    Route::get('inform/see_alerts','InformController@see_alerts')->middleware('auth:api');//用户点击,查看消息通知
    Route::get('inform/msm_inf','InformController@msm_inf')->middleware('auth:api');//系统调用接口——有新评论提醒

//排行----------------------------

    Route::get('rankings/see_ranking','RankingsController@see_ranking')->middleware('auth:api');//查看用户排行
    Route::get('They_count','StepsController@They_count')->middleware('auth:api');//今日步数

    /*赛事接口开始*/

    Route::get('activity/activity','ActivityController@activity')->middleware('auth:api');//查询接口(活动页面)
    Route::get('activity/details','ActivityController@details')->middleware('auth:api');//赛事详情页面
    Route::get('activity/enrol','ActivityController@enrols')->middleware('auth:api');//赛事报名
    Route::get('activity/exchange','ActivityController@exchange')->middleware('auth:api');//赛事领奖
    Route::get('perseverance/clock_in','PerseveranceController@clock_in')->middleware('auth:api');//毅力使者打卡接口
    Route::get('takeflag/select_takefalg','TakeFlagController@select_takefalg')->middleware('auth:api');//查询周边的旗子
    Route::get('takeflag/get_takefalg','TakeFlagController@get_takefalg')->middleware('auth:api');//获取旗子

//排行----------------------------
    /* 动态 */
    Route::get('dynamic/show_list','DynamicController@show_list')->middleware('auth:api');//动态首页即动态列表
    Route::get('dynamic/show_dy_content','DynamicController@show_dy_content')->middleware('auth:api');//动态详情页
    Route::get('dynamic/show_nice_content','DynamicController@show_nice_content')->middleware('auth:api');//点赞详情页

    /*互换*/
    Route::get('swap/show_list','SwapController@show_list')->middleware('auth:api');//互换首页  还没测  注意,在兑换的时候,需要发布人id
    Route::get('swap/select_release_swap','SwapController@select_release_swap')->middleware('auth:api');//查询发布互换
    Route::get('swap/release_swap','SwapController@release_swap')->middleware('auth:api');//发布互换
    Route::get('swap/details','SwapController@details')->middleware('auth:api');//互换详情页
    Route::get('swap/exchange','SwapController@exchange')->middleware('auth:api');//兑换

    /*话题*/
    Route::get('topic/show_list','SubjectController@show_list')->middleware('auth:api');//话题首页
    Route::get('topic/details','TopicController@details')->middleware('auth:api');//话题详情
    Route::post('topic/release_topic','TopicController@release_topic')->middleware('auth:api');//发布话题
    Route::get('topic/del_topic','TopicController@del_topic')->middleware('auth:api');//删除话题

    //公共图片上传接口
    Route::post('img/uploads','AppraiseController@uploads')->middleware('auth:api');

//协议
    //接口测试专用
    Route::get('test','TestController@test')->middleware('auth:api');
    // Route::get('t3','TestController@t3');
    // Route::post('t2','TestController@t2');
    // Route::get('tt','TestController@tt');//测试偏差
    // Route::get('text',function (){
    //     return view('admin.xieyi.1');
    // });

    /*******************接口结束**********************/

    // 文件上传处理方法
    Route::post('some_upload_img','TopicController@uploads');
    //发送消息
    Route::post('inform/inform','InformController@inform');
    //取消发送
    Route::post('inform/inform2','InformController@inform2');
    //后台登录
    Route::match(['get', 'post'], 'login', 'IndexController@login');
    //ajax获取管理员列表
    Route::post('admin/ajax_list', 'AdminController@ajax_list');
    //ajax获取用户列表
    Route::post('member/ajax_list', 'MemberController@ajax_list');
    //ajax获取商家列表
    Route::post('merchant/ajax_list', 'MerchantController@ajax_list');
    //ajax获取商家分类列表
    Route::post('ification/ajax_list', 'IficationController@ajax_list');
    //ajax获取优惠券列表
    Route::post('coupon/ajax_list', 'CouponController@ajax_list');
    //ajax获取互换信息列表
    Route::post('swap/ajax_list', 'SwapController@ajax_list');
    //ajax获取互换信息->用户列表
    Route::get('swap/ajax_coupon', 'SwapController@ajax_coupon');
    //ajax获取积分商城列表
    Route::post('intmall/ajax_list', 'IntMallController@ajax_list');
    //批量增加优惠券
    Route::any('coupon/creates', 'CouponController@creates');
    Route::any('coupon/stores', 'CouponController@stores');
    //动态表
    Route::post('dynamic/ajax_list', 'DynamicController@ajax_list');
    //话题分类表
    Route::post('subject/ajax_list', 'SubjectController@ajax_list');
    //话题表
    Route::post('topic/ajax_list', 'TopicController@ajax_list');
    //活动商城表
    Route::post('actmall/ajax_list', 'ActMallController@ajax_list');
    //活动表
    Route::post('activity/ajax_list', 'ActivityController@ajax_list');
    //优惠券图片表
    Route::post('picture/ajax_list', 'PictureController@ajax_list');
    //商家评价表
    Route::post('appraise/ajax_list', 'AppraiseController@ajax_list');
    //评论表
    Route::post('comment/ajax_list', 'CommentController@ajax_list');
    //奖章
    Route::post('medal/ajax_list', 'MedalController@ajax_list');
    //排行
    Route::post('rankings/ajax_list', 'RankingsController@ajax_list');
    //通知
    Route::post('inform/ajax_list', 'InformController@ajax_list');
    //反馈
    Route::post('feedback/ajax_list', 'FeedbackController@ajax_list');
    //积分商品发货
    Route::post('theDelivery/ajax_list', 'TheDeliveryController@ajax_list');
    Route::get('theDelivery/undo/{id}', 'TheDeliveryController@undo');//积分商品取消发货
    Route::post('health/ajax_list', 'HealthController@ajax_list');
    Route::get('health/showadd/{add}', 'HealthController@showadd');//健康达人展示收货地址
    Route::get('health/send/{member_id}', 'HealthController@send');//健康达人发货
    Route::get('health/undo/{member_id}', 'HealthController@undo');//健康达人取消发货
    Route::post('perseverance/ajax_list', 'PerseveranceController@ajax_list');
    Route::get('perseverance/showadd/{add}', 'PerseveranceController@showadd');//毅力使者展示收货地址
    Route::get('perseverance/send/{member_id}', 'PerseveranceController@send');//毅力使者发货
    Route::get('perseverance/undo/{member_id}', 'PerseveranceController@undo');//毅力使者取消发货
    Route::post('takeflag/ajax_list', 'TakeFlagController@ajax_list');
    Route::get('takeflag/showadd/{add}', 'TakeFlagController@showadd');//夺旗先锋展示收货地址
    Route::get('takeflag/send/{member_id}', 'TakeFlagController@send');//夺旗先锋发货
    Route::get('takeflag/undo/{member_id}', 'TakeFlagController@undo');//夺旗先锋取消发货
    Route::get('rankings/showcontent/{rankings_id}', 'RankingsController@showcontent');//排行->查看详细排名

    Route::get('dynamic/recycling', 'DynamicController@recycling');//动态-回收站
    Route::post('dynamic/recycling_list', 'DynamicController@recycling_list');//动态-回收站-查询数据
    Route::post('dynamic/restore', 'DynamicController@restore');//动态-回收站-恢复

    //管理页后台认证  除login页面外,所有控制器进行登录防翻墙
    Route::group(['middleware'=>['web','checkadmin']], function () {
//    Route::group(['middleware'=>'web'], function () {//测试用
        // 退出登录
        Route::get('logout','IndexController@logout');
        //后台首页
        Route::any('index', 'IndexController@index');
        //后台欢迎界面
        Route::any('welcome', function () {
            return view('admin.index.welcome');
        });
        //退出登录
        Route::get('loginout', 'IndexController@loginout');
        //管理员admin资源控制
        Route::resource('admin','AdminController');
        //用户users资源控制
        Route::resource('member','MemberController');
        //商家 merchant 资源控制
        Route::resource('merchant','MerchantController');
        //商家分类 ification 资源控制
        Route::resource('ification','IficationController');
        //优惠券管理 coupon 资源控制
        Route::resource('coupon','CouponController');
        //互换管理 swap 资源控制
        Route::resource('swap','SwapController');
        //互换管理 IntMall 资源控制
        Route::resource('intMall','IntMallController');
        //动态管理
        Route::resource('dynamic','DynamicController');
        Route::any('dy_destroy','DynamicController@dy_destroy');
        //话题分类管理
        Route::resource('subject','SubjectController');
        //话题管理
        Route::resource('topic', 'TopicController');
        //活动商城管理
        Route::resource('actmall', 'ActMallController');
        //活动管理
        Route::resource('activity', 'ActivityController');
        //优惠券图片管理
        Route::resource('picture', 'PictureController');
        //商家评价管理 只作为借口
        Route::resource('appraise', 'AppraiseController');
        //用户评论管理
        Route::resource('comment', 'CommentController');
        //强制删除某条评论及其回复
        Route::resource('pun_comments','CommentController@pun_comments');
        //奖章管理
        Route::resource('medal', 'MedalController');
        //排行管理
        Route::resource('rankings', 'RankingsController');
        //通知管理
        Route::resource('inform', 'InformController');
        //反馈管理
        Route::resource('feedback', 'FeedbackController');
        //健康达人
        Route::resource('health', 'HealthController');
        //毅力使者
        Route::resource('perseverance', 'PerseveranceController');
        //夺旗先锋
        Route::resource('takeflag', 'TakeFlagController');
        //积分商品发货
        Route::resource('theDelivery', 'TheDeliveryController');
    });
});
