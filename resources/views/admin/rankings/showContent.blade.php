@extends('public.public')
@section('title')添加话题@endsection
@section('content')
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/webuploader/0.1.5/webuploader.css" />
    <link rel="stylesheet" href="{{ asset('admin') }}/lib/jiaoben2576/diyUpload/css/diyUpload.css" />
    <article class="page-container">
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
            var content = '{!! $info !!}';
            var obj = eval('(' + content + ')');
            var str = ' <table class="table table-border table-bordered table-bg radius table-striped"> <thead> <tr> <th>昵称</th> <th>名次</th>  </tr> </thead><tbody>';
                $.each(obj,function(i,v){
                    str += '<tr><td> '+ v.nickname +' </td>  <td> 第 ' + (i+1) + ' 名 </td></tr>';
                });
                str += ' </tbody>  </table>'
            $('.page-container').html(str);
        });
    </script>
@endsection