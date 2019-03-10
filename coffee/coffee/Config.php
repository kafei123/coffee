<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

class Config
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 配置前缀
     * @var string
     */
    protected $prefix = 'app';

    /**
     * 配置文件目录
     * @var string
     */
    protected $path;

    /**
     * 配置文件后缀
     * @var string
     */
    protected $ext;

    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath;

    /**
     * 构造方法
     * 
     * @access public
     * @param string $path 配置路径
     * @param string $ext 文件后缀
     * @return void
     */
    public function __construct($path = '', $ext = '.php', $rootPath = '')
    {
        $this->path     = $path;
        $this->ext      = $ext;
        $this->rootPath = $rootPath;
    }

    /**
     * 实例化
     * 
     * @access public
     * @param App $app 类
     * @return Config
     */
    public static function __make(App $app)
    {
        $path = $app->getConfigPath();
        $ext  = $app->getConfigExt();
        $rootPath  = $app->getRootPath();
        return new static($path, $ext, $rootPath);
    }

    /**
     * 获取配置信息
     * 
     * @access public
     * @return void
     */
    public function init ()
    {
        // 先加载配置文件
        $this->config = include $this->rootPath . '/coffee/convention.php';

        // 获取公共配置目录下的配置信息
        $this->getConfigInfo($this->rootPath);
    }

    /**
     * 获取配置信息
     * 
     * @access public
     * @param string $configPath 配置目录路径
     * @return bool
     */
    public function getConfigInfo($configPath = '')
    {
        if ($configPath === '') {
            $configPath = $this->rootPath;
        }

        // 初始化配置目录下的配置信息数组
        $configPathInfo = [];

        // 判断是否存在该目录
        if (is_dir($configPath)) {
            // 打开目录
            $configdirs = opendir($configPath);

            if ($configdirs) {
                // 遍历文件夹
                while (($file = readdir($configdirs)) !== false) {
                    // 转换成utf-8格式
                    $filepath = iconv('GBK', 'utf-8', $configPath . $file);

                    if (!is_dir($filepath)) {
                        // 去除后缀，获取文件名
                        $filename = substr($file, 0, strpos($file, '.'));
                        // 将数据返回数组中
                        $configPathInfo[$filename] = include_once($filepath);
                    }
                }
            }

            // 合并两个数组
            if (!empty($configPathInfo)) {
                // 循环遍历合并
                foreach ($configPathInfo as $key => $configs) {
                    if (isset($this->config[$key])) {
                        $this->config[$key] = array_merge($this->config[$key], $configs);
                    } else {
                        $this->config[$key] = $configs;
                    }
                }
            }
        }
    }

    /**
     * 根据模块更新数据文件
     * 
     * @access public
     * @return void
     */
    public function updateConfig()
    {
        if (defined('MODULE_NAME')) {
            // 获取指定模块下的路径文件
            $configPath = $this->rootPath . 'application' . DIRECTORY_SEPARATOR . MODULE_NAME . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

            // 更新配置信息
            $this->getConfigInfo($configPath);
        }
    }

    

    /**
     * 获取一级配置
     * @access public
     * @param  string    $name 一级配置名
     * @return array
     */
    public function pull($name)
    {
        $name = strtolower($name);

        return isset($this->config[$name]) ? $this->config[$name] : [];
    }

    /**
     * 获取配置信息
     * 
     * @access public
     * @param string $name 参数名（支持多级配置 .号分割）
     * @param string $default 默认值
     * @return string|array
     */
    public function get ($name = null, $default = null)
    {
        // 判断是否存在键名，并且是否存在.符号
        if ($name && strpos($name, '.') === false) { 
            // 如果存在，并且不存在.符号，则默认获取 prefix 下的元素
            $name = $this->prefix . '.' . $name;
        }

        // 不存在，返回全部
        if (empty($name)) { 
            return $this->config;
        }

        // 判断.符号是否是最后一个
        if (substr($name, -1) == '.') { 
            // 如果是，则获取一级配置
            return $this->pull(substr($name, 0, -1));
        }

        // 获取指定一级配置下的指定的二级配置信息
        $names = explode('.', $name);

        // 获取二级配置key
        $key = $names[1];
        
        // 一级配置名字小写
        $name = strtolower($names[0]);

        if (isset($this->config[$name])) { 
            // 判断是否存在二级配置
            if (isset($this->config[$name][$key])) { 
                // 存在，则返回
                return $this->config[$name][$key];
            } else { 
                // 不存在，返回默认值
                return $default;
            }
        }

        // 返回全部配置
        return $this->config;
    }
}
