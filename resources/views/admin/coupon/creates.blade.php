@extends('public.public')
@section('title')批量批量添加优惠券@endsection
@section('content')
    <article class="page-container">
        <form action="{{ url('admin/coupon/stores')  }}" method="post"  class="form form-horizontal" id="form-coupon-add">
            {{ csrf_field() }}

            <div class="row cl">
            <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>导入方式：</label>
            <div class="formControls col-xs-8 col-sm-9 skin-minimal">
            <div class="radio-box">
            <input name="type3" type="radio" value="1" id="status-5"  checked >
            <label for="status-5">手工</label>
            </div>
            <div class="radio-box">
            <input name="type3" type="radio" value="0" id="status-6" >
            <label for="status-6">自动</label>
            </div>
            </div>
            </div>
            {{--自动--}}
            <div class="row cl" id="ex" style="display: none;">
            <label class="form-label col-xs-4 col-sm-3">导入excel：</label>
                <div class="formControls col-xs-8 col-sm-9" >
                    <span class="btn-upload form-group">
                      <input class="input-text upload-url radius" type="text" name="uploadfile-1" id="uploadfile-1" readonly>
                                            <a href="javascript:void();" class="btn btn-secondary radius">浏览文件</a>
                      <input type="file" multiple name="excel" class="input-file">
                    </span>
                </div>
            </div>
            <div class="row cl" id="ex2" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属商家：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    {{--<input type="text" class="input-text"  placeholder="所属商家" id="merchant_id2" name="merchant_id2">--}}
                    <select class="select" name="merchant_id2" id="merchant_id2">
                        @foreach($merchantInfo as $v)
                            <option value="{{ $v->id  }}">{{ $v->nickname  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row cl" id="ex3" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券图片：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="picture_id2" id="picture_id2">
                    </select>
                </div>
            </div>
            <div class="row cl" id="ex4" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>生成地区：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="请填写详细的商家地址" id="address3" name="address3">
                </div>
            </div>
            <div class="row cl" id="ex8" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>生成区域：</label>
                <div class="formControls col-xs-8 col-sm-9" >
                    <select class="select" name="address4" id="address4">
                        <option value="罗湖区">罗湖区</option>
                        <option value="福田区">福田区</option>
                        <option value="南山区">南山区</option>
                        <option value="宝安区">宝安区</option>
                        <option value="龙岗区">龙岗区</option>
                        <option value="盐田区">盐田区</option>
                    </select>
                </div>
            </div>
            <div class="row cl" id="ex5" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>类型：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="type2" type="radio" value="1" id="status-5" checked >
                        <label for="status-1">无期限</label>
                    </div>
                    <div class="radio-box">
                        <input name="type2" type="radio" value="0" id="status-6" >
                        <label for="status-2">有时效</label>
                    </div>
                </div>
            </div>
            <div class="row cl" id="hidden_t2" style="display: none" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>活动时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="datetime-local" class="input-text"  placeholder="开始时间" id="start_at2" name="start_at2" >
                    <input type="datetime-local" class="input-text"  placeholder="结束时间" id="end_at2" name="end_at2" style="margin-top: 5px;">
                </div>
            </div>
            <div class="row cl" id="ex6" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠形式：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="action2" type="radio" value="2" id="status-7"  checked>
                        <label for="status-3">折扣</label>
                    </div>
                    <div class="radio-box">
                        <input name="action2" type="radio" value="1" id="status-8" >
                        <label for="status-4">减免金额</label>
                    </div>
                </div>
            </div>
            <div class="row cl" id="ex7" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠价格：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="如果是折扣,请直接填写折扣率(如9.5),金额则直接填写数字(单位元,如30)" id="price2" name="price2">
                </div>
            </div>
            <div class="row cl" id="ex7" style="display: none;">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>描述：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="如:满100可用" id="note2" name="note2">
                </div>
            </div>
    {{--自动结束--}}

            <div class="row cl" name="xz">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>所属商家：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    {{--<input type="text" class="input-text"  placeholder="所属商家" id="merchant_id" name="merchant_id">--}}
                    <select class="select" name="merchant_id" id="merchant_id">
                        @foreach($merchantInfo as $v)
                            <option value="{{ $v->id  }}">{{ $v->nickname  }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row cl" name="xz">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠券图片：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <select class="select" name="picture_id" id="picture_id">
                    </select>
                </div>
            </div>
            <div class="row cl"  name="xz">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>生成类型：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="cr_type" type="radio" value="1" id="status-8" checked >
                        <label for="status-1">按商家</label>
                    </div>
                    <div class="radio-box">
                        <input name="cr_type" type="radio" value="0" id="status-9" >
                        <label for="status-2">按区域</label>
                    </div>
                </div>
            </div>
            <div class="row cl"  name="xz" >
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
            <div class="row cl" name="xz">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>生成数量：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="生成数量一次避免过大,最好不超过300张.生成后编号为自动生成" id="number" name="number">
                </div>
            </div>
            <div class="row cl" name="xz">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>类型：</label>
                <div class="formControls col-xs-8 col-sm-9 skin-minimal">
                    <div class="radio-box">
                        <input name="type" type="radio" value="1" id="status-1" checked >
                        <label for="status-1">无期限</label>
                    </div>
                    <div class="radio-box">
                        <input name="type" type="radio" value="0" id="status-2" >
                        <label for="status-2">有时效</label>
                    </div>
                </div>
            </div>
            <div class="row cl" id="hidden_t" style="display: none" name="xz" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>活动时间：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="datetime-local" class="input-text"  placeholder="开始时间" id="start_at" name="start_at" >
                    <input type="datetime-local" class="input-text"  placeholder="结束时间" id="end_at" name="end_at" style="margin-top: 5px;">
                </div>
            </div>

            <div class="row cl" name="xz">
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
            <div class="row cl" name="xz" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>优惠价格：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="如果是折扣,请直接填写折扣率(如9.5),金额则直接填写数字(单位元,如30)" id="price" name="price">
                </div>
            </div>
            <div class="row cl" name="xz" >
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>描述：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <input type="text" class="input-text"  placeholder="如:满100可用" id="note" name="note">
                </div>
            </div>
            <div class="row cl">
                <label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>使用说明：</label>
                <div class="formControls col-xs-8 col-sm-9">
                    <div id="ed"></div>
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
            var ue = UE.getEditor('ed');

            //根据选择的商家,选择该商家旗下的优惠券
            $(document).ready(function () {
                //商家的id
                var id = $("select option:checked").val();
                        @foreach($pictureInfo as $v)
                var id_ = '{{$v->merchant_id}}';
                if(id_ == id){
                    $("#picture_id").append("<option value='{{ $v->id  }}''>{{ $v->price  }}{{ $v->action==1 ? '元' : '折' }}</option>");
                    $("#picture_id2").append("<option value='{{ $v->id  }}''>{{ $v->price  }}{{ $v->action==1 ? '元' : '折' }}</option>");
                }
                @endforeach
            })
            $("input[name='cr_type']").change(function () {
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
                var id = $("#merchant_id").val();
                console.log(id)
                        @foreach($pictureInfo as $v)
                var id_ = '{{$v->merchant_id}}';
                if(id_ == id){
                    $("#picture_id").append("<option value='{{ $v->id  }}''>{{ $v->price  }}{{ $v->action==1 ? '元' : '折' }}</option>");
                }
                @endforeach
            })
            $('#merchant_id2').change(function () {
                //改变的时候,先清除原先的
                $("#picture_id2").empty();
                var id = $("#merchant_id2").val();
                @foreach($pictureInfo as $v)
                var id_ = '{{$v->merchant_id}}';
                if(id_ == id){
                    $("#picture_id2").append("<option value='{{ $v->id  }}''>{{ $v->price  }}{{ $v->action==1 ? '元' : '折' }}</option>");
                }
                @endforeach
            })
//构造符合datetime-local格式的当前日期
//            //类型选择
            $("input[name='type3']").click(function () {
                if( $(this).val() == 0  ){//id="xz"
                 //选择自动,隐藏其他所有div除了自身和button
                    $('div[name="xz"]').hide();//hidden_t
                    $('div[id="ex"]').show();//hidden_t
                    $('div[id="ex2"]').show();//hidden_t
                    $('div[id="ex3"]').show();//hidden_t
                    $('div[id="ex4"]').show();//hidden_t
                    $('div[id="ex5"]').show();//hidden_t
                    $('div[id="ex6"]').show();//hidden_t
                    $('div[id="ex7"]').show();//hidden_t
                    $('div[id="ex8"]').show();//hidden_t
                }else{
                    //还原
                    $('div[name="xz"]').show();//hidden_t
                    $('div[id="ex"]').hide();//hidden_t
                    $('div[id="ex2"]').hide();//hidden_t
                    $('div[id="ex3"]').hide();//hidden_t
                    $('div[id="ex4"]').hide();//hidden_t
                    $('div[id="ex5"]').hide();//hidden_t
                    $('div[id="ex6"]').hide();//hidden_t
                    $('div[id="ex7"]').hide();//hidden_t
                    $('div[id="ex8"]').hide();//hidden_t
                    $('div[id="hidden_t2"]').hide();//hidden_t
                }
            });
            $("input[name='type2']").click(function () {
                if( $(this).val() == 1  ){
                    $('#hidden_t2').hide();
                    //清除时间
                    $("input[name='start_at2']").val(null);
                    $("input[name='end_at2']").val(null);
                }else{
                    //如果选择了有期限
                    $('#hidden_t2').show();
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
            // 单选按钮外观效果  --<
            $('.skin-minimal input').iCheck({
                checkboxClass: 'icheckbox-blue',
                radioClass: 'iradio-blue',
                increaseArea: '20%'
            });

            $("#form-coupon-add").validate({
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
                            // layer.alert('提示信息',{ icon:1,time:3000},function(){});
                            layer.msg('批量批量添加成功！', {
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