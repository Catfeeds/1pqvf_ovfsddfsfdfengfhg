@extends('public.public')
@section('title')编辑评论@endsection
@section('content')
    <article class="page-container">
            <form action="{{ url('admin/appraise/' . $appraiseInfo->id ) }}" method="post"  class="form form-horizontal" id="form-appraise-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>星级：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select name="appraise" class="select" id="appraise">
                            <option value="1" @if($appraiseInfo->appraise==1) selected @endif>一星</option>
                            <option value="2" @if($appraiseInfo->appraise==2) selected @endif>二星</option>
                            <option value="3" @if($appraiseInfo->appraise==3) selected @endif>三星</option>
                            <option value="4" @if($appraiseInfo->appraise==4) selected @endif>四星</option>
                            <option value="5" @if($appraiseInfo->appraise==5) selected @endif>五星</option>
                        </select>
                        <input type="hidden" name="mer_id" value="{{ $appraiseInfo->mer_id  }}" >
                    </div>
                </div>

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>内容：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <textarea cols="80" rows="12" name="content" id="content"  class="layui-textarea"></textarea>
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
            var proinfo = '{!! $appraiseInfo->content !!}';
            $('#content').html(proinfo);

            $("#form-appraise-edit").validate({
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