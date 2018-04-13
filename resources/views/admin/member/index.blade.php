@extends('public.public')
@section('title')用户列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 用户管理 <span class="c-gray en">&gt;</span>
	用户列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="cl pd-5 bg-1 bk-gray mt-20"> <span class="l">
			<a href="javascript:;" onclick="member_add('添加用户','{{ url('admin/member/create')  }}','800','500')" class="btn btn-primary radius"><i class="Hui-iconfont">&#xe600;</i> 添加用户</a></span>
	</div>
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="16">用户列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
				<th width="">手机号</th>
				<th width="">昵称</th>
                <th width="">头像</th>
                <th width="">性别</th>
                <th width="">年龄</th>
                <th width="">积分</th>
                <th width="">二维码</th>
                <th width="">关注</th>
                <th width="">粉丝</th>
                <th width="">关注的商家</th>
                <th width="">持有优惠券</th>
                <th width="">已购的商品</th>
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
            "targets": [2,1,3,7,8,9,10,11,12,13,-1],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/member/ajax_list') }}",        // 服务端uri地址，显示数据的uri
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'phone',"defaultContent": ""},
            {'data':'nickname',"defaultContent": "暂无"},
            {'data':'avatar',"defaultContent": ""},
            {'data':'sex',"defaultContent": ""},
            {'data':'age',"defaultContent": ""},
            {'data':'integral',"defaultContent": ""},
            {'data':'qr_code',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'attention_id',"defaultContent": ""},
            {'data':'coupon_id',"defaultContent": ""},
            {'data':'tesco',"defaultContent": ""},
            {'data':'disabled_at',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            $(row).find('td:eq(-2)').html(data.disabled_at < 1 ? '启用' : '禁用');//z状态
            $(row).find('td:eq(3)').html(data.avatar == null ? '还没有头像' : '<img src="/'+ data.avatar +'" style="width: 100px;height: 80px;">');//z状态asset('storage/file.txt');
            $(row).find('td:eq(8)').html( '<input class="btn radius btn-secondary" onclick="showfriends_nicknam( \''+ data.friends_nickname +'\' )" type="button" value="查看">' );// 使用状态 1:未使用,2:已使用,3:过期
            $(row).find('td:eq(9)').html( '<input class="btn radius btn-secondary" onclick="showfans_nickname( \''+ data.fans_nickname +'\' )" type="button" value="查看">' );// 使用状态 1:未使用,2:已使用,3:过期
            $(row).find('td:eq(4)').html(data.sex == 1 ? '女' : data.sex == 2 ? '男':'保密');//z状态
            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="member_edit(' +
                '\'用户编辑\',\'/admin/member/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
                '<a title="删除" href="javascript:;" onclick="member_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
                '<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
    function showfriends_nicknam( friends_nickname ) {
        layer.open({
            type: 1
            ,offset:  'auto'
            ,content: '<div style="margin: 5px;" class="">'+friends_nickname+'</div>'
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
    function showfans_nickname( fans_nickname ) {
        layer.open({
            type: 1
            ,offset:  'auto'
            ,content: '<div style="margin: 5px;" class="">'+fans_nickname+'</div>'
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
	/*用户-用户-添加*/
    function member_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*用户-用户-编辑*/
    function member_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*用户-用户-删除*/
    function member_del(obj,id){
        layer.confirm('用户删除须谨慎，确认要删除吗？',function(index){
            url = '/admin/member/'+ id;
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