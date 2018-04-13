@extends('public.public')
@section('title')编辑优惠券图片@endsection
@section('content')
    <article class="page-container">
            <form action="{{ url('admin/picture/'.$pictureInfo->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-picture-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属商家：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select class="select" name="merchant_id" id="merchant_id">
                            @foreach($merchantInfo as $v)
                                <option value="{{ $v->id  }}" @if($v->id==$pictureInfo->merchant_id) selected @endif>{{ $v->nickname  }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠形式：</label>
                    <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                        <div class="radio-box">
                            <input name="action" type="radio" value="2" id="status-3"  @if($pictureInfo->action==2) checked @endif>
                            <label for="status-3">折扣</label>
                        </div>
                        <div class="radio-box">
                            <input name="action" type="radio" value="1" id="status-4" @if($pictureInfo->action==1) checked @endif>
                            <label for="status-4">减免金额</label>
                        </div>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">优惠额度：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" value="{{ $pictureInfo->price  }}"  placeholder="如果是折扣,请直接填写折扣率(如9.5),金额则直接填写数字(单位元,如30)" id="price" name="price">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">优惠券图片：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="picture_url" class="input-file">
                    </span>
                        <br>
                        <img style="width: 100px;" src="/{{ $pictureInfo->picture_url  }}" alt="">
                        <input type="hidden" name="old_img" value="{{$pictureInfo->picture_url}}">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">抵扣券图片：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="deduction_url" class="input-file">
                    </span>
                        <br>
                        <img style="width: 100px;" src="/{{ $pictureInfo->deduction_url  }}" alt="">
                        <input type="hidden" name="old_img2" value="{{$pictureInfo->deduction_url}}">
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
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-picture-edit").validate({
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