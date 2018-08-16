@extends('public.public')
@section('title')反馈列表@endsection
@section('content')
{{--主题内容--}}
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 反馈管理 <span class="c-gray en">&gt;</span>
	反馈列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<table class="table table-border table-bordered table-bg datatables">
		<thead>
			<tr>
				<th scope="col" colspan="9">反馈列表</th>
			</tr>
			<tr class="text-c">
				<th width="">ID</th>
                <th width="">反馈图片</th>
                <th width="">反馈类型</th>
				<th width="">反馈人</th>
                <th width="">反馈的内容</th>
                <th width="">联系方式</th>
                <th width="">反馈时间</th>
                <th width="">处理结果</th>
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
            "targets": [-1,1,3,4,5],
            "orderable": false
        }],
        "ajax": {
            "url": "{{ url('admin/feedback/ajax_list') }}",
            "type": "post",   // ajax 的http请求类型
            'headers': { 'X-CSRF-TOKEN' : '{{ csrf_token() }}' },
        },
        //按列显示从服务器端过来的数据
        'columns':[
            {'data':'id',"defaultContent": ""},
            {'data':'',"defaultContent": ""},
            {'data':'cation',"defaultContent": "找不到"},
            {'data':'member.nickname',"defaultContent": "暂无"},
            {'data':'content',"defaultContent": ""},
            {'data':'contact',"defaultContent": ""},
            {'data':'created_at',"defaultContent": ""},
            {'data':'handling',"defaultContent": ""},
            {'data':'b',"defaultContent": ""},
        ],
        //自定义列  row= 每一行  data 数据  dataindex 数据所在的下标
        'createdRow':function ( row,data,dataIndex ) {
            var cnt = data.recordsFiltered;
			$('#coutent').html( cnt );
            $(row).addClass('text-c');//居中
            /////////////////根据话题id和动态id,判断这条反馈的所属
            $(row).find('td:eq(2)').html(data.cation == 1 ? '功能建议': ( data.cation == 2 ? 'bug提交' : '商家问题' ));//1:功能建议2bug提交3商家问题处理结果
            $(row).find('td:eq(-2)').html(data.handling == 1 ? '已处理': '未处理');//1=已处理,2=未处理
            if( data.inform_url != null ){
                for(var i=0;i<data.inform_url.length;i++){
                    data.inform_url = data.inform_url.replace('\'','ct2rs1')
                    data.inform_url = data.inform_url.replace('\"','ct2rs2')
                    data.inform_url = data.inform_url.replace('&','ct2rs3')
                }
                $(row).find('td:eq(1)').html("<input class='btn radius btn-secondary' onclick='showContent2(\""+data.inform_url+ "\"  )' type='button' value = '查看'>");//z状态
            }else{
                $(row).find('td:eq(1)').html("<input class='btn radius btn-secondary' onclick='showContent3(\""+null+ "\"  )' type='button' value = '查看'>");//z状态
            }

            $(row).find('td:eq(4)').html("<input class='btn radius btn-secondary' onclick='showContent(\""+data.content+ "\"  )' type='button' value = '查看'>");//z状态
            //操作
            $(row).find('td:eq(-1)').html('<a title="编辑" href="javascript:;" onclick="comment_edit(' +
                '\'反馈编辑\',\'/admin/feedback/'+data.id+'/edit\',\''+data.id+'\')" style="text-decoration:none"><i class="Hui-iconfont">&#xe6df;</i></a> ' +
				'<a title="删除" href="javascript:;" onclick="comment_del(this,\''+data.id+'\')" class="ml-5" style="text-decoration:none">' +
				'<i class="Hui-iconfont">&#xe6e2;</i></a>');
        }
    });
    function showContent2(content) {
        for(var i=0;i<content.length;i++){
            content = content.replace('ct2rs1','\'')
            content = content.replace('ct2rs2','\"')
            content = content.replace('ct2rs3','&')
        }
        layer.open({
            type: 1
            ,offset:  'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
            ,content: '<div style="padding: 20px 10px;" id="xlss"></div>'
            ,btn: '关闭'
            ,shade: [0.6, '#000000']
            ,shadeClose:true
            ,area: ['1000px', '600px']
            ,btnAlign: 'c' //按钮居中
            ,yes: function(){
                layer.closeAll();
            }
        });
        //先将img分割成数组
        var arr = new Array();
        var img = content;
        arr = img.split(',');
        for(var i=0;i<arr.length;i++)
        {
            arr[i] = arr[i].replace('[','');
            arr[i] = arr[i].replace(']','');
            var reg=/\\/g;
            arr[i] = arr[i].replace(reg,'');
            var reg=/"/g;
            arr[i] = arr[i].replace(reg,'');
            $(document).ready(function () {
                $('#xlss').append('<img style="width: 400px;" src="/'+ arr[i] +'">&nbsp;&nbsp;&nbsp;&nbsp;');
            })
        }


    }
    function showContent3( a ) {
            layer.open({
                type: 1
                ,offset:  'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                ,content: '<div style="padding: 20px 10px;" id="xlss">空空如也</div>'
                ,btn: '关闭'
                ,shade: [0.6, '#000000']
                ,shadeClose:true
                ,area: ['1000px', '600px']
                ,btnAlign: 'c' //按钮居中
                ,yes: function(){
                    layer.closeAll();
                }
            });
    }
    function showContent(content) {
        layer.open({
            type: 1
            ,offset:  'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
//            ,content: '<div style="padding: 20px 10px;">'+ content +'</div>'
            ,content: '<textarea cols="120" rows="20" style="margin: 5px;font-size: 18px;" placeholder="'+ content +'" class="layui-textarea"></textarea>'
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
	/*反馈-反馈-添加*/
    function comment_add(title,url,w,h){
        layer_show(title,url,'1200','800');
    }
	/*反馈-反馈-编辑*/
    function comment_edit(title,url,id,w,h){
        layer_show(title,url,'1200','800');
    }
	/*反馈-反馈-删除*/
    function comment_del(obj,id){
        layer.confirm('反馈删除须谨慎，确认要删除吗？',function(index){
            //此处请求后台程序，下方是成功后的前台处理……
            url = '/admin/feedback/'+ id;
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