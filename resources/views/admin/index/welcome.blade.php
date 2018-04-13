<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<!--[if lt IE 9]>
<script type="text/javascript" src="{{asset('admin')}}/lib/html5shiv.js"></script>
<script type="text/javascript" src="{{asset('admin')}}/lib/respond.min.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="{{asset('admin')}}/static/h-ui/css/H-ui.min.css" />
<link rel="stylesheet" type="text/css" href="{{asset('admin')}}/static/h-ui.admin/css/H-ui.admin.css" />
<link rel="stylesheet" type="text/css" href="{{asset('admin')}}/lib/Hui-iconfont/1.0.8/iconfont.css" />
<link rel="stylesheet" type="text/css" href="{{asset('admin')}}/static/h-ui.admin/skin/default/skin.css" id="skin" />
<link rel="stylesheet" type="text/css" href="{{asset('admin')}}/static/h-ui.admin/css/style.css" />
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>我的桌面</title>
</head>
<body>
<div class="page-container">
	<p class="f-20 text-success">欢迎来到跑券 <span class="f-14">v1.0</span>后台系统！</p>
	<table class="table table-border table-bordered table-bg mt-20">
		<thead>
			<tr>
				<th colspan="2" scope="col">服务器信息</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th width="30%">服务器计算机名</th>
				<td><span id="lbServerName"><?php echo $_SERVER['HTTP_HOST']; ?></span></td>
			</tr>
			<tr>
				<td>服务器IP地址</td>
				<td><?php  echo $_SERVER['REMOTE_ADDR'];?></td>
			</tr>
			<tr>
				<td>服务器域名</td>
				<td><?php echo $_SERVER['HTTP_HOST']; ?></td>
			</tr>
			<tr>
				<td>服务器端口 </td>
				<td><?php echo $_SERVER['SERVER_PORT'];?></td>
			</tr>

			<tr>
				<td>本文件所在文件夹 </td>
				<td><?php echo __FILE__;  ?></td>
			</tr>
			<tr>
				<td>服务器操作系统 </td>
				<td><?PHP echo PHP_OS; ?></td>
			</tr>

			<tr>
				<td>服务器脚本超时时间 </td>
				<td><?PHP echo get_cfg_var("max_execution_time")."秒 "; ?></td>
			</tr>

			<tr>
				<td>服务器当前时间 </td>
				<td><?php echo date('Y-m-d H:i:s',time()); ?></td>
			</tr>

			<tr>
				<td>当前程序占用内存 </td>
				<td><?php echo "占用: ".memory_get_usage()/1024/8;?>Kb</td>
			</tr>

			<tr>
				<td>当前Session数量 </td>
				<td>{{ count(Request::session())  }}</td>
			</tr>
			<tr>
				<td>当前SessionID </td>
				<td>{{ Request::cookie('laravel_session')  }}</td>
			</tr>
		</tbody>
	</table>
</div>
<footer class="footer mt-20">
	<div class="container">
		<p>  copy© 易息通达网络科技有限公司
			<img src="{{ asset('admin')  }}/images/logo.png" style="width: 35px;" alt=""></p>
	</div>
</footer>
<script type="text/javascript" src="{{asset('admin')}}/lib/jquery/1.9.1/jquery.min.js"></script> 
<script type="text/javascript" src="{{asset('admin')}}/static/h-ui/js/H-ui.min.js"></script> 
<!--此乃百度统计代码，请自行删除-->
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?080836300300be57b7f34f4b3e97d911";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
<!--/此乃百度统计代码，请自行删除-->
</body>
</html>