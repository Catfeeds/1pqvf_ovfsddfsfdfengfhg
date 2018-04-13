@extends('public.public')
@section('title')编辑活动@endsection
@section('content')
    <article class="page-container">
            <form action="{{ url('admin/activity/'.$activityInfo->id ) }}" method="post" enctype="multipart/form-data"  class="form form-horizontal" id="form-activity-edit">
                {{ csrf_field() }}
                {{ method_field('put') }}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>活动简介：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $activityInfo->note }}" class="input-text"  placeholder="活动名称/简介" id="note" name="note">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>活动标题：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $activityInfo->title }}" class="input-text"  placeholder="活动标题" id="title" name="title">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">活动封面：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="img_url" class="input-file">
                    </span><br>
                        <img style="width: 100px;" src="/{{ $activityInfo->img_url  }}" alt="">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3">顶部图：</label>
                    <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="top_img_url" class="input-file">
                    </span><br>
                        <img style="width: 100px;" src="/{{ $activityInfo->top_img_url  }}" alt="">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>活动说明：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <div id="ed"></div>
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖品：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <select class="select" name="actMall_id" id="actMall_id">
                            @foreach($actmallInfo as $v)
                                <option value="{{ $v->id }} " num='{{ $v->act_num  }}'  @if($v->id==$activityInfo->actMall_id) selected @endif >{{ $v->note  }}</option>
                            @endforeach
                        </select>
                        <input type="hidden"  value="{{ $activityInfo->actMall_id }}" class="input-text"  placeholder="旧的奖品id" id="old_actmall_id" name="old_actmall_id">
                    </div>
                </div>
                {{--当选择了该奖品后,显示该库存,保存到kucun的val中,进行比对--}}
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖品库存：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text" class="input-text" readonly  placeholder="选择的奖品剩余的库存" id="kucun" name="kucun" >
                    </div>
                </div>
                <div class="row cl" >
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>活动时间(不修改则不要选)：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="datetime-local" class="input-text"  placeholder="开始时间" id="start_at" name="start_at" >
                        <input type="datetime-local" class="input-text"  placeholder="结束时间" id="end_at" name="end_at" style="margin-top: 5px;">
                    </div>
                </div>
                <div class="row cl">
                    <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>奖品数量：</label>
                    <div class="formControls col-xs-8 col-sm-9">
                        <input type="text"  value="{{ $activityInfo->actMall_num }}" class="input-text"  placeholder="奖品数量" id="act_num" name="actMall_num">
                        <input type="hidden"  value="{{ $activityInfo->actMall_num }}" class="input-text"  placeholder="旧奖品数量" id="old_act_num" name="old_actMall_num">
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
    {{--富文本--}}
    <script type="text/javascript" charset="utf-8" src="{{ asset('admin')  }}/lib/ueditor/1.4.3/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="{{ asset('admin')  }}/lib/ueditor/1.4.3/ueditor.all.min.js"> </script>
    <script type="text/javascript" charset="utf-8" src="{{ asset('admin')  }}/lib/ueditor/1.4.3/lang/zh-cn/zh-cn.js"></script>
    <!--请在下方写此页面业务相关的脚本-->
    <script>
        $(function(){
            //回显content的内容到富文本编辑器中
            var ue = UE.getEditor('ed');
            ue.ready(function() {//编辑器初始化完成再赋值
                var a = '';
                ue.setContent(a);
                var proinfo = '{!! $activityInfo->content !!}';
                var arr = new Array();
                proinfo = proinfo.replace('[','');
                proinfo = proinfo.replace(']','');
                var reg=/\"\,\"/g;
                proinfo = proinfo.replace(reg,'<br>');
                var reg=/\"/g;
                proinfo = proinfo.replace(reg,'');

                ue.setContent(proinfo);
            })

            $(document).ready(function () {
                var num = $("select option:checked").attr("num");
                $('#kucun').val(num);
            })
            $('#actMall_id').change(function () {
                var num = $("select option:checked").attr("num");
                $('#kucun').val(num);
            })
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-activity-edit").validate({
                rules:{
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