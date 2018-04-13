<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<link rel="Bookmark" href="{{ asset('admin') }}/68895745141322071.png" >
<link rel="Shortcut Icon" href="{{ asset('admin') }}/68895745141322071.png" />
<!--[if lt IE 9]>
<script type="text/javascript" src="{{ asset('admin') }}/lib/html5shiv.js"></script>
<script type="text/javascript" src="{{ asset('admin') }}/lib/respond.min.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="{{ asset('admin') }}/static/h-ui/css/H-ui.min.css" />
<link rel="stylesheet" type="text/css" href="{{ asset('admin') }}/static/h-ui.admin/css/H-ui.admin.css" />
<link rel="stylesheet" type="text/css" href="{{ asset('admin') }}/lib/Hui-iconfont/1.0.8/iconfont.css" />
<link rel="stylesheet" type="text/css" href="{{ asset('admin') }}/static/h-ui.admin/skin/default/skin.css" id="skin" />
<link rel="stylesheet" type="text/css" href="{{ asset('admin') }}/static/h-ui.admin/css/style.css" />
<!--[if IE 6]>
<script type="text/javascript" src="{{ asset('admin') }}/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>@yield('title')</title>

</head>
<body>
@yield('content')

</body>
</html>

<script src="{{ asset('admin') }}/lib/jquery/1.9.1/jquery.min.js"></script>
<script src="{{ asset('admin') }}/lib/jquery.contextmenu/jquery.contextmenu.r2.js"></script>
<script src="{{ asset('admin') }}/static/h-ui/js/H-ui.js"></script>
<script src="{{ asset('admin') }}/static/h-ui.admin/js/H-ui.admin.js"></script>
<script type="text/javascript" src="{{ asset('admin')  }}/lib/laypage/1.2/laypage.js"></script>
{{--验证插件--}}
<script type="text/javascript" src="{{ asset('admin')  }}/lib/datatables/1.10.0/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="{{ asset('admin')  }}/lib/My97DatePicker/4.8/WdatePicker.js"></script>
@yield('footer-script')
