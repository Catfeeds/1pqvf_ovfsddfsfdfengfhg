@extends('public.public')
@section('title')编辑奖章@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/medal/'.$medal->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-medal-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}

                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">奖章图片：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="medal_url" class="input-file">
                    </span>
                        <br>
                        <img src="/{{ $medal->medal_url  }}" style="width: 100px;" alt="">
                        <input type="hidden" name="old_url" value="{{ $medal->medal_url  }}">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖章简介：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" value="{{$medal->note}}" class="input-text"  placeholder="" id="note" name="note">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖章详情：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" value="{{$medal->content}}" class="input-text"  placeholder="" id="content" name="content">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖励积分：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" value="{{$medal->rewards}}" class="input-text"  placeholder="获得奖章,所奖励的积分数量" id="rewards" name="rewards">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>类型：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="type" type="radio" value="1" id="sex-1"  @if($medal->type==1) checked @endif>
                            <label for="sex-1">运动</label>
                        </div>
                        <div class="radio-box">
                            <input name="type" type="radio" value="2" id="sex-2" @if($medal->type==2) checked @endif>
                            <label for="sex-2">优惠券</label>
                        </div>
                        <div class="radio-box">
                            <input name="type" type="radio" value="3" id="sex-3" @if($medal->type==3) checked @endif>
                            <label for="sex-3">活动</label>
                        </div>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>类型：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="pid" type="radio" value="0" id="sex-4" @if($medal->pid == 0) checked @endif>
                            <label for="sex-1">点亮</label>
                        </div>
                        <div class="radio-box">
                            <input name="pid" type="radio" value="1" id="sex-5" @if($medal->pid != 0) checked @endif>
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
            $(document).ready(function () {
                if( $("input[name='pid']:checked").val() == 1 ){
                    $('#bs').show();
                    @foreach($bright as $v)
                     $("#bright").append("<option value='{{ $v->id  }}'' @if($v->id==$medal->pid) selected @endif >{{ $v->note }}</option>");
                    @endforeach
                }
            })
            $("input[name='pid']").change(function () {
                if( $(this).val() != 0  ){
                    //选择了暗淡
                    $('#bs').show();
                    @foreach($bright as $v)
                     $("#bright").append("<option value='{{ $v->id  }}'' @if($v->id==$medal->pid) selected @endif >{{ $v->note }}</option>");
                    @endforeach
                }else{
                    $('#bs').hide();
                    $("#bright").empty();
                }
            })

            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-medal-edit").validate({
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