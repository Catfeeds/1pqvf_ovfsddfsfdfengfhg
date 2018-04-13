@extends('public.public')
@section('title')编辑动态@endsection
@section('content')
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/webuploader/0.1.5/webuploader.css" />
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/jiaoben2576/diyUpload/css/diyUpload.css" />
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/dynamic/'.$dynamicInfo->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-dynamic-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>发布人：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $Info->member->nickname }}" class="input-text" readonly  placeholder="昵称" id="nickname" name="nickname">
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>图片：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <div id="box">
                            <div id="test" ></div>
                        </div>
                        <input type="hidden"  id='img_url'>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>原图：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <span id="xx"></span>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>内容：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <textarea cols="80" rows="12" name="content" id="content"  class="layui-textarea"></textarea>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>发布的城市：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select class="select"  name="addres" id="addres">
                            <option value="深圳" @if($dynamicInfo->addres=='深圳') selected @endif>深圳</option>
                            <option value="广州" @if($dynamicInfo->addres=='广州') selected @endif>广州</option>
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
            var proinfo = '{!! $dynamicInfo->content !!}';
            $('#content').html(proinfo);

            //循环取出图片
            var img = "{{$dynamicInfo->img_url}}";
            if( $('#xx').html() != null){
                //先将img分割成数组
                var arr = new Array();
                arr = img.split(',');
                for(var i=0;i<arr.length;i++)
                {
                    arr[i] = arr[i].replace('[','');
                    arr[i] = arr[i].replace(']','');
                    var reg=/\\/g;
                    arr[i] = arr[i].replace(reg,'');
                    var reg=/"/g;
                    arr[i] = arr[i].replace(reg,'');
                    var reg=/&quot;/g;
                    arr[i] = arr[i].replace(reg,'');
                    $('#xx').append('<br><img style="width: 100px;" src="/'+ arr[i] +'"><input type="hidden" name="old_url[]" value="'+ arr[i] +'">');
                }
            }

            //多图上传
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

            $("#form-dynamic-edit").validate({
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