@extends('public.public')
@section('title')优惠券分类列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 优惠券分类管理 <span class="c-gray en">&gt;</span>
	优惠券分类列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="picture_add('新增优惠券类别','{{ url('admin/coupon_category/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i>
                添加优惠券分类</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="7">优惠券分类列表</th>
			</tr>
			<tr class="text-c">
                <th width="">商家ID</th>
                <th width="">商家名称</th>
				<th width="">优惠券ID</th>
                <th width="">优惠券名称</th>
                <th width="">优惠券图片</th>
                <th width="">抵扣券图片</th>
                <th width="">操作</th>
			</tr>
		</thead>
		<tbody>
			<tr class="text-c">
				<td>商家ID</td>
				<td>分类名称</td>
				<td class="td-manage"><a style="text-decoration:none" onClick="picture_stop(this,'10001')" href="javascript:;" title="停用"><i class="Hui-iconfont">&#xe631;</i></a>
					<a title="编辑" href="javascript:;" onclick="picture_edit('优惠券图片分类编辑','admin-add.html','1','800','500')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a>
					<a title="删除" href="javascript:;" onclick="picture_del(this,'1')" class="ml-5" style="text-decoration:none"><i class="Hui-iconfont">&#xe6e2;</i></a></td>
			</tr>
		</tbody>
	</table>
</div>

@endsection
@section('footer-script')
<script src="{{ asset('admin') }}/lib/layer/2.4/layer.js"></script>
<script src="{{ asset('admin') }}/lib/icheck/jquery.icheck.min.js"></script>
<script type="text/javascript">
    $('.datatables').DataTable({
        //显示数量
        "lengthMenu":[[5,10,-1],[5,10,'全部']],
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
            "targets": [1,3,4,5,6],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/coupon_category/ajax_list') }}",
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'merchant.id',"defaultContent": ""},
            {'data':'merchant.nickname',"defaultContent": ""},
            {'data':'id',"defaultContent": ""},
            {'data':'coupon_name',"defaultContent": ""},
            {'data':'picture_url',"defaultContent": ""},
            {'data':'deduction_url',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(2)').html(data.id);
            $(row).find('td:eq(-2)').html(data.deduction_url == null ? '还没有图片' : '<img src="/'+ data.deduction_url +'" style="width: 100px;height: 80px;">');
            $(row).find('td:eq(3)').html();
            $(row).find('td:eq(4)').html(data.picture_url == null ? '还没有图片' : '<img src="/'+ data.picture_url +'" style="width: 100px;height: 80px;">');

            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="picture_edit(' +
                '\'优惠券别分类编辑\',\'/admin/coupon_category/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
				'<a title="删除" href="javascript:;" onclick="picture_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
				'<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
	/*优惠券图片分类-优惠券图片分类-添加*/
    function picture_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*优惠券图片分类-优惠券图片分类-编辑*/
    function picture_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*优惠券图片分类-优惠券图片分类-删除*/
    function picture_del(obj,id){
        layer.confirm('重要！您确认要加入回收站吗？',function(index){
            url = '/admin/coupon_category/'+ id;
            data = {
                '_token':'{{ csrf_token()  }}',
                '_method':'delete',
            };
            $.post(url,data,function (msg) {
                if( msg.status != "success" ){
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