<?php
return [
	'yun'=>[//阿里云短信
		'access_key_id'        => 'LTAIfcowNvzdoZG7',//
		'access_key_secret'    => 'WrWUFEXKdoBUeRtGk4IgbdEGBzHBea',//
		'common_sign_name'     => '易息通达',//普通模板签名
		'template_code'        => [
			'register' => 'SMS_125020376',//模板code让一个变量来替换
		]
	],
	'api'=>[//云市场短信
		'api_app_key'           => 'LTAIfcowNvzdoZG7',//
		'api_app_secret'        => 'WrWUFEXKdoBUeRtGk4IgbdEGBzHBea',//
		'api_sign_name'         => '易息通达',//普通模板签名
		'api_template_code'     => [
			'register' => 'SMS_125020376',//模板code让一个变量来替换
		]
	],
	'note'=>[//短信发送API
		'access_key_id'        => 'LTAIfcowNvzdoZG7',//
		'access_key_secret'    => 'WrWUFEXKdoBUeRtGk4IgbdEGBzHBea',//
		'common_sign_name'     => '易息通达',//普通模板签名
		'template_code'        => [
			'register' => 'SMS_125020376',//模板code让一个变量来替换SMS_122294951
		],
		'enable_http_proxy'     => false,//是否开始代理
		'http_proxy_ip'         => '127.0.0.1',
		'http_proxy_port'       => '8888'
	]
];