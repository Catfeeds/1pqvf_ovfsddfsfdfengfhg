@extends('public.public')
@section('title')编辑优惠券@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/coupon/' . $couponInfo->id ) }}" method="post"  class="form form-horizontal" id="form-coupon-edit">
            {{ csrf_field() }}
            {{ method_field('put') }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3">优惠券编号：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    {{  $couponInfo->cp_number }}
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属商家：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="merchant_id" id="merchant_id">
                        @foreach($merchantInfo as $v)
                            <option value="{{ $v->id  }}" @if($couponInfo->merchant_id==$v->id) selected @endif>{{ $v->nickname  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row cl" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>该商家优惠券：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="cp_cate_id" id="picture_id">
                    </select>
                </div>
            </div>
            <div class="row cl" id="hidden_t" name="xz" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>开始时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="datetime-local" class="input-text"  placeholder="开始时间" id="start_at" name="start_at" ><br/>
                    <font size="1" color="red">*开始时间必须在活动时间内；务必为00:00  例如：2018/10/1 00:00</font>
                </div>
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>结束时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="datetime-local" class="input-text"  placeholder="结束时间" id="end_at" name="end_at" style="margin-top: 5px;"><br/>
                    <font size="1" color="red">*结束时间必须在活动时间内；务必为23:59  例如：2018/10/31 23:59</font>
                </div>
            </div>
            <div class="row cl" id="" >
                <label class="form-label col-xs-4 col-sm-3">描述：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  value="{{ $couponInfo->note }}" id="note" name="note">
                </div>
            </div>
            <div class="row cl">
                <div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
                    <input id="sub" class="btn btn-primary radius" type="submit" value="&nbsp;&nbsp;提交&nbsp;&nbsp;">
                </div>
            </div>
        </form>
    </article>
@endsection
@section('footer-script')
    <script type="text/javascript" src="{{ asset('admin')  }}/lib/jquery.validation/1.14.0/jquery.validate.js"></script>
    <script type="text/javascript" src="{{ asset('admin')  }}/lib/jquery.validation/1.14.0/validate-methods.js"></script>
    <script type="text/javascript" src="{{ asset('admin')  }}/lib/jquery.validation/1.14.0/messages_zh.js"></script>
    <script src="{{ asset('admin') }}/lib/layer/2.4/layer.js"></script>
    <script src="{{ asset('admin') }}/lib/icheck/jquery.icheck.min.js"></script>
    <!--请在下方写此页面业务相关的脚本-->
    <script>
        $(function(){
            $(document).ready(function () {
                //商家的id
                var id = $("select option:checked").val();
                @foreach($pictureInfo as $v)
                    var id_ = '{{$v->merchant_id}}';
                    if(id_ == id){
                        $("#picture_id").append("<option value='{{ $v->id  }}''>{{ $v->coupon_name }}　活动时间：{{ $v->send_start_at}}至{{ $v->send_end_at}}　面额/折扣：{{ $v->coupon_money}} @if($couponInfo->merchant_id==$v->id) selected @endif </option>");
                    }
                @endforeach
            })
            $('#merchant_id').change(function () {
                //改变的时候,先清除原先的
                $("#picture_id").empty();
                var id = $("select option:checked").val();
                @foreach($pictureInfo as $v)
                var id_ = '{{$v->merchant_id}}';
                if(id_ == id){
                    $("#picture_id").append("<option value='{{ $v->id  }}''>{{ $v->coupon_name }}　　活动时间：：{{ $v->send_start_at}}至{{ $v->send_end_at}}　面额/折扣：{{ $v->coupon_money}}</option>");
                }
                @endforeach
            })
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });


            $("#form-coupon-edit").validate({
                rules:{
                    note:{
                        required:true,
                        minlength:3,
                        maxlength:20
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