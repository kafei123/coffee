<?php

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------
    'app'        => [
        // 应用名称
        'app_name'               => '',
        // 应用地址
        'app_host'               => '',
        // 应用调试模式
        'app_debug'              => false,
        // 应用Trace
        'app_trace'              => false,
        // 应用模式状态
        'app_status'             => '',
        // 是否HTTPS
        'is_https'               => false,
        // 入口自动绑定模块
        'auto_bind_module'       => false,
        // 注册的根命名空间
        'root_namespace'         => [],
        // 默认输出类型
        'default_return_type'    => 'html',
        // 默认AJAX 数据返回格式,可选json xml ...
        'default_ajax_return'    => 'json',
        // 默认JSONP格式返回的处理方法
        'default_jsonp_handler'  => 'jsonpReturn',
        // 默认JSONP处理方法
        'var_jsonp_handler'      => 'callback',
        // 默认时区
        'default_timezone'       => 'Asia/Shanghai',
        // 是否开启多语言
        'lang_switch_on'         => false,
        // 默认验证器
        'default_validate'       => '',
        // 默认语言
        'default_lang'           => 'zh-cn',

        // +----------------------------------------------------------------------
        // | 模块设置
        // +----------------------------------------------------------------------

        // 自动搜索控制器
        'controller_auto_search' => false,
        // 操作方法前缀
        'use_action_prefix'      => false,
        // 操作方法后缀
        'action_suffix'          => '',
        // 默认的空控制器名
        'empty_controller'       => 'Error',
        // 默认的空模块名
        'empty_module'           => '',
        // 默认模块名
        'default_module'         => 'index',
        // 是否支持多模块
        'app_multi_module'       => true,
        // 禁止访问模块
        'deny_module_list'       => ['common'],
        // 默认控制器名
        'default_controller'     => 'Index',
        // 默认操作名
        'default_action'         => 'index',
        // 是否自动转换URL中的控制器和操作名
        'url_convert'            => true,
        // 默认的访问控制器层
        'url_controller_layer'   => 'controller',
        // 应用类库后缀
        'class_suffix'           => false,
        // 控制器类后缀
        'controller_suffix'      => false,
    ],

    // +----------------------------------------------------------------------
    // | 数据库设置
    // +----------------------------------------------------------------------

    'database'   => [
        // 数据库类型
        'type'            => 'mysql',
        // 数据库连接DSN配置
        'dsn'             => '',
        // 服务器地址
        'hostname'        => '127.0.0.1',
        // 数据库名
        'database'        => '',
        // 数据库用户名
        'username'        => 'root',
        // 数据库密码
        'password'        => '',
        // 数据库连接端口
        'hostport'        => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => ''
    ],

    //分页配置
    'paginate'   => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],
];
