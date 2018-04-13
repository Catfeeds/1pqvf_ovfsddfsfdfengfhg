@extends('public.public')
@section('title')编辑商家分类@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/inform/' . $informInfo->id ) }}" method="post"  class="form form-horizontal" id="ification-edit">
                {{ csrf_field() }}
                {{ method_field('put') }} {{-- 修改一定要加上 表单请求伪造 --}}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">标题：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" autocomplete="off"  placeholder="标题" id="title" value="{{ $informInfo->title  }}" name="title">
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
            //回显content的内容到富文本编辑器中
            var ue = UE.getEditor('ed');
            ue.ready(function() {//编辑器初始化完成再赋值
                var a = '';
                ue.setContent(a);
                var proinfo = '{!! $informInfo->content !!}';
                var arr = new Array();
                proinfo = proinfo.replace('[','');
                proinfo = proinfo.replace(']','');
                var reg=/\"\,\"/g;
                proinfo = proinfo.replace(reg,'<br>');
                var reg=/\"/g;
                proinfo = proinfo.replace(reg,'');
                ue.setContent(proinfo);
            })

            // 单选按钮外观效果
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#ification-edit").validate({
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