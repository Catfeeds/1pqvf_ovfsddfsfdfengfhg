@extends('public.public')
@section('title')积分商城列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 积分商城管理 <span class="c-gray en">&gt;</span>
	积分商城列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="intmall_add('添加积分商品','{{ url('admin/intMall/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加积分商品</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="7">积分商城列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">商品名</th>
				<th width="">商品图片</th>
                <th width="">商品数量</th>
                <th width="">积分价格</th>
                <th width="">rmb价格</th>
                <th width="">操作</th>
            </tr>
		</thead>
		<tbody>
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
            "targets": [2,1,3,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/intmall/ajax_list') }}",
            "type": "post",
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'trade_name',"defaultContent": ""},
            {'data':'img_url',"defaultContent": "暂无"},
            {'data':'trade_num',"defaultContent": ""},
            {'data':'integral_price',"defaultContent": ""},
            {'data':'rmb_price',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(2)').html(data.img_url == null ? '暂无图片' : '<img src="/'+ data.img_url +'" style="width: 100px;height: 80px;">');
            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="intmall_edit(' +
                '\'积分商城编辑\',\'/admin/intMall/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
                '<a title="删除" href="javascript:;" onclick="intmall_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
                '<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
	/*积分商城-积分商城-添加*/
    function intmall_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*积分商城-积分商城-编辑*/
    function intmall_edit(title,url,id,w,h){
//        console.log(url);
        layer_show(title,url,'1200','800');
    }
	/*积分商城-积分商城-删除*/
    function intmall_del(obj,id){
        layer.confirm('积分商城删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/intMall/'+ id;
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