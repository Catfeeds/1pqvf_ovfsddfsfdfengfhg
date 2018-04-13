@extends('public.public')
@section('title')编辑用户@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/member/'.$memberInfo->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-member-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>昵称：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $memberInfo->nickname or ''}}" class="input-text"  placeholder="昵称" id="nickname" name="nickname">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>密码：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="" class="input-text"  placeholder="不填则为不修改密码" id="password" name="password">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>确认密码：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="" class="input-text"  placeholder="不修改密码则无需填写" id="pwd" name="pwd">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">上传头像：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="avatar" class="input-file">
                    </span>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>城市：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select class="select" name="city" id="">
                            <option value="深圳">深圳</option>
                            <option value="广州">广州</option>
                        </select>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>性别：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="sex" type="radio" value="2" id="sex-1" @if($memberInfo->sex == 2) checked @endif>
                            <label for="sex-1">男</label>
                        </div>
                        <div class="radio-box">
                            <input name="sex" type="radio" value="1" id="sex-2" @if($memberInfo->sex == 1) checked @endif>
                            <label for="sex-2">女</label>
                        </div>
                        <div class="radio-box">
                            <input name="sex" type="radio" value="3" id="sex-3" @if($memberInfo->sex == 3) checked @endif>
                            <label for="sex-2">保密</label>
                        </div>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">年龄：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text"  value="{{ $memberInfo->age or '' }}" autocomplete="off"  placeholder="年龄" id="age" name="age">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">身高：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text"  value="{{ $memberInfo->height or '' }}" autocomplete="off"  placeholder="身高" id="height" name="height">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">体重：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text"  value="{{ $memberInfo->weight or ''}}" autocomplete="off"  placeholder="体重" id="weight" name="weight">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">收货地址：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        姓　　名:<input type="text" class="input-text"  value="{{ $memberInfo->address->address->nickname or '' }}" autocomplete="off"  placeholder="姓名" id="address_nickname" name="address_nickname">
                        手机号码:<input type="text" class="input-text"  value="{{ $memberInfo->address->address->phone or '' }}" autocomplete="off"  placeholder="手机号" id="address_phone" name="address_phone">
                        省　　份:<input type="text" class="input-text"  value="{{ $memberInfo->address->address->province or '' }}" autocomplete="off"  placeholder="省份" id="address_province" name="address_province">
                        详细地址;<input type="text" class="input-text"  value="{{ $memberInfo->address->address->address or '' }}" autocomplete="off"  placeholder="详细地址" id="address_address" name="address_address">
                        邮政编码：<input type="text" class="input-text"  value="{{ $memberInfo->address->address->zip_code or '' }}" autocomplete="off"  placeholder="邮政编码" id="address_zip_code" name="address_zip_code">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>禁用状态：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="disabled_at" type="radio" value="1" id="status-1" @if($memberInfo->disabled_at==null) checked @endif >
                            <label for="status-1">启用</label>
                        </div>
                        <div class="radio-box">
                            <input name="disabled_at" type="radio" value="0" id="status-2" @if($memberInfo->disabled_at!=null) checked @endif >
                            <label for="status-2">禁用</label>
                        </div>
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>手机号：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="" class="input-text"  placeholder="手机号码" id="phone" name="phone">
                    </div>
                </div>

                <div class="row cl">
                    <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
                        <input class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                    </div>
                </div>
            </form>
    </article>
@endsection
@section('footer-script')
    <script type="text/javascript" src="{{ asset('admin')  }}/lib/jquery.validation/1.14.0/jquery.validate.js"></script>
    <script type="text/javascript" src="{{ asset('admin')  }}/lib/jquery.validation/1.14.0/validate-methods.js"></script>
    <script type="text/javascript" src="{{ asset('admin')  }}/lib/jquery.validation/1.14.0/messages_zh.js"></script>
    {{--layer--}}
    <script src="{{ asset('admin') }}/lib/layer/2.4/layer.js"></script>
    <script src="{{ asset('admin') }}/lib/icheck/jquery.icheck.min.js"></script>
    <!--请在下方写此页面业务相关的脚本-->
    <script>
        $(function(){
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-member-edit").validate({
                rules:{
                    password:{
                        minlength:3,
                        maxlength:20
                    },
                    pwd:{
                        equalTo: "#password"
                    },
                    age:{
                        range:[1,150]
                    },
                    height:{
                        range:[1,300]
                    },
                    weight:{
                        range:[1,1000]
                    },
                },
                onkeyup:false,
                focusCleanup:true,
                success:"valid",
                submitHandler:function(form){
                    $(form).ajaxSubmit(function(msg){
                        if( msg.status != 'success' ){
                            layer.alert(msg.msg, {
                                icon: 5,
                                skin: 'layer-ext-moon'
                            });
                        }else{
                            layer.msg('修改成功！', {
                                icon: 1,
                                skin: 'layer-ext-moon'
                            },function(){
                                parent.location.reload();
                                var index = parent.layer.getFrameIndex( window.name );
                                parent.layer.close(index);
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection