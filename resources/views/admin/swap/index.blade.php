@extends('public.public')
@section('title')互换列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 互换管理 <span class="c-gray en">&gt;</span>
	互换列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="swap_add('添加互换','{{ url('admin/swap/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加互换</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="6">互换列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">发布人</th>
				<th width="">所需积分数量</th>
				<th width="">发布的优惠券</th>
				<th>状态</th>
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
				<td class="td-manage"><a style="text-decoration:none" onClick="swap_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="swap_edit('互换编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="swap_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
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
            "targets": [2,3,1,5],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/swap/ajax_list') }}",
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'member.nickname',"defaultContent": ""},
            {'data':'integral',"defaultContent": "暂无"},
            {'data':'coupon.id',"defaultContent": ""},
            {'data':'status',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(-2)').html(data.status == 1 ? '已被兑换' : '还没人理会');//z状态
            $(row).find('td:eq(3)').html('优惠券id : '+data.coupon.id + '<br>所属商家id : ' + data.coupon.merchant_id + '<br>优惠价格 : ' + (data.coupon.action==1 ? data.coupon.price + '元' : data.coupon.price + '折'));//z状态
            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="swap_edit(' +
                '\'互换编辑\',\'/admin/swap/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
				'<a title="删除" href="javascript:;" onclick="swap_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
				'<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
	/*互换-互换-添加*/
    function swap_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*互换-互换-编辑*/
    function swap_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*互换-互换-删除*/
    function swap_del(obj,id){
        layer.confirm('互换删除须谨慎，确认要删除吗？',function(index){
            url = '/admin/swap/'+ id;
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