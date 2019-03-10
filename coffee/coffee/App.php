<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

use Exception;

/**
 * App 应用管理
 */
class App extends Container
{
    const VERSION = '1.0.0';

    /**
     * 当前模块路径
     * @var string
     */
    protected $modulePath;

    /**
     * 应用调试模式
     * @var bool
     */
    protected $appDebug = true;

    /**
     * 应用开始时间
     * @var float
     */
    protected $beginTime;

    /**
     * 应用内存初始占用
     * @var integer
     */
    protected $beginMem;

    /**
     * 应用类库命名空间
     * @var string
     */
    protected $namespace = 'app';

    /**
     * 应用类库后缀
     * @var bool
     */
    protected $suffix = false;

    /**
     * 严格路由检测
     * @var bool
     */
    protected $routeMust;

    /**
     * 应用类库目录
     * @var string
     */
    protected $appPath;

    /**
     * 框架目录
     * @var string
     */
    protected $coffeePath;

    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath;

    /**
     * 运行时目录
     * @var string
     */
    protected $runtimePath;

    /**
     * 配置目录
     * @var string
     */
    protected $configPath;

    /**
     * 路由目录
     * @var string
     */
    protected $routePath;

    /**
     * 配置后缀
     * @var string
     */
    protected $configExt;

    /**
     * 应用调度实例
     * @var Dispatch
     */
    protected $dispatch;

    /**
     * 绑定模块（控制器）
     * @var string
     */
    protected $bindModule;

    /**
     * 初始化
     * @var bool
     */
    protected $initialized = false;

    public function __construct($appPath = '')
    {
        $this->coffeePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
        $this->path($appPath);
    }

    /**
     * 绑定模块或者控制器
     * @access public
     * @param  string $bind
     * @return $this
     */
    public function bind($bind)
    {
        $this->bindModule = $bind;
        return $this;
    }

    /**
     * 设置应用类库目录
     * @access public
     * @param  string $path 路径
     * @return $this
     */
    public function path($path)
    {
        $this->appPath = $path ? realpath($path) . DIRECTORY_SEPARATOR : $this->getAppPath();

        return $this;
    }

    /**
     * 初始化应用
     * @access public
     * @return void
     */
    public function initialize()
    {
        // 防止多次初始化
        if ($this->initialized) {
            return;
        }

        // 已经初始化成功
        $this->initialized = true;
        // 开始时间
        $this->beginTime   = microtime(true);
        // 获取分配给PHP的内存
        $this->beginMem    = memory_get_usage();

        // 配置路径
        $this->rootPath    = dirname($this->appPath) . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
        $this->routePath   = $this->rootPath . 'route' . DIRECTORY_SEPARATOR;
        $this->configPath  = $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
        $this->configExt   = '.php';

        static::setInstance($this);

        $this->instance('app', $this);

        // 初始化配置
        $this->config->init();
    }

    /**
     * 执行应用程序
     * @access public
     * @return Response
     * @throws Exception
     */
    public function run()
    {
        // 初始化应用
        $this->initialize();

        // 进行路由解析
        $this->routeParsing();

        // 根据当前的路由模块，更新配置
        $this->config->updateConfig();

        if (!$this->config->get('app.app_debug')) {
            // ini_set('display_errors', 'Off');
        } elseif (PHP_SAPI != 'cli') {
            //重新申请一块比较大的buffer
            if (ob_get_level() > 0) {
                $output = ob_get_clean();
            }
            ob_start();
            if (!empty($output)) {
                echo $output;
            }
        }

        // 数据库配置初始化
        Db::init($this->config->get('database.'));

        // 访问数据
        $this->distributionRequest();
    }

    /**
     * 进行路由解析
     * 
     * @access public
     * @return Response
     * @throws Exception
     */
    public function routeParsing()
    {
        // 获取Request参数
        $routeParam = $this->request->getRoute();

        // 获取当前请求类型
        $method = $this->request->getMethod();

        // 初始化路由
        $route = $this->route->init($routeParam, $method);

        // 解析控制器
        $this->analysisRouteAction($route);
    }

    /**
     * 进行路由解析
     * 
     * @access public
     * @param string $route 路由规则
     * @return Response
     * @throws Exception
     */
    public function analysisRouteAction($route)
    { 
        // 初始化
        $module = $this->config->get('app.default_module');
        $controller = $this->config->get('app.default_controller');
        $action = $this->config->get('app.default_action');

        // 判断
        if (strpos($route, '/') === false) { 
            // 直接是方法
            $action = $route;
        } else { 
            // 解析数组
            $classs = explode('/', $route);

            // 判断数组长度
            if (count($classs) == 2) { 
                // 指定控制器和方法
                $controller = $classs[0];
                $action = $classs[1];
            } else if (count($classs) > 2) { 
                $module = $classs[0];
                $controller = $classs[1];
                $action = $classs[2];
            }
        }

        // 定义常量
        define('MODULE_NAME', $module);
        define('CONTROLLER_NAME', ucfirst($controller));
        define('ACTION_NAME', $action);
    }

    /**
     * 分发请求
     * 
     * @access public
     * @return void
     */
    private static function distributionRequest ()
    {
        $module = 'app\\' . MODULE_NAME . '\\' . 'controller' . '\\' . CONTROLLER_NAME;
        $action = ACTION_NAME;

        $obj = new $module();

        $obj->$action();
    }

    /**
     * 获取根目录
     * 
     * @access public
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * 获取应用类库目录
     * @access public
     * @return string
     */
    public function getAppPath()
    {
        if (is_null($this->appPath)) {
            $this->appPath = Loader::getRootPath() . 'application' . DIRECTORY_SEPARATOR;
        }

        return $this->appPath;
    }

    /**
     * 获取应用运行时目录
     * @access public
     * @return string
     */
    public function getRuntimePath()
    {
        return $this->runtimePath;
    }

    /**
     * 获取核心框架目录
     * @access public
     * @return string
     */
    public function getThinkPath()
    {
        return $this->thinkPath;
    }

    /**
     * 获取路由目录
     * @access public
     * @return string
     */
    public function getRoutePath()
    {
        return $this->routePath;
    }

    /**
     * 获取应用配置目录
     * @access public
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * 获取配置后缀
     * @access public
     * @return string
     */
    public function getConfigExt()
    {
        return $this->configExt;
    }

    /**
     * 获取应用类库命名空间
     * @access public
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * 设置应用类库命名空间
     * @access public
     * @param  string $namespace 命名空间名称
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * 是否启用类库后缀
     * @access public
     * @return bool
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * 获取应用开启时间
     * @access public
     * @return float
     */
    public function getBeginTime()
    {
        return $this->beginTime;
    }

    /**
     * 获取应用初始内存占用
     * @access public
     * @return integer
     */
    public function getBeginMem()
    {
        return $this->beginMem;
    }
}