<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

use Exception;

class Route
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
     * 路由参数数组
     * 
     * @var array
     */
    public $routeParam = [];

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
     * @param string $routeParam 路由参数
     * @param string $method 请求类型
     * @return void
     */
    public function init ($routeParam = '', $method = '') 
    { 
        $this->routeParam = $this->analysisRoutesParams($routeParam);
        $this->method = $method;

        // 加载路由文件
        $this->loadRouteFiles();

        // 解析路由
        return $this->analysisRoutes();
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
                    include_once($filename);
                }
            }
        }

        return true;
    }

    /**
     * 添加GET路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function get ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        $this->addRoute('GET', $route, $action);
    }

    /**
     * 添加POST路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function post ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        $this->addRoute('POST', $route, $action);
    }

    /**
     * 添加PUT路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function put ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        $this->addRoute('PUT', $route, $action);
    }

    /**
     * 添加DELETE路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function delete ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        $this->addRoute('DELETE', $route, $action);
    }

    /**
     * 添加PATCH路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function patch ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        $this->addRoute('PATCH', $route, $action);
    }

    /**
     * 添加ANY路由规则
     * 
     * @access public
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function any ($route = null, $action = null)
    { 
        if ($route === null || $action === null) { 
            return;
        }

        $this->addRoute('ANY', $route, $action);
    }

    /**
     * 添加路由规则
     * 
     * @access public
     * @param string $method 请求类型
     * @param string $route 路由
     * @param string $action 对应方法
     * @return bool
     */
    public function addRoute ($method = 'GET', $route, $action)
    { 
        // 判断
        if (!isset($this->route[$method])) { 
            $this->route[$method] = [];
        }

        $routes = [
            'route' => [],
            'params' => '',
            'action' => $action
        ];

        // 判断路由名称规则
        if (strpos($route, '/') === false) { 
            $routes['route'][0] = $route;
        } else { 
            $routeNames = explode('/', $route);

            // 获取最后一个元素，判断是不是参数
            $param = $routeNames[count($routeNames) - 1];

            if (strpos($param, ':') === 0) { 
                // 存在参数赋值
                $routes['params'] = substr($param, strpos($param, ':') + 1);
                // 移除最后一个元素
                array_pop($routeNames);
            }

            $routes['route'] = $routeNames;
        }

        // 添加路由规则进去
        $this->route[$method][] = $routes;
    }

    /**
     * 解析路由
     * 
     * @access public
     * @return bool
     */
    public function analysisRoutes ()
    { 
        // 获取当前请求类型下的路由规则
        if (!isset($this->route[$this->method])) { 
            throw new Exception("路由不存在");
        }

        $routes = $this->route[$this->method];

        // 判断是否定义了该路由
        foreach ($routes as $route) { 
            // 解析路由名称
            if ($this->checkRouteName($route)) { 
                // 路由匹配，跳出循环
                return $route['action'];
            }
        }

        throw new Exception("路由不存在");
    }

    /**
     * 获取路由名称
     * 
     * @access public
     * @param string $route 路由
     * @return bool
     */
    public function checkRouteName (array $route)
    { 
        // 计算路由名数量
        $routename_count = count($route['route']);

        // 参数数量
        $param_len = count($this->routeParam);

        if ($param_len < $routename_count) { 
            // 路由不匹配
            return false;
        }

        // 先截取一定数量的参数
        $params = array_slice($this->routeParam, 0, $routename_count);

        // 拼接名称
        $routeName = implode('/', $route['route']);
        $paranRouteName = implode('/', $params);

        if ($routeName != $paranRouteName) { 
            // 路由不匹配
            return false;
        }

        // 判断路由是否存在参数
        if (!empty($route['params'])) { 
            // 存在参数，判断路由参数是否匹配
            $params = array_slice($this->routeParam, $routename_count);

            // 判断个数
            if (count($params) == 2 && $params[0] == $route['params']) { 
                // 路由匹配
                return true;
            }
        }

        // 路由不匹配
        return false;
    }

    /**
     * 解析路由参数
     * 
     * @access public
     * @param string $routeParam 路由参数
     * @return bool
     */
    public function analysisRoutesParams ($routeParam = '')
    { 
        // 解析成数组
        $params = explode('/', $routeParam['s']);

        return $params;
    }
}
