@extends('public.public')
@section('title')优惠券列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 优惠券管理 <span class="c-gray en">&gt;</span>
	优惠券列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
            <a href="javascript:;" onclick="coupon_add('添加优惠券','{{ url('admin/coupon/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加优惠券</a></span>
        &nbsp;&nbsp;
        <a href="javascript:;" onclick="coupon_adds('批量添加优惠券','{{ url('admin/coupon/creates')  }}','800','500')" class="btn btn-success radius"><i class="Hui-iconfont">&#xe600;</i> 批量添加优惠券</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="16">优惠券列表</th>
			</tr>
			<tr class="text-c">
                <th width="">商家</th>
                <th width="">所属商家</th>
				<th width="">父级ID</th>
                <th width="">优惠券名称</th>
                <th width="">面额/折扣</th>
				<th width="">优惠券编号</th>
                <th width="">优惠券图片</th>
                <th width="">优惠券当前状态</th>
                <th width="">优惠券所在地</th>
                <th>优惠券所在经纬度</th>
                <th>优惠券效期</th>
                <th>所属用户id</th>
                <th>获取时间</th>
                <th>描述</th>
				<th>操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
                <th width="">商家</th>
                <th width="">所属商家</th>
                <th width="">父级ID</th>
                <th width="">优惠券名称</th>
                <th width="">面额/折扣</th>
                <th width="">优惠券编号</th>
                <th width="">优惠券图片</th>
                <th width="">优惠券当前状态</th>
                <th width="">优惠券所在地</th>
                <th>优惠券所在经纬度</th>
                <th>优惠券效期</th>
                <th>所属用户id</th>
				<td class="td-manage"><a style="text-decoration:none" onClick="coupon_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="coupon_edit('优惠券编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="coupon_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
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
        "lengthMenu":[[10,20,50],[10,20,50]],
        'paging':true,//分页
        'info':true,//分页辅助
        'searching':true,//既时搜索
        'ordering':true,//启用排序
        "order": [[ 0, "desc" ]],//排序规则  默认下标为1的显示倒序
        "stateSave": false,//使用状态.是否保持 默认true
        "processing": false,//是否显示数据在处理中的状态
        "serverSide": true,//是否开启服务端
        //设置不需要排序的字段
        "columnDefs": [{
            "targets": [-1,1,3,4,6,9,10,13,14],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/coupon/ajax_list') }}",  // 服务端uri地址，显示数据的uri
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'coupon_category.merchant_id',"defaultContent": "暂无"},
            {'data':'coupon_category.merchant_name',"defaultContent": ""},
            {'data':'cp_cate_id',"defaultContent": ""},
            {'data':'coupon_category.coupon_name',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'cp_number',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'',"defaultContent": "暂无"},
            {'data':'',"defaultContent": "暂无"},
            {'data':'member_id',"defaultContent": ""},
            {'data':'create_at',"defaultContent": ""},
            {'data':'note',"defaultContent": ""},
            {'data':'c',"defaultContent": ""},
        ],
        //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(6)').html(data.cp_cate_id == null ? '还没有图片' : '<img src="/'+ data.coupon_category.picture_url +'" style="width: 60px;height: 30px;">');
            $(row).find('td:eq(9)').html('纬度 : '+data.lat+'<br>经度 : '+data.lng);//经纬度
            $(row).find('td:eq(8)').html(data.adcode+':'+data.district);//所在区域
            $(row).find('td:eq(10)').html('开始时间 : '+data.start_at+'<br>结束时间 : '+data.end_at);//有效期
            $(row).find('td:eq(7)').html(data.status == 0 ? '未领取' : data.status == 1 ? '已领取但未使用' : data.status == 2 ? '已使用' : '过期或其他');//使用状态:0未领取,1已领取但未使用,2已使用,3过期
            $(row).find('td:eq(4)').html(data.coupon_category.coupon_type == 1 ? data.coupon_category.coupon_money + '元' : data.coupon_category.coupon_money + '折');//
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="coupon_edit(' +
                '\'优惠券编辑\',\'/admin/coupon/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
				'<a title="删除" href="javascript:;" onclick="coupon_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
				'<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
	/*优惠券-优惠券-添加*/
    function coupon_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
    /*优惠券-优惠券-批量添加*/
    function coupon_adds(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*优惠券-优惠券-编辑*/
    function coupon_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*优惠券-优惠券-删除*/
    function coupon_del(obj,id){
        layer.confirm('优惠券删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/coupon/'+ id;
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