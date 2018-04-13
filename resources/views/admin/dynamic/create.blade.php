@extends('public.public')
@section('title')添加动态@endsection
@section('content')
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/webuploader/0.1.5/webuploader.css" />
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/jiaoben2576/diyUpload/css/diyUpload.css" />
    <article class="page-container">
        <form action="{{ url('admin/dynamic')  }}" method="post"  class="form form-horizontal" id="form-dynamic-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>发布人：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select"  name="member_id" id="">
                        @foreach($memberInof as $item)
                        <option value="{{ $item->id  }}">{{ $item->nickname  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">图片：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <div id="box">
                        <div id="test" ></div>
                    </div>
                    <input type="hidden"  id='img_url'>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>内容：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <textarea cols="80" rows="7" name="content" id="content" placeholder="请输入内容" class="layui-textarea"></textarea>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">发布的地址：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="addres" id="addres">
                        <option value="深圳">深圳</option>
                        <option value="广州">广州</option>
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
    <script src="{{ asset('admin') }}/lib/webuploader/0.1.5/webuploader.min.js"></script>
    <script src="{{asset('admin')}}/lib/jiaoben2576/diyUpload/js/webuploader.html5only.min.js"></script>
    <script src="{{asset('admin')}}/lib/jiaoben2576/diyUpload/js/diyUpload.js"></script>
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

            $('#test').diyUpload({
                url:"{{URL('admin/some_upload_img')}}",
                success:function( data ) {
                    $("#img_url").after("<input type='hidden' name='img_url[]' class='hidpic' value="+data.pic+">");
                },
            });
            $('#show').click(function(){
                var b=$('.hidpic').map(function() {
                    return $(this).val();
                }).get().join(',');
                $('#pics').val(b);
            });

            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });
            $("#form-dynamic-add").validate({
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