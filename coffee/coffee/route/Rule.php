<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee\route;

class Rule
{
    /**
     * 配置信息
     * 
     * @var array
     */
    public $config = [];

    /**
     * 现有的规则数组
     * 
     * @var array
     */
    public $route = [];

    /**
     * 项目模块路径
     * 
     * @var string
     */
    public $rootPath = '';

    /**
     * 公共路由路径
     * 
     * @var string
     */
    public $routePath = '';

    /**
     * 项目路由路径
     * 
     * @var string
     */
    public $appPath = '';

    /**
     * 构造函数
     * 
     * @access public
     * @param array $config 配置参数
     * @return void
     */
    public function __construct (array $config = []) 
    { 
        $this->rootPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->routePath = $this->rootPath . 'route' . DIRECTORY_SEPARATOR;
        $this->appPath = $this->rootPath . 'application';
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 实例化接口
     * 
     * @access public
     * @param App $app App实例
     * @return void
     */
    public function __make (App $app, Config $config) 
    { 
        $route = new static($config->get('app.'));

        return $route;
    }

    /**
     * 初始化路由
     * 
     * @access public
     * @return void
     */
    public function init () 
    { 
        $this->loadRouteFiles();
    }

    /**
     * 加载路由文件
     * 
     * @access public
     * @return bool
     */
    public function loadRouteFiles ()
    { 
        if (!is_dir($this->routePath)) { 
            return;
        }

        $files = scandir($this->routePath);

        $route = [];

        // 遍历
        foreach ($files as $file) { 
            // 判断
            if ($file != '.' && $file != '..') { 
                // 获取路径
                $filename = $this->routePath . $file;
                // 判断是文件还是目录
                if (is_dir($filename)) { 
                    // 目录，递归
                } else { 
                    // 获取文件名
                    $name = substr($file, 0, strpos($file, '.'));
                    $route[$name] = include_once($filename);
                }
            }
        }

        $this->route = $route;

        return true;
    }

    /**
     * 添加路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public static function get ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        self::addRoute('GET', $route, $action);
    }

    /**
     * 解析路由规则
     * 
     * @access public
     * @param string $method 请求类型
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function analysisRoute ($method = 'GET', $route, $action)
    {
        // 判断
        if (!isset($this->route[$method])) { 
            $this->route[$method] = [];
        }

        // 添加路由规则进去
        $this->route[$method][$route] = $action;
    }
}
