@extends('public.public')
@section('title')编辑商品@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/intMall/'.$intmallInfo->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-intmall-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>商品名称：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $intmallInfo->trade_name }}" class="input-text"  placeholder="商品名称" id="trade_name" name="trade_name">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">商品图片：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="img_url" class="input-file">
                    </span>
                        <br>
                        <img style="width: 100px;" src="/{{ $intmallInfo->img_url  }}" alt="">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>商品数量：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $intmallInfo->trade_num }}" class="input-text"  placeholder="商品数量" id="trade_num" name="trade_num">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>积分价格：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $intmallInfo->integral_price }}" class="input-text"  placeholder="积分价格" id="integral_price" name="integral_price">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>人民币价格：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $intmallInfo->rmb_price }}" class="input-text"  placeholder="人民币价格" id="rmb_price" name="rmb_price">
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
    {{--<script src="{{ asset('admin') }}/lib/jquery.form.js"></script>--}}
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

            $("#form-intmall-edit").validate({
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