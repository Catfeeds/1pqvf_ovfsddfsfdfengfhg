@extends('public.public')
@section('title')评论列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 评论管理 <span class="c-gray en">&gt;</span>
	评论列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="7">评论列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
                <th width="">评论所属</th>
                <th width="">回复的id</th>
				<th width="">评论人</th>
                <th width="">评论的内容</th>
                <th width="">评论的时间</th>
				<th width="">操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
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
            "targets": [2,3,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/comment/ajax_list') }}",        // 服务端uri地址，显示数据的uri
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'dy_id',"defaultContent": ""},
            {'data':'parent_id',"defaultContent": "找不到"},
            {'data':'member.nickname',"defaultContent": "暂无"},
            {'data':'',"defaultContent": ""},
            {'data':'created_at',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(1)').html(data.dy_id == null ? '话题':'动态');//勾选 dy=动态, to=话题
            $(row).find('td:eq(4)').html(data.dy_id == null ? data.topic.content : data.dynamic.content);//勾选 dy=动态, to=话题
            for(var i=0;i<data.content.length;i++){
                data.content = data.content.replace('\'','ct2rs1')
                data.content = data.content.replace('\"','ct2rs2')
                data.content = data.content.replace('&','ct2rs3')
            }
            $(row).find('td:eq(4)').html("<input class='btn radius btn-secondary' onclick='showContent(\""+data.content+ "\"  )' type='button' value = '查看'>");//z状态
            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="comment_edit(' +
                '\'评论编辑\',\'/admin/comment/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
				'<a title="删除" href="javascript:;" onclick="comment_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
				'<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });

    function showContent(content) {
        for(var i=0;i<content.length;i++){
            content = content.replace('ct2rs1','\'')
            content = content.replace('ct2rs2','\"')
            content = content.replace('ct2rs3','&')
        }
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
	/*评论-评论-添加*/
    function comment_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*评论-评论-编辑*/
    function comment_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*评论-评论-删除*/
    function comment_del(obj,id){
        layer.confirm('评论删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/comment/'+ id;
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