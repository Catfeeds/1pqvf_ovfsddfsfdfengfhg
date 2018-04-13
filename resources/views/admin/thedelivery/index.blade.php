@extends('public.public')
@section('title')积分商品发货列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 积分商品发货管理 <span class="c-gray en">&gt;</span>
	积分商品发货列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="8">积分商品发货列表</th>
			</tr>
			<tr class="text-c">
                <th width="">ID</th>
				<th width="">用户名</th>
                <th width="">兑换的商品</th>
                <th width="">所选物流</th>
                <th width="">快递单号</th>
                <th width="">兑换时间</th>
                <th width="">发货时间</th>
                <th width="">操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
				<td>id</td>
				<td>用户名</td>
				<td>备注</td>
				<td class="td-manage"><a style="text-decoration:none" onClick="theDelivery_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="theDelivery_edit('积分商品发货编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="theDelivery_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
			</tr>

		</tbody>
	</table>
</div>

@endsection
@section('footer-script')
<script src="{{ asset('admin') }}/lib/layer/2.4/layer.js"></script>
<script src="{{ asset('admin') }}/lib/icheck/jquery.icheck.min.js"></script>
<script type="text/javascript">
    function showContent(content) {
        for(var i=0;i<content.length;i++){
            content = content.replace('ct2rs1','\'')
            content = content.replace('ct2rs2','\"')
        }
        layer.open({
            type: 1
            ,offset:  'auto'
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
            "targets": [1,-2,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/theDelivery/ajax_list') }}",
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'member.nickname',"defaultContent": ""},
            {'data':'intmall.trade_name',"defaultContent": ""},
            {'data':'logistics',"defaultContent": ""},
            {'data':'Order',"defaultContent": ""},
            {'data':'created_at',"defaultContent": ""},
            {'data':'delivery_time',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中

            //操作
            //如果还没发货,则为查看地址,如果已经发货,则为查看地址,并多出一个取消发货
            if(  data.delivery_time == null ){ //发货时间=null 代表还没发货 不能取消
                $(row).find('td:eq(-1)').html( '<input class="btn radius btn-secondary" onclick="showadd(' +
                    '\'查看\',\'/admin/theDelivery/'+data.id+'/edit\',\''+data.id+'\')" type="button" value="查看">' );
            }else{
                $(row).find('td:eq(-1)').html( '<input class="btn radius btn-secondary" onclick="showadd(' +
                    '\'查看\',\'/admin/theDelivery/'+data.id+'/edit\',\''+data.id+'\')" type="button" value="查看">&nbsp;&nbsp;<input id="sed" class="btn radius btn-success " onclick="undo('+ data.member_id +')" ' +
                    'type="button" value="取消" >' );
            }
        }
    });

    //取消发货
    function undo(id){
        layer.confirm('提示:请先查看用户的收货地址是否正确,发货需谨慎',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/theDelivery/undo/'+ id;
            data = {
                '_token':'{{ csrf_token()  }}',
            };
            $.get(url,data,function (msg) {
                if( msg.status != 'success' ){
                    layer.alert(msg.msg,{
                        icon:5,
                        skin:'layer-ext-moon'
                    })
                }else{
                    layer.msg('成功',{icon:1,time:3000});
                    location.reload();
                }
            });
        });
    }
    //查看收货地址
    function showadd(title,url,w,h){
        layer_show(title,url,'1000','600');
    }

	/*积分商品发货-积分商品发货-添加*/
    function theDelivery_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*积分商品发货-积分商品发货-编辑*/
    function theDelivery_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*积分商品发货-积分商品发货-删除*/
    function theDelivery_del(obj,id){
        layer.confirm('积分商品发货删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/health/'+ id;
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