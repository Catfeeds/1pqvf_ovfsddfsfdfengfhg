@extends('public.public')
@section('title')商家列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 商家管理 <span class="c-gray en">&gt;</span>
	商家列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="merchant_add('添加商家','{{ url('admin/merchant/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加商家</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="12">商家列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">昵称</th>
                <th width="">所属分类</th>
                <th width="">标签</th>
                <th width="">所在地址</th>
                <th width="">纬度</th>
                <th width="">封面图</th>
                <th width="">头像</th>
                <th width="">店铺图</th>
                <th width="">评价星级</th>
                <th width="">禁用状态</th>
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
        "lengthMenu":[[10,20,-1],[10,20,'全部']],
        "displayLength":10,//默认一页显示的数量
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
            "targets": [-1,1,3,4,5,6,7,8,10],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/merchant/ajax_list') }}",
            "type": "post",
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'nickname',"defaultContent": ""},
            {'data':'ification_id',"defaultContent": "暂无"},
            {'data':'labelling',"defaultContent": "标签"},
            {'data':'address',"defaultContent": ""},
            {'data':'latitude',"defaultContent": ""},
            {'data':'img_url',"defaultContent": ""},
            {'data':'avatar',"defaultContent": ""},
            {'data':'store_image',"defaultContent": ""},
            {'data':'appraise_n',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'c',"defaultContent": ""},
        ],
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(2)').html(data.cate_name);
            $(row).find('td:eq(-4)').html(data.store_image == null ? '店铺图片找不到了' : '<img src="/'+ data.store_image +'" style="width: 50px;height: 30px;">');
            $(row).find('td:eq(6)').html(data.img_url == null ? '图片找不到了' : '<img src="/'+ data.img_url +'" style="width: 50px;height: 30px;">');
            $(row).find('td:eq(7)').html(data.avatar == null ? '头像找不到了' : '<img src="/'+ data.avatar +'" style="width: 50px;height: 30px;">');
            $(row).find('td:eq(5)').html('纬度 : '+data.latitude.lat+'<br>经度 : '+data.latitude.lng);//经纬度
            $(row).find('td:eq(10)').html(data.deleted_at == null ? '启用' : '禁用<br/>'+data.deleted_at );//经纬度

            //操作
            $(row).find('td:eq(-1)').html(
                '<a title="编辑" href="javascript:;" onclick="merchant_edit(' + '\'商家编辑\',\'/admin/merchant/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
                '<a title="加入回收站" href="javascript:;" onclick="merchant_disable(this,\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe706;</i></a>'
                +
                '<a title="彻底删除（慎用）" href="javascript:;" onclick="merchant_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
                '<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
	/*商家-商家-添加*/
    function merchant_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*商家-商家-编辑*/
    function merchant_edit(title,url,id,w,h){
//        console.log(url);
        layer_show(title,url,'1200','800');
    }
	/*商家-商家-删除*/
    function merchant_del(obj,id){
        layer.confirm('删除后不可恢复，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/merchant/'+ id;
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
    /*商家-商家-禁用*/
    function merchant_disable(obj,id){
        layer.confirm('确认禁用吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/merchant_disable/'+'?id='+id;
            data = {
                '_token':'{{ csrf_token()  }}',
                'id':'id',
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