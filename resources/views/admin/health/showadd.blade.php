@extends('public.public')
@section('title')添加话题@endsection
@section('content')
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/webuploader/0.1.5/webuploader.css" />
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/jiaoben2576/diyUpload/css/diyUpload.css" />
    <article class="page-container">
        <form action=""   class="form form-horizontal" id="form-topic-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">姓名：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off"  id="nickname" >
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">手机号：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off" id="nickname2" >
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">省份：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off" >
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">详细地址：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off"  >
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">邮政编码：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off" >
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

    <!--请在下方写此页面业务相关的脚本-->
    <script>
        $(function(){
            // 单选按钮外观效果  --<
            var content = '{{  $info }}';
            for(var i=0;i<content.length;i++){
                content = content.replace('&quot;','"');
            }
            var obj = eval('(' + content + ')');
            var x=document.getElementsByTagName("input");
            $(x[1]).val(obj.address.nickname);
            $(x[2]).val(obj.address.phone);
            $(x[3]).val(obj.address.province);
            $(x[4]).val(obj.address.address);
            $(x[5]).val(obj.address.zip_code);

        });
    </script>
@endsection