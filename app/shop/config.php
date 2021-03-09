<?php
//配置文件
return [
    'view_replace_str' => [
		'__CSS__'      => '/static/oa/css',
		'__PUBLIC__'   => '/static/public',
		'__JS__'       => '/static/oa/js',
		'__IMAGES__'   => '/static/oa/images',
		'__HIGHCHARTS__'   => '/static/plugin/Highcharts',
		'__LAYER__'   => '/static/plugin/layer',
	],
	'app_trace' => true,
	
	//自定义
	// RBAC权限访问的基于文件的权限管理，易于理解但是不易于后期维护
	//角色权限
	'roles_auth' => array(
						1 => '超级管理员',
						2 => '中层领导',
						3 => '普通职员'
					),
	// 权限配置
	'role_auths_path' => array(
							1 => '*/*',
							2 => array('index/*','email/*', 'knowledge/*', 'document/*'),
							3 => array('index/*','knowledge/*', 'email/*'),
						),
];