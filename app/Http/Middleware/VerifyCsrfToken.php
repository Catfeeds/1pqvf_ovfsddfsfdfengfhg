<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //排除掉登录接口  -->post
        'admin/MemberLogin',//会员登录
        'admin/MemberReg',//会员注册
        'admin/sms',//短信
        'admin/Member_Upd',//修改会员信息接口
        'admin/some_upload_img',//some_upload_img 多图上传
        'admin/img/uploads',//公共图上传
        'admin/release_dynamic',//发布动态
        'admin/upd_member_avatar',//上传头像
        'admin/appraise/appraise',//商家打星
        'admin/opinion_feedback',//意见反馈
        'admin/topic/release_topic',//发布话题
    ];
}
