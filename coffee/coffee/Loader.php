<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

class Loader
{
    /**
     * 类名映射信息
     * @var array
     */
    protected static $classMap = [];

    /**
     * 类库别名
     * @var array
     */
    protected static $classAlias = [];

    /**
     * 类库别名
     * @var array
     */
    private static $prefixLengthsPsr4 = [];
    private static $prefixDirsPsr4    = [];
    private static $fallbackDirsPsr4  = [];

    /**
     * 获取应用根目录
     * 
     * @access public
     * @return string
     */
    public static function getRootPath()
    {
        if ('cli' == PHP_SAPI) {
            $scriptName = realpath($_SERVER['argv'][0]);
        } else {
            $scriptName = $_SERVER['SCRIPT_FILENAME'];
        }

        $path = realpath(dirname($scriptName));

        if (!is_file($path . DIRECTORY_SEPARATOR . 'coffee')) {
            $path = dirname($path);
        }

        return $path . DIRECTORY_SEPARATOR;
    }

    /**
     * 注册自动加载机制
     * 
     * @access public
     * @return void
     */
    public static function register($autoload = '')
    {
        // 注册系统自动加载
        spl_autoload_register($autoload ?: 'coffee\\Loader::autoload', true, true);

        // 获取系统根目录
        $rootPath = self::getRootPath();

        // 注册命名空间定义
        self::addNamespace([
            'coffee'  => __DIR__,
            'app'  => dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'application',
        ]);
    }

    /**
     * 自动加载
     * 
     * @access public
     * @return bool|class
     */
    public static function autoload($class)
    {
        // 判断是否存在类别名，存在直接返回
        if (isset(self::$classAlias[$class])) {
            return class_alias(self::$classAlias[$class], $class);
        }

        // 不存在，则寻找文件
        if ($file = self::findFile($class)) {

            // Win环境严格区分大小写
            if (strpos(PHP_OS, 'WIN') !== false && pathinfo($file, PATHINFO_FILENAME) != pathinfo(realpath($file), PATHINFO_FILENAME)) {
                return false;
            }

            __include_file($file);
        }
    }

    /**
     * 查找文件
     * 
     * @access private
     * @param  string $class
     * @return string|false
     */
    private static function findFile($class)
    {
        if (!empty(self::$classMap[$class])) {
            // 类库映射
            return self::$classMap[$class];
        }

        // 查找文件，根据 PSR-4 规范来查找文件，拼接文件后缀
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';

        // 获取文件名首字母
        $first = $class[0];

        // 判断是否存在 prefixLengthsPsr4 数组中
        if (isset(self::$prefixLengthsPsr4[$first])) { 
            // 存在，循环 prefixLengthsPsr4 数组
            foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {
                // 判断是否是命名空间开头
                if (0 === strpos($class, $prefix)) {
                    // 如果是，循环遍历 prefixDirsPsr4 数组
                    foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
                        // 判断文件是否存在
                        if (is_file($file = $dir . DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $length))) {
                            return $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-4 fallback dirs
        foreach (self::$fallbackDirsPsr4 as $dir) {
            if (is_file($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
                return $file;
            }
        }

        // 查找 PSR-0
        if (false !== $pos = strrpos($class, '\\')) {
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
            . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . '.php';
        }
    }

    /**
     * 注册命名空间
     * 
     * @access public
     * @return String
     */
    public static function addNamespace($namespace, $path = '')
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $paths) {
                self::addPsr4($prefix . '\\', rtrim($paths, DIRECTORY_SEPARATOR), true);
            }
        } else {
            self::addPsr4($namespace . '\\', rtrim($path, DIRECTORY_SEPARATOR), true);
        }
    }

    /**
     * 添加Psr4空间
     * 
     * @access public
     * @return String
     */
    private static function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!isset(self::$prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }

            self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            self::$prefixDirsPsr4[$prefix]                = (array) $paths;
        } elseif ($prepend) {
            // Prepend directories for an already registered namespace.
            self::$prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                self::$prefixDirsPsr4[$prefix]
            );
        } else {
            // Append directories for an already registered namespace.
            self::$prefixDirsPsr4[$prefix] = array_merge(
                self::$prefixDirsPsr4[$prefix],
                (array) $paths
            );
        }
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * 
     * @access public
     * @param  string  $name 字符串
     * @param  integer $type 转换类型
     * @param  bool    $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

/**
 * 作用范围隔离
 *
 * @param $file
 * @return mixed
 */
function __include_file($file)
{
    return include $file;
}
