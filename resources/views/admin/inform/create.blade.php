@extends('public.public')
@section('title')添加通知@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/inform')  }}" method="post"  class="form form-horizontal" id="form-inform-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">标题：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off"  placeholder="标题" id="title" name="title">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>内容：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <div id="ed"></div>
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
    {{--富文本--}}
    <script type="text/javascript" charset="utf-8" src="{{ asset('admin')  }}/lib/ueditor/1.4.3/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="{{ asset('admin')  }}/lib/ueditor/1.4.3/ueditor.all.min.js"> </script>
    <script type="text/javascript" charset="utf-8" src="{{ asset('admin')  }}/lib/ueditor/1.4.3/lang/zh-cn/zh-cn.js"></script>

    <!--请在下方写此页面业务相关的脚本-->
    <script>
        $(function(){
            //富文本
            var ue = UE.getEditor('ed');
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-inform-add").validate({
                rules:{
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
                        }else{ // 成功
                            layer.msg('添加成功！', {
                                icon: 1,
                                skin: 'layer-ext-moon'
                            },function(){
                                parent.location.reload();
                                // 关闭当前的layer插件的窗口
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