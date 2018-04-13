@extends('public.public')
@section('title')添加互换@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/swap')  }}" method="post"  class="form form-horizontal" id="form-swap-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>发布人：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="member_id" id="member_id">
                        @foreach($memberInfo as $v)
                        <option value="{{ $v->id  }}">{{ $v->nickname  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>选择优惠券：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select"  name="coupon_id" id="coupon_id">
                    </select>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>兑换所需积分：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text" autocomplete="off"  placeholder="所需积分只能是大于1" id="integral" name="integral">
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
            //ajax获取所选用户的所有可交易的优惠券
            $(document).ready(function () {
                var id = $('#member_id').val();
                $.ajax({
                    type:"GET",
                    url:"{{ url('admin/swap/ajax_coupon') }}",
                    dataType:"json",
                    data:{'member_id':id},
                    success:function(data){
                        if( data.couponInfo.length == 0 ){
                            $("#coupon_id").append("<option value=''>"+'没有可交易的优惠券'+"</option>");
                        }else {
                            for(var i=0;i<data.couponInfo.length;i++){
                                var danwei = '';
                                if(data.couponInfo[i].action == 1){
                                    danwei += '元';
                                }else{
                                    danwei += '折';
                                }
                                $("#coupon_id").append("<option value='"+data.couponInfo[i].id+"'>"+data.couponInfo[i].merchant.nickname+':'+data.couponInfo[i].price+ danwei +"</option>");
                                danwei = '';
                            }
                        }
                    }
                })
            })
            $(document).on('change','#member_id',function () {
                $("#coupon_id").find("option").remove();
                var id = $(this).val();
                $.ajax({
                    type:"GET",
                    url:"{{ url('admin/swap/ajax_coupon') }}",
                    dataType:"json",
                    data:{'member_id':id},
                    success:function(data){
                        if( data.couponInfo.length == 0 ){
                            $("#coupon_id").append("<option value=''>"+'没有可交易的优惠券'+"</option>");
                        }else {
                            for(var i=0;i<data.couponInfo.length;i++){
                                var danwei = '';
                                if(data.couponInfo[i].action == 1){
                                    danwei += '元';
                                }else{
                                    danwei += '折';
                                }
                                $("#coupon_id").append("<option value='"+data.couponInfo[i].id+"'>"+data.couponInfo[i].merchant.nickname+':'+data.couponInfo[i].price+ danwei +"</option>");
                                danwei = '';
                            }
                        }
                    }
                })
            });
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-swap-add").validate({
                rules:{
                    integral:{
                        required:true,
                    }
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