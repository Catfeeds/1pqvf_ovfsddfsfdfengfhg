@extends('public.public')
@section('title')编辑互换@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/swap/' . $swapInfo->id ) }}" method="post"  class="form form-horizontal" id="form-swap-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                 <input type="hidden"  value="{{ $swapInfo->id }}" name="id">
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券id:</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        {{ $swapInfo->coupon_id }}
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>当前状态<br>(1:已被兑换/2:还没被兑换)：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        @if($swapInfo->status==2)
                            satus:{{ $swapInfo->status }}
                            （已发布的且未兑换的优惠券）
                        @else
                            satus:{{ $swapInfo->status }}<span class="c-red">只有已发布的且未兑换的优惠券才能修改</span>
                        @endif
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>积分：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $swapInfo->integral }}" class="input-text"  placeholder="修改积分只能是数值" id="integral" name="integral">
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

            $("#form-swap-edit").validate({
                rules:{
                    integral:{
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