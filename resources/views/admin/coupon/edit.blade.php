@extends('public.public')
@section('title')编辑优惠券@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/coupon/' . $couponInfo->id ) }}" method="post"  class="form form-horizontal" id="form-coupon-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券编号：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $couponInfo->cp_id }}" class="input-text"  placeholder="优惠券编号" id="cp_id" name="cp_id">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属商家：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select name="merchant_id" id="merchant_id">
                            @foreach($merchantInfo as $v)
                                <option value="{{ $v->id  }}" @if($couponInfo->merchant_id==$v->id) selected @endif>{{ $v->nickname  }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>类型：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="type" type="radio" value="1" id="status-1" @if($couponInfo->start_at==null) checked @endif>
                            <label for="status-1">无期限</label>
                        </div>
                        <div class="radio-box">
                            <input name="type" type="radio" value="0" id="status-2" @if($couponInfo->start_at!=null) checked @endif>
                            <label for="status-2">有时效</label>
                        </div>
                    </div>
                </div>
                <div class="row cl" id="hidden_t" style="display: none">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券开始时间：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="datetime-local" class="input-text"  placeholder="开始时间" id="start_at" name="start_at" >
                        <input type="datetime-local" class="input-text"  placeholder="结束时间" id="end_at" name="end_at" style="margin-top: 5px;">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠形式：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="action" type="radio" value="2" id="status-3"  @if($couponInfo->action==2) checked @endif>
                            <label for="status-3">折扣</label>
                        </div>
                        <div class="radio-box">
                            <input name="action" type="radio" value="1" id="status-4" @if($couponInfo->action==1) checked @endif>
                            <label for="status-4">减免金额</label>
                        </div>
                    </div>
                </div>
                <div class="row cl" id="" >
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠价格：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="{{  $couponInfo->price }}"  placeholder="如果是折扣,请直接填写折扣率(如9.5),金额则直接填写数字(单位元,如30)" id="price" name="price">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券使用状态：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="status" type="radio" value="1" id="status-8"  @if($couponInfo->status==1) checked @endif>
                            <label for="status-3">未使用</label>
                        </div>
                        <div class="radio-box">
                            <input name="status" type="radio" value="2" id="status-9" @if($couponInfo->status==2) checked @endif>
                            <label for="status-4">已使用</label>
                        </div>
                        <div class="radio-box">
                            <input name="status" type="radio" value="3" id="status-9" @if($couponInfo->status==3) checked @endif>
                            <label for="status-4">过期</label>
                        </div>
                    </div>
                </div>
                <div class="row cl" id="" >
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>描述：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="{{ $couponInfo->note  }}" placeholder="如:满100可用" id="note" name="note">
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
            //第一次加载的时候先判断,选择的是否是有时效
            $(document).ready(function () {
                if( $("input[name='type']").attr('checked') == 'checked' ){
                    $('#hidden_t').hide();
                    //清除时间
                    $("input[name='start_at']").val(null);
                    $("input[name='end_at']").val(null);
                }else{
                    //如果选择了有期限
                    $('#hidden_t').show();
                    //恢复默认的时间
                }
            });

            $("input[name='type']").click(function () {
                if( $(this).val() == 1  ){
                    $('#hidden_t').hide();
                    //清除时间
                    $("input[name='start_at']").val(null);
                    $("input[name='end_at']").val(null);
                }else{
                    //如果选择了有期限
                    $('#hidden_t').show();
                }
            });
            $("#form-coupon-edit").validate({
                rules:{
                    username:{
                        required:true,
                        minlength:2,
                        maxlength:4
                    },
                    note:{
                        required:true,
                        minlength:3,
                        maxlength:20
                    },
                    email:{
                        required:true,
                        email:true,
                    }
                },
                onkeyup:false,
                focusCleanup:true,
                success:"valid",
                submitHandler:function(form){
                    $(form).ajaxSubmit(function(msg){
                        if( msg.status != 'success' ){  // 失败
                            layer.alert(msg.msg, {
                                icon: 5,
                                skin: 'layer-ext-moon' //该皮肤由layer.seaning.com友情扩展。关于皮肤的扩展规则，去这里查阅
                            });
                        }else{ // 成功
                            layer.msg('修改成功！', {
                                icon: 1,
                                skin: 'layer-ext-moon' //该皮肤由layer.seaning.com友情扩展。关于皮肤的扩展规则，去这里查阅
                            },function(){
                                parent.location.reload(); // 让父级窗口刷新
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