@extends('public.public')
@section('title')编辑商家@endsection
@section('content')
    <article class="page-container">
            {{-- 隐藏域传递ID --}}
            <form action="{{ url('admin/merchant/'.$merchantInfo->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-merchant-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>昵称：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $merchantInfo->nickname }}" class="input-text"  placeholder="昵称" id="nickname" name="nickname">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属分类：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select class="select" name="ification_id" id="ification_id">
                            @foreach($ification as $v)
                                <option value="{{ $v->id }}" @if(($merchantInfo->ification_id)==($v->id)) selected @endif>{{ $v->cate_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>标签：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="{{$merchantInfo->labelling}}"  placeholder="如:咖啡/休闲餐厅" id="labelling" name="labelling">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所在地址：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" value="{{ $merchantInfo->address }}" class="input-text"  placeholder="详细地址" id="address" name="address">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">封面图：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="img_url" class="input-file">
                    </span>
                        @if($merchantInfo->img_url != null )<br> <img src="/{{ $merchantInfo->img_url  }}" style="height: 80px;width: 80px"><br>@endif
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">头像：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="avatar" class="input-file">
                    </span>
                        @if($merchantInfo->avatar != null )<br> <img src="/{{ $merchantInfo->avatar  }}" style="height: 80px;width: 80px"><br>@endif
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">店铺图：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="store_image" class="input-file">
                    </span>
                        @if($merchantInfo->store_image != null )<br> <img src="/{{ $merchantInfo->store_image  }}" style="height: 80px;width: 80px"><br>@endif
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>禁用状态：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="disabled_at" type="radio" value="1" id="status-1" @if($merchantInfo->disabled_at==null) checked @endif >
                            <label for="status-1">启用</label>
                        </div>
                        <div class="radio-box">
                            <input name="disabled_at" type="radio" value="0" id="status-2" @if($merchantInfo->disabled_at!=null) checked @endif >
                            <label for="status-2">禁用</label>
                        </div>
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

            $("#form-merchant-edit").validate({
                rules:{
                    age:{
                        range:[1,150]
                    },
                    height:{
                        range:[1,300]
                    },
                    weight:{
                        range:[1,1000]
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