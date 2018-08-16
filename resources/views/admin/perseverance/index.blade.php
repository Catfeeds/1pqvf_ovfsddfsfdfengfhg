@extends('public.public')
@section('title')毅力使者列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 毅力使者管理 <span class="c-gray en">&gt;</span>
	毅力使者列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="9">毅力使者列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">用户名</th>
                <th width="">打卡天数</th>
                <th width="">达成状态</th>
                <th width="">领奖时间</th>
                <th width="">发货状态</th>
                <th width="">联系方式</th>
                <th width="">收货地址</th>
                <th width="">操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
				<td>id</td>
				<td>用户名</td>
				<td>备注</td>
				<td class="td-manage"><a style="text-decoration:none" onClick="perseverance_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="perseverance_edit('毅力使者编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="perseverance_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
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
            "targets": [1,-2,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/perseverance/ajax_list') }}",
            "type": "post",
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'member.nickname',"defaultContent": ""},
            {'data':'punch_d',"defaultContent": ""},
            {'data':'status',"defaultContent": "暂无"},
            {'data':'award',"defaultContent": ""},
            {'data':'delivery',"defaultContent": ""},
            {'data':'member.phone',"defaultContent": "暂无"},
            {'data':'address',"defaultContent": "暂无"},
            {'data':'b',"defaultContent": ""},
        ],
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(3)').html(data.status2 == 1 ? '完成' : data.status2 == 2 ? '未完成' : '已领奖');// 使用状态 1:未使用,2:已使用,3:过期
            $(row).find('td:eq(4)').html(data.award == null ? '未领奖' :data.award);// 使用状态 1:未使用,2:已使用,3:过期
            $(row).find('td:eq(5)').html(data.delivery == null ? '未发货' :'已发货');// 使用状态 1:未使用,2:已使用,3:过期
            $(row).find('td:eq(-2)').html( '<input class="btn radius btn-secondary" onclick="showadd(' +
                '\'收货地址\',\'/admin/perseverance/showadd/'+data.member_id+'\')" type="button" value="查看">' );// 使用状态 1:未使用,2:已使用,3:过期
            //判断是否达成状态,是否已经发货,是否已经领奖
            //操作
            if(  data.delivery == null ){
                $(row).find('td:eq(-1)').html('<input id="sed" class="btn radius btn-secondary" onclick="send('+ data.member_id +')" ' +
                    'type="button" value="发货">'+
                    '&nbsp&nbsp<input id="sed" class="btn radius btn-disabled" onclick="undo('+ data.member_id +')" ' +
                    'type="button" value="取消" disabled >');
            }else{
                $(row).find('td:eq(-1)').html('<input id="sed" class="btn radius btn-disabled" onclick="send('+ data.member_id +')" ' +
                    'type="button" value="发货" disabled >'+
                    '&nbsp&nbsp<input id="sed" class="btn radius btn-secondary" onclick="undo('+ data.member_id +')" ' +
                    'type="button" value="取消"  >');
            }
        }
    });
    //是否发货
    function send(member_id){
        layer.confirm('提示:请先查看用户的收货地址是否正确,发货需谨慎',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/perseverance/send/'+ member_id;
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
    //取消发货
    function undo(member_id){
        layer.confirm('提示:请先查看用户的收货地址是否正确,发货需谨慎',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/perseverance/undo/'+ member_id;
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
	/*毅力使者-毅力使者-添加*/
    function perseverance_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*毅力使者-毅力使者-编辑*/
    function perseverance_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*毅力使者-毅力使者-删除*/
    function perseverance_del(obj,id){
        layer.confirm('毅力使者删除须谨慎，确认要删除吗？',function(index){
            url = '/admin/perseverance/'+ id;
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