<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee\db;

use PDO;
use PDOException;

class Connection
{
    /**
     * 数据库实例
     * 
     * @var array
     */
    protected static $instance = [];

    /**
     * 数据库连接参数配置
     * 
     * @var array
     */
    protected $config = [
        // 数据库类型
        'type'            => '',
        // 服务器地址
        'hostname'        => '',
        // 数据库名
        'database'        => '',
        // 用户名
        'username'        => '',
        // 密码
        'password'        => '',
        // 端口
        'hostport'        => '',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
        // 数据库调试模式
        'debug'           => false,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy'          => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate'     => false,
        // 读写分离后 主服务器数量
        'master_num'      => 1,
        // 指定从服务器序号
        'slave_no'        => '',
        // 模型写入后自动读取主服务器
        'read_master'     => false,
        // 是否严格检查字段是否存在
        'fields_strict'   => true,
        // 数据集返回类型
        'resultset_type'  => '',
        // 自动写入时间戳字段
        'auto_timestamp'  => false,
        // 时间字段取出后的默认时间格式
        'datetime_format' => 'Y-m-d H:i:s',
        // 是否需要进行SQL性能分析
        'sql_explain'     => false,
        // Builder类
        'builder'         => '',
        // Query类
        'query'           => '\\think\\db\\Query',
        // 是否需要断线重连
        'break_reconnect' => false,
        // 断线标识字符串
        'break_match_str' => [],
    ];

    /**
     * PDO连接参数
     * 
     * @var array
     */
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * 当前SQL指令
     * 
     * @var string
     */
    protected $queryStr = '';

    /**
     * 当前SQL指令
     * 
     * @var int
     */
    protected $numRows = 0;

    /**
     * 错误信息
     * 
     * @var string
     */
    protected $error = '';

    /**
     * 数据库连接ID 支持多个连接
     * 
     * @var PDO[]
     */
    protected $links = [];

    /**
     * 当前连接ID
     * 
     * @var PDO
     */
    protected $linkID;
    protected $linkRead;
    protected $linkWrite;

    /**
     * 查询结果类型
     * 
     * @var PDO
     */
    protected $fetchType = PDO::FETCH_ASSOC;

    /**
     * 字段属性大小写
     * 
     * @var PDO
     */
    protected $attrCase = PDO::CASE_LOWER;

    /**
     * 构造函数，读取数据库配置信息
     * 
     * @access public
     * @param  array $config 数据库配置数组
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }


    }

    /**
     * 取得数据库连接类实例
     * 
     * @access public
     * @param  mixed         $config 连接配置
     * @param  bool|string   $name 连接标识 true 强制重新连接
     * @return Connection
     * @throws Exception
     */
    public static function instance($config = [], $name = false)
    {
        // 判断连接标识是否为false
        if ($name === false) {
            // 为false，则自动生成标识
            $name = md5(serialize($config));
        }

        // 判断是否开启了强制重新连接，或者是否不存在实例
        if (true === $name || !isset(self::$instance[$name])) {
            // 判断是否存在数据库连接类型
            if (empty($config['type'])) {
                echo '连接类型不存在';
                exit;
            }

            // 判断是否开启了强制重连
            if ($name === true) {
                $name = md5(serialize($config));
            }

            // $connent = new self();

            // 获取实例
            // self::$instance[$name] = $connent->connect($config);
            self::$instance[$name] = new self($config);
        }

        return self::$instance[$name];
    }

    /**
     * 连接数据库方法
     * 
     * @access public
     * @param  array         $config 连接参数
     * @param  integer       $linkNum 连接序号
     * @param  array|bool    $autoConnection 是否自动连接主数据库（用于分布式）
     * @return PDO
     * @throws Exception
     */
    public function connect(array $config = [], $linkNum = 0, $autoConnection = false)
    {
        // 判断是否存在该连接序号
        if (isset($this->links[$linkNum])) { 
            return $this->links[$linkNum];
        }

        // 判断是否存在连接配置信息
        if (!$config) {
            $config = $this->config;
        } else { 
            $config = array_merge($this->config, $config);
        }

        // 连接参数
        if (isset($config['params']) && is_array($config['params'])) {
            $params = $config['params'] + $this->params;
        } else { 
            $params = $this->params;
        }

        // 进行连接
        try { 
            if (empty($config['dsn'])) {
                $config['dsn'] = $this->parseDsn($config);
            }

            $this->links[$linkNum] = new PDO($config['dsn'], $config['username'], $config['password'], $params);

            return $this->links[$linkNum];
        } catch (\PDOException $e) { 
            // 判断是否存在自动重连接
            if ($autoConnection) {
                // 尝试重连
                return $this->connect($autoConnection, $linkNum);
            } else { 
                // 输出错误信息
                throw $e;
            }
        }
    }

    /**
     * 解析pdo连接的dsn信息
     * 
     * @access protected
     * @param  array $config 连接信息
     * @return string
     */
    protected function parseDsn($config)
    {
        // 获取连接
        if (!empty($config['socket'])) { 
            $dsn = 'mysql:unix_socket=' . $config['socket'];
        } else if (!empty($config['hostport'])) {
            $dsn = 'mysql:host=' . $config['hostname'] . ';port=' . $config['hostport'];
        } else {
            $dsn = 'mysql:host=' . $config['hostname'];
        }

        // 拼接数据库
        $dsn .= ';dbname=' . $config['database'];

        // 判断是否存在字符集设置
        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }

        return $dsn;
    }

    /**
     * 获取数据库的配置参数
     * @access public
     * @param  string $config 配置名称
     * @return mixed
     */
    public function getConfig($config = '')
    {
        return $config ? $this->config[$config] : $this->config;
    }

    /**
     * 设置数据库的配置参数
     * 
     * @access public
     * @param  string|array      $config 配置名称
     * @param  mixed             $value 配置值
     * @return void
     */
    public function setConfig($config, $value = '')
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        } else {
            $this->config[$config] = $value;
        }
    }
}
