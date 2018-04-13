@extends('public.public')
@section('title')话题分类列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 话题分类管理 <span class="c-gray en">&gt;</span>
	话题分类列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="subject_add('添加话题分类','{{ url('admin/subject/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加话题分类</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="5">话题分类列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">分类名称</th>
                <th width="">封面图片</th>
                <th width="">话题描述</th>
				<th width="">操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
				<td>id</td>
				<td>用户名</td>
				<td>备注</td>
				<td class="td-manage"><a style="text-decoration:none" onClick="subject_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="subject_edit('话题分类编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="subject_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
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
            "targets": [2,1,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/subject/ajax_list') }}",
            "type": "post",
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'cate_name',"defaultContent": ""},
            {'data':'img_url',"defaultContent": "暂无"},
            {'data':'cate_note',"defaultContent": "暂无"},
            {'data':'b',"defaultContent": ""},
        ],
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');
            $(row).find('td:eq(2)').html(data.img_url != null ? '<img style="width: 100px;" src="/'+ data.img_url +'">' : '暂无图片');
            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="subject_edit(' +
                '\'话题分类编辑\',\'/admin/subject/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
				'<a title="删除" href="javascript:;" onclick="subject_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
				'<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
	/*话题分类-话题分类-添加*/
    function subject_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*话题分类-话题分类-编辑*/
    function subject_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*话题分类-话题分类-删除*/
    function subject_del(obj,id){
        layer.confirm('话题分类删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/subject/'+ id;
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