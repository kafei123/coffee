<?php
# 命名空间
namespace coffee;

if (!defined('ACCESS')) {
    header('location:../public/index.php');
}

class Application
{
    public static $coffeePath = '';

    public static function run ()
    {
        self::$coffeePath = __DIR__ . DIRECTORY_SEPARATOR;
        self::setChar();
        self::setSys();
        self::setDir();
        self::loadConfig();
        //self::autoLoad();
        self::analysisUrl();
        self::dispatch();
    }

    // 设置字符集
    private static function setChar ()
    {
        header('content-type:text/html;charset=utf-8');
    }

    // 设置系统错误
    private static function setSys ()
    {
        // 设置错误提示
        ini_set('display_errors', 'on');
        // 设置显示所有类型的错误，注意E_ALL没有引号
        ini_set('error_reporting', E_ALL);
    }

    // 设置目录常量
    private static function setDir ()
    {
        define('COFFEE_PATH', self::$coffeePath);
        echo COFFEE_PATH;
    }

    // 加载配置
    private static function loadConfig ()
    {
        $GLOBALS['config'] = include_once COFFEE_PATH . 'convention.php';
    }

    // 类的自动加载
    private static function loadCore ($name)
    {
        // 获取文件名
        $filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . $name . '.php';

        if (file_exists($filename))
        {
            include_once($filename);
        }
    }

    // 注册
    private static function autoLoad ()
    {
        spl_autoload_register('self::loadCore');
    }

    // 获取控制器
    private static function loadController ($name)
    {
        $filename = COFFEE_PATH . basename($name) . '.php';

        if (file_exists($filename))
        {
            include_once($filename);
        }
    }

    // 分析URL
    private static function analysisUrl ()
    {
        // 获取变量
        $module = isset($_REQUEST['m']) ? ucfirst(strtolower($_REQUEST['m'])) : 'Index';
        $controller = isset($_REQUEST['c']) ? ucfirst(strtolower($_REQUEST['c'])) : 'Index';
        $action = isset($_REQUEST['a']) ? $_REQUEST['a'] : 'index';

        // 定义常量
        define('MODULE', $module);
        define('CONTROLLER', $controller);
        define('ACTION', $action);

        var_dump($module);
        var_dump($module);
        var_dump($controller);
        var_dump($action);
    }

    // 分发请求
    private static function dispatch ()
    {
        $module = 'application\\' . MODULE . '\\' . CONTROLLER;
        $action = ACTION;

        $obj = new $module();

        $obj->$action();
    }
}
