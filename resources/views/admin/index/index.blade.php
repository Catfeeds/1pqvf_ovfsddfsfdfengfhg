@extends('public.public')
@section('title')跑券 @endsection
@section('content')

	{{--顶部导航栏--}}
	<header class="navbar-wrapper">
		<div class="navbar navbar-fixed-top">
			<div class="container-fluid cl"> <a class="logo navbar-logo f-l mr-10 hidden-xs" href="javascript:void(0)">跑券后台管理系统</a> <a class="logo navbar-logo-m f-l mr-10 visible-xs" href="/admin/index">paoquan</a>
				<span class="logo navbar-slogan f-l mr-10 hidden-xs">v0.1</span>
				<a aria-hidden="false" class="nav-toggle Hui-iconfont visible-xs" href="javascript:;">&#xe667;</a>
				<nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
					<ul class="cl">
						<li>{{ \Auth::guard('admin')->user()->note }}</li>
						<li class="dropDown dropDown_hover">
							<a href="#" class="dropDown_A"> {{ \Auth::guard('admin')->user()->username  }} <i class="Hui-iconfont">&#xe6d5;</i></a>
							<ul class="dropDown-menu menu radius box-shadow">
								<li><a href="{{ url('admin/loginout')  }}">退出</a></li>
							</ul>
						</li>
						<li id="Hui-skin" class="dropDown right dropDown_hover"> <a href="javascript:;" class="dropDown_A" title="换肤"><i class="Hui-iconfont" style="font-size:18px">&#xe62a;</i></a>
							<ul class="dropDown-menu menu radius box-shadow">
								<li><a href="javascript:;" data-val="default" title="默认（黑色）">默认（黑色）</a></li>
								<li><a href="javascript:;" data-val="blue" title="蓝色">蓝色</a></li>
								<li><a href="javascript:;" data-val="green" title="绿色">绿色</a></li>
								<li><a href="javascript:;" data-val="red" title="红色">红色</a></li>
								<li><a href="javascript:;" data-val="yellow" title="黄色">黄色</a></li>
								<li><a href="javascript:;" data-val="orange" title="橙色">橙色</a></li>
							</ul>
						</li>
					</ul>
				</nav>
			</div>
		</div>
	</header>
	{{--左侧菜单栏--}}
	<aside class="Hui-aside">
		<div class="menu_dropdown bk_2">
			<dl id="menu-member">
				<dt><i class="Hui-iconfont">&#xe60d;</i> 用户管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/admin')  }}" data-title="管理员" href="javascript:void(0)">管理员</a></li>
						<li><a data-href="{{ url ( 'admin/member' ) }}" data-title="用户列表" href="javascript:;">用户列表</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont dianpu">&#xe66a;</i> 商家管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/ification')  }}" data-title="商家分类" href="javascript:void(0)">商家分类</a></li>
						<li><a data-href="{{ url('admin/merchant')  }}" data-title="商家列表" href="javascript:void(0)">商家列表</a></li>
						<li><a data-href="{{ url('admin/appraise')  }}" data-title="商家评价列表" href="javascript:void(0)">商家评价列表</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont vip-card2">&#xe6b4;</i> 优惠券管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/coupon_category')  }}" data-title="优惠券分类" href="javascript:void(0)">优惠券分类</a></li>
						<li><a data-href="{{ url('admin/coupon')  }}" data-title="优惠券列表" href="javascript:void(0)">优惠券列表</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont vip-card2">&#xe6b4;</i> 商城管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/intMall')  }}" data-title="积分商城" href="javascript:void(0)">积分商城</a></li>
						<li><a data-href="{{ url('admin/actmall')  }}" data-title="活动商城" href="javascript:void(0)">活动商城</a></li>
						<li><a data-href="{{ url ( 'admin/swap' ) }}" data-title="互换管理" href="javascript:;">互换管理</a></li>
						<li><a data-href="{{ url('admin/theDelivery')  }}" data-title="发货管理" href="javascript:void(0)">发货管理</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont hot">&#xe6c1;</i> 活动管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/activity')  }}" data-title="活动分类" href="javascript:void(0)">活动分类</a></li>
						<li><a data-href="{{ url('admin/health')  }}" data-title="健康达人" href="javascript:void(0)">健康达人</a></li>
						<li><a data-href="{{ url('admin/perseverance')  }}" data-title="毅力使者" href="javascript:void(0)">毅力使者</a></li>
						<li><a data-href="{{ url('admin/takeflag')  }}" data-title="夺旗先锋" href="javascript:void(0)">夺旗先锋</a></li>
						<li><a data-href="{{ url('admin/rankings')  }}" data-title="排行榜" href="javascript:void(0)">排行榜</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont comment">&#xe622;</i> 博文管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/subject')  }}" data-title="话题分类列" href="javascript:void(0)">话题分类</a></li>
						<li><a data-href="{{ url('admin/topic')  }}" data-title="话题管理" href="javascript:void(0)">话题列表</a></li>
						<li><a data-href="{{ url ( 'admin/dynamic' ) }}" data-title="动态管理" href="javascript:;">动态列表</a></li>
						<li><a data-href="{{ url('admin/comment')  }}" data-title="评论列表" href="javascript:void(0)">评论列表</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont">&#xe62d;</i> 广告管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="#" data-title="app首页弹窗" href="javascript:;">app首页弹窗</a></li>
						<li><a data-href="#" data-title="发现首页轮播" href="javascript:;">发现首页轮播</a></li>
					</ul>
				</dd>
			</dl>
			<dl id="menu-admin">
				<dt><i class="Hui-iconfont huangguan">&#xe6d3;</i> 官方管理<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd>
					<ul>
						<li><a data-href="{{ url('admin/medal')  }}" data-title="奖章管理" href="javascript:void(0)">奖章管理</a></li>
						<li><a data-href="{{ url('admin/inform')  }}" data-title="消息通知" href="javascript:void(0)">消息通知</a></li>
						<li><a data-href="{{ url('admin/feedback')  }}" data-title="反馈管理" href="javascript:void(0)">反馈管理</a></li>
					</ul>
				</dd>
			</dl>
		</div>
	</aside>
	{{--左侧结束--}}

	<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a></div>
	<section class="Hui-article-box">
		<div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
			<div class="Hui-tabNav-wp">
				<ul id="min_title_list" class="acrossTab cl">
					<li class="active">
						<span title="我的桌面" data-href="{{ url('admin/welcome') }}">我的桌面</span>
						<em></em></li>
				</ul>
			</div>
			<div class="Hui-tabNav-more btn-group"><a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d4;</i></a><a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d7;</i></a></div>

		</div>
		<div id="iframe_box" class="Hui-article">
			<div class="show_iframe">
				<div style="display:none" class="loading"></div>
				<iframe scrolling="yes" frameborder="0" src="{{ url('admin/welcome') }}"></iframe>
			</div>
		</div>
	</section>

	<div class="contextMenu" id="Huiadminmenu">
		<ul>
			<li id="closethis">关闭当前 </li>
			<li id="closeall">关闭全部 </li>
		</ul>
	</div>

@endsection
@section('footer-script')
	<!--请在下方写此页面业务相关的脚本-->
	<script type="text/javascript">

        /*个人信息*/
        function myselfinfo(){
            layer.open({
                type: 1,
                area: ['300px','200px'],
                fix: false, //不固定
                maxmin: true,
                shade:0.4,
                title: '查看信息',
                content: '<div>管理员信息</div>'
            });
        }

        /*资讯-添加*/
        function article_add(title,url){
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }
        /*图片-添加*/
        function picture_add(title,url){
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }
        /*产品-添加*/
        function product_add(title,url){
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }
        /*用户-添加*/
        function member_add(title,url,w,h){
            layer_show(title,url,w,h);
        }
	</script>
@endsection
