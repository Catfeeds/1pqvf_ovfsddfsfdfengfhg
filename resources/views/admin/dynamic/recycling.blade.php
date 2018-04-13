@extends('public.public')
@section('title')动态列表@endsection
@section('content')
    {{--主题内容--}}
    <nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 动态管理 <span class="c-gray en">&gt;</span>
        回收站 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
    <div class="page-container">
        {{--<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">--}}
			{{--<a href="javascript:;" onclick="dynamic_add('添加动态','{{ url('admin/dynamic/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加动态</a></span>--}}
            {{--&nbsp;&nbsp;&nbsp;&nbsp;--}}
            {{--<a href="javascript:;" onclick="showRecyclingList('名次排行','/admin/dynamic/recycling',800,500)" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 回收站</a></span>--}}
        {{--</div>--}}
        <table class="table table-border table-bordered table-bg datatables">
            <thead>
            <tr>
                <th scope="col" colspan="7">动态列表</th>
            </tr>
            <tr class="text-c">
                <th width="">ID</th>
                <th width="">发布的用户</th>
                <th width="">点赞数</th>
                <th width="">图片</th>
                <th width="">发布的地址</th>
                <th width="">发布时间</th>
                <th width="">操作</th>
            </tr>
            </thead>
            <tbody>
            <tr class="text-c">
                <td>id</td>
                <td>用户名</td>
                <td>备注</td>
                <td>创建时间</td>
                <td>禁用状态</td>
                <td class="td-manage"><a style="text-decoration:none" onClick="dynamic_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
                    <a title="编辑" href="javascript:;" onclick="dynamic_edit('动态编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
                    <a title="删除" href="javascript:;" onclick="dynamic_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
            </tr>

            </tbody>
        </table>
    </div>

@endsection
@section('footer-script')
    <script src="{{ asset('admin') }}/lib/layer/2.4/layer.js"></script>
    <script src="{{ asset('admin') }}/lib/icheck/jquery.icheck.min.js"></script>
    <script type="text/javascript">
        /* database 插件  */
        $('.datatables').DataTable({
            //显示数量
            "lengthMenu":[[2,4,-1],[2,4,'全部']],
            'paging':true,//分页
            'info':true,//分页辅助
            'searching':true,//既时搜索
            'ordering':true,//启用排序
            "order": [[ 1, "desc" ]],//排序规则  默认下标为1的显示倒序
            "stateSave": false,//使用状态.是否保持 默认true
            "processing": false,//是否显示数据在处理中的状态
            "serverSide": false,//是否开启服务端
            //设置不需要排序的字段
            "columnDefs": [{
                "targets": [3,4,-1,1,5],
                "orderable": false
            }],
            "ajax": {
                "url": "{{ url('admin/dynamic/recycling_list') }}",
                "type": "post",
                'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
            },
            //按列显示从服务器端过来的数据
            'columns':[
                {'data':'id',"defaultContent": ""},
                {'data':'member.nickname',"defaultContent": ""},
                {'data':'nice_num',"defaultContent": "暂无"},
                {'data':'',"defaultContent": "暂无"},
                {'data':'addres',"defaultContent": ""},
                {'data':'created_at',"defaultContent": ""},
                {'data':'c',"defaultContent": ""},
            ],
            //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
            'createdRow':function ( row,data,dataIndex ) {
                var cnt = data.recordsFiltered;
                $('#coutent').html( cnt );
                $(row).addClass('text-c');//居中
                if(data.img_url == null){
                    $(row).find('td:eq(3)').html('暂无图片');
                }else{
                    //先将img分割成数组
                    var arr = new Array();
                    var img = data.img_url;
                    arr = img.split(',');
                    for(var i=0;i<arr.length;i++)
                    {
                        arr[i] = arr[i].replace('[','');
                        arr[i] = arr[i].replace(']','');
                        var reg=/\\/g;
                        arr[i] = arr[i].replace(reg,'');
                        var reg=/"/g;
                        arr[i] = arr[i].replace(reg,'');
                        if($(row).find('td:eq(3)').html() != null && $(row).find('td:eq(3)').html() != '暂无'){
                            $(row).find('td:eq(3)').append('<img style="width: 100px;" src="/'+ arr[i] +'">');
                        }else{
                            $(row).find('td:eq(3)').html('<img style="width: 100px;" src="/'+ arr[i] +'">');
                        }
                    }
                }

                $(row).find('td:eq(-1)').html(  '<a title="恢复" href="javascript:;" onclick="restore(\' '+data.id+' \')" class="btn radius btn-secondary" >恢复</a> ' );
            }
        });
        //恢复
        function restore(id) {
            layer.confirm('确认要恢复吗？',function(index){
                //此处请求后台程序，下方是成功后的前台处理……
                url = '/admin/dynamic/restore?id='+ id;
                data = {
                    '_token':'{{ csrf_token()  }}',
                };
                $.post(url,data,function (msg) {
                    if( msg.status != 'success' ){
                        layer.alert(msg.msg,{
                            icon:5,
                            skin:'layer-ext-moon'
                        })
                    }else{
                        location.reload();
                        layer.msg('发送成功',{icon:1,time:1000});
                    }
                });
            });
        }
    </script>
@endsection