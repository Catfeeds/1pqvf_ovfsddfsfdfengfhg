@extends('public.public')
@section('title')添加优惠券@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/coupon')  }}" method="post"  class="form form-horizontal" id="form-coupon-add">
            {{ csrf_field() }}
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属商家：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="merchant_id" id="merchant_id">
                        @foreach($merchantInfo as $v)
                          <option value="{{ $v->id  }}">{{ $v->nickname  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row cl" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券图片：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="picture_id" id="picture_id">
                    </select>
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>生成方式：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="cr_type" type="radio" value="1" id="status-8" checked >
                        <label for="status-1">商家周边</label>
                    </div>
                    <div class="radio-box">
                        <input name="cr_type" type="radio" value="0" id="status-9" >
                        <label for="status-2">本市区域</label>
                    </div>
                </div>
            </div>
            <div class="row cl" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>生成地区：</label>
                <div class="formControls col-xs-8 col-sm-9" id="ce1" style="display: none">
                    <select class="select" name="address1" id="address1">
                        <option value="罗湖区">罗湖区</option>
                        <option value="福田区">福田区</option>
                        <option value="南山区">南山区</option>
                        <option value="宝安区">宝安区</option>
                        <option value="龙岗区">龙岗区</option>
                        <option value="盐田区">盐田区</option>
                    </select>
                </div>
                <div class="formControls col-xs-8 col-sm-9" id="ce2">
                    <input type="text" class="input-text"  placeholder="请填写详细的商家地址" id="address2" name="address2">
                </div>
            </div>
            <div class="row cl">
                <div class="radio-box" style="display:none">
                    <input name="type" type="hidden" value="0" id="status-2" >
                    <label for="status-2">有时效</label>
                </div>
            {{--</div>--}}
            </div>
            <div class="row cl" id="hidden_t" name="xz" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>开始时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="datetime-local" class="input-text"  placeholder="开始时间" id="start_at" name="start_at" ><br/>
                    <font size="1" color="red">*开始时间务必为00:00  例如：2018/10/1 00:00</font>
                </div>
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>结束时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="datetime-local" class="input-text"  placeholder="结束时间" id="end_at" name="end_at" style="margin-top: 5px;"><br/>
                    <font size="1" color="red">*结束时间务必为23:59  例如：2018/10/31 23:59</font>
                </div>
            </div>

            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠形式：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="action" type="radio" value="2" id="status-3"  checked>
                        <label for="status-3">折扣</label>
                    </div>
                    <div class="radio-box">
                        <input name="action" type="radio" value="1" id="status-4" >
                        <label for="status-4">减免金额</label>
                    </div>
                </div>
            </div>
            <div class="row cl" id="" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠价格：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="如果是折扣,请直接填写折扣率(如9.5),金额则直接填写数字(单位元,如30)" id="price" name="price">
                </div>
            </div>
            <div class="row cl" id="" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>描述：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="如:满100可用" id="note" name="note">
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
                        $("#picture_id").append("<option value='{{ $v->id  }}''>{{ $v->price  }}{{ $v->action==1 ? '元' : '折' }}</option>");
                    }
                @endforeach
            })
            /*当点击按商家/区域的时候,对应的清除*/
            $("input[name='cr_type']").change(function () {
                console.log( $(this).val() )
                if($(this).val() == 1){
                    $('#ce2').show();
                    $('#ce1').hide();
                }else{
                    $('#ce1').show();
                    $('#ce2').hide();
                }
            })
            $('#merchant_id').change(function () {
                //改变的时候,先清除原先的
                $("#picture_id").empty();
                var id = $("select option:checked").val();
                @foreach($pictureInfo as $v)
                var id_ = '{{$v->merchant_id}}';
                if(id_ == id){
                    $("#picture_id").append("<option value='{{ $v->id  }}''>{{ $v->price  }}{{ $v->action==1 ? '元' : '折' }}</option>");
                }
                @endforeach
            })

//            $("input[name='type']").change(function () {
//                if( $(this).val() == 1  ){
//                    $('#hidden_t').hide();
//                    //清除时间
//                    $("input[name='start_at']").val(null);
//                    $("input[name='end_at']").val(null);
//                }else{
//                    //如果选择了有期限
//                    $('#hidden_t').show();
//                }
//            });
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });


            $("#form-coupon-add").validate({
                rules:{},
                onkeyup:false,
                focusCleanup:true,
                success:"valid",
                submitHandler:function(form){//提交后执行以下代码
                    $('#sub').attr("disabled","true"); //防止重复点击 $('#sub').attr("disabled","true"); //防止重复点击
                    $(form).ajaxSubmit(function(msg){
                        if( msg.status != 'success' ){  // 失败
                            layer.alert(msg.msg, {
                                icon: 5,
                                skin: 'layer-ext-moon'
                            });
                        }else{ // 成功
                            // layer.alert('提示信息',{ icon:1,time:3000},function(){});
                            layer.msg('添加成功！', {
                                icon: 1,
                                skin: 'layer-ext-moon'
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