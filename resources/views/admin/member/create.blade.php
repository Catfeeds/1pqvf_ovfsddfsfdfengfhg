@extends('public.public')
@section('title')添加用户@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/member')  }}" method="post"  class="form form-horizontal" id="form-member-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>用户：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="用户名称" id="nickname" name="nickname">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">头像：</label>
                <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="avatar" class="input-file">
                    </span>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>手机号码：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="手机号码" id="phone" name="phone">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>初始密码：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="password" class="input-text" autocomplete="off"  placeholder="密码" id="password" name="password">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>确认密码：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="password" class="input-text" autocomplete="off"   placeholder="确认密码" id="pwd" name="pwd">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">城市：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="city" id="city">
                        <option value="深圳">深圳</option>
                        <option value="广州">广州</option>
                    </select>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>性别：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="sex" type="radio" value="2" id="sex-1" checked>
                        <label for="sex-1">男</label>
                    </div>
                    <div class="radio-box">
                        <input name="sex" type="radio" value="1" id="sex-2">
                        <label for="sex-2">女</label>
                    </div>
                    <div class="radio-box">
                        <input name="sex" type="radio" value="3" id="sex-3">
                        <label for="sex-3">保密</label>
                    </div>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">年龄：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off"  placeholder="年龄" id="age" name="age">
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
            //验证手机规则
            jQuery.validator.addMethod("isMobile", function(value, element) {
                var length = value.length;
                var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
                return this.optional(element) || (length == 11 && mobile.test(value));
            }, "请正确填写您的手机号码");
            $("#form-member-add").validate({
                rules:{
                    phone:{
                        required:true,
                        isMobile : true
                    },
                    password:{
                        required:true,
                        minlength:3,
                        maxlength:20
                    },
                    pwd:{
                        required:true,
                        equalTo: "#password"
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
                            layer.msg('添加成功！', {
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