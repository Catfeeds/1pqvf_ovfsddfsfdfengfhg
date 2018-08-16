@extends('public.public')
@section('title')通知列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 通知管理 <span class="c-gray en">&gt;</span>
	通知列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="inform_add('添加通知','{{ url('admin/inform/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加通知</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="5">通知列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">标题</th>
                <th width="">内容</th>
                <th width="">发送时间</th>
                <th width="">操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
				<td>id</td>
				<td>通知名称</td>
				<td class="td-manage"><a style="text-decoration:none" onClick="inform_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="inform_edit('通知编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="inform_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
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
        "lengthMenu":[[10,20,-1],[10,20,'全部']],
        'paging':true,//分页
        'info':true,//分页辅助
        'searching':true,//既时搜索
        'ordering':true,//启用排序
        "order": [[ 0, "desc" ]],//排序规则  默认下标为1的显示倒序
        "stateSave": false,//使用状态.是否保持 默认true
        "processing": false,//是否显示数据在处理中的状态
        "serverSide": false,//是否开启服务端
        //设置不需要排序的字段
        "columnDefs": [{
            "targets": [1,2,3,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/inform/ajax_list') }}",
            "type": "post",
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'title',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'push_time',"defaultContent": ""},
            {'data':'c',"defaultContent": ""},
        ],
        //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            for(var i=0;i<data.content.length;i++){
                data.content = data.content.replace('\'','ct2rs1')
                data.content = data.content.replace('\"','ct2rs2')
                data.content = data.content.replace('&','ct2rs3')
            }
            $(row).find('td:eq(2)').html("<input class='btn radius btn-secondary' onclick='showContent( \""+data.content+"\")' type='button' value = '查看'>");//z状态
            //操作
            if( data.push_time == null ){
                $(row).find('td:eq(-1)').html(
                    '<a title="发送" href="javascript:;" onclick="inform(\' '+data.id+' \')" class="btn radius btn-secondary" >发送</a> ' +

                    '<a title="编辑" href="javascript:;" onclick="inform_edit(' +
                    '\'通知编辑\',\'/admin/inform/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
                    '<a title="删除" href="javascript:;" onclick="inform_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
                    '<i class="Hui-iconfont">&#xe6e2;</i></a>');
            }else{
                $(row).find('td:eq(-1)').html(
                    '<a title="取消" disabled="" href="javascript:;" onclick="inform2(\' '+data.id+' \')" class="btn btn-success-outline radius" >取消</a> ' +

                    '<a title="编辑" href="javascript:;" onclick="inform_edit(' +
                    '\'通知编辑\',\'/admin/inform/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
                    '<a title="删除" href="javascript:;" onclick="inform_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
                    '<i class="Hui-iconfont">&#xe6e2;</i></a>');
            }

        }
    });
    function showContent(content) {
        for(var i=0;i<content.length;i++){
            content = content.replace('ct2rs1','\'')
            content = content.replace('ct2rs2','\"')
            content = content.replace('ct2rs3','&')
        }
        content = content.replace('[','');
        content = content.replace(']','');
        var reg=/\"\,\"/g;
        content = content.replace(reg,'<br>');
        var reg=/\"/g;
        content = content.replace(reg,'');
        layer.open({
            type: 1
            ,offset:  'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
            ,content: '<div style="padding: 20px 10px;">'+ content +'</div>'
            ,btn: '关闭'
            ,shade: [0.6, '#000000']
            ,shadeClose:true
            ,area: ['800px', '400px']
            ,btnAlign: 'c' //按钮居中
            ,yes: function(){
                layer.closeAll();
            }
        });
    }
    //发送
    function inform(id) {
        layer.confirm('确认要发送吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/inform/inform?id='+ id;
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
    //取消发送
    function inform2(id) {
        layer.confirm('确认要取消吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/inform/inform2?id='+ id;
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
                    layer.msg('取消成功',{icon:1,time:1000});
                }
            });
        });
    }
	/*通知-通知-添加*/
    function inform_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*通知-通知-编辑*/
    function inform_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*通知-通知-删除*/
    function inform_del(obj,id){
        layer.confirm('通知删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/inform/'+ id;
            data = {
                '_token':'{{ csrf_token()  }}',
                '_method':'delete',
            };
            $.post(url,data,function (msg) {
                if( msg.status != 'success' ){
                    layer.alert(msg.msg,{
                        icon:5,
                        skin:'layer-ext-moon'
                    })
                }else{
                    location.reload();
                    $(obj).parents('tr').remove();
                    layer.msg('删除成功',{icon:1,time:1000});
                }
            });
        });
    }

</script>
@endsection