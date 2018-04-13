@extends('public.public')
@section('title')添加奖章@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/medal')  }}" method="post"  class="form form-horizontal" id="form-medalc-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">奖章图片：</label>
                <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="medal_url" class="input-file">
                    </span>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖章简介：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="奖章简介" id="note" name="note">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖章详情：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="奖章说明" id="content" name="content">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖励积分：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="获得奖章,所奖励的积分数量" id="rewards" name="rewards">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>获取类型：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="type" type="radio" value="1" id="sex-1" checked>
                        <label for="sex-1">运动</label>
                    </div>
                    <div class="radio-box">
                        <input name="type" type="radio" value="2" id="sex-2">
                        <label for="sex-2">优惠券</label>
                    </div>
                    <div class="radio-box">
                        <input name="type" type="radio" value="3" id="sex-3">
                        <label for="sex-3">活动</label>
                    </div>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>类型：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="pid" type="radio" value="0" id="sex-4" checked>
                        <label for="sex-1">点亮</label>
                    </div>
                    <div class="radio-box">
                        <input name="pid" type="radio" value="1" id="sex-5">
                        <label for="sex-2">暗淡</label>
                    </div>
                </div>
            </div>
            <div class="row cl" id="bs" style="display: none;" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>请选择对应点亮的奖章：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="bright" id="bright">

                    </select>
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
            //如果用户选择了暗淡,则需要找出所有点亮的,将点亮的id替换
            $("input[name='pid']").change(function () {
                if( $(this).val() != 0  ){
                    //选择了暗淡
                    $('#bs').show();
                    @foreach($bright as $v)
                     $("#bright").append("<option value='{{ $v->id  }}''>{{ $v->note }}</option>");
                    @endforeach
                }else{
                    $('#bs').hide();
                    $("#bright").empty();
                }
            })

            $("input[name='type']").change(function () {
                if( $(this).val() == 1  ){
                    $('#hidden_t').hide();
                    //清除时间
                    $("input[name='start_at']").val(null);
                    $("input[name='end_at']").val(null);
                }else{
                    //如果选择了有期限
                    $('#hidden_t').show();
                }
            });

            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });
            $("#form-medalc-add").validate({
                rules:{
                    medal_url:{
                        required:true,
                    },
                    note:{
                        required:true,
                    },
                    type:{
                        required:true,
                    },
                    content:{
                        required:true,
                    },
                    rewards:{
                        required:true,
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