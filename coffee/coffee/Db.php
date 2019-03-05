<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

use coffee\db\Connection;

class Db
{
    /**
     * 当前数据库连接对象
     * @var Connection
     */
    protected static $connection;

    /**
     * 数据库配置
     * @var array
     */
    protected static $config = [];

    /**
     * 配置
     * @access public
     * @param  mixed $config
     * @return void
     */
    public static function init($config = [])
    {
        self::$config = $config;

        if (empty($config['query'])) {
            self::$config['query'] = '\\coffee\\db\\Query';
        }
    }

    /**
     * 获取数据库配置
     * 
     * @access public
     * @param  string $config 配置名称
     * @return mixed
     */
    public static function getConfig($name = '')
    {
        if ('' === $name) {
            return self::$config;
        }

        return isset(self::$config[$name]) ? self::$config[$name] : null;
    }

    /**
     * 切换数据库连接
     * 
     * @access public
     * @param  mixed         $config 连接配置
     * @param  bool|string   $name 连接标识 true 强制重新连接
     * @param  string        $query 查询对象类名
     * @return mixed 返回查询对象实例
     * @throws Exception
     */
    public static function connect ($config = [], $name = false, $query = '') 
    { 
        // 解析配置参数
        $options = self::parseConfig($config ?: self::$config);

        $query = $query ?: $options['query'];

        // 创建数据库实例
        self::$connection = Connection::instance($options, $name);

        return new $query(self::$connection);
    }

    /**
     * 数据库连接参数解析
     * 
     * @access private
     * @param  mixed $config
     * @return array
     */
    private static function parseConfig($config)
    {
        if (is_string($config) && false === strpos($config, '/')) {
            // 读取指定的配置参数
            $config = isset(self::$config[$config]) ? self::$config[$config] : self::$config;
        }

        $result = is_string($config) ? self::parseDsnConfig($config) : $config;

        if (empty($result['query'])) {
            $result['query'] = self::$config['query'];
        }

        return $result;
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * 
     * @access private
     * @param  string $dsnStr
     * @return array
     */
    private static function parseDsnConfig($dsnStr)
    {
        // 解析url信息
        $info = parse_url($dsnStr);

        // 不符合规则，返回空数组
        if (!$info) { 
            return [];
        }

        // 获取配置信息
        $dsn = [
            'type'      => $info['scheme'],
            'username'  => isset($info['user']) ? $info['user'] : '',
            'password'  => isset($info['pass']) ? $info['pass'] : '',
            'hostname'  => isset($info['host']) ? $info['host'] : '',
            'hostport'  => isset($info['port']) ? $info['port'] : '',
            'database'  => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'   => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        // 判断是否定义了查询类
        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dns['params'] = [];
        }

        return $dsn;
    }

    /**
     * 回调函数
     * 
     * @access private
     * @param  string $method 方法名
     * @param  string|array $args 参数
     * @return array
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([static::connect(), $method], $args);
    }
}
