<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee\db;

use coffee\Db;
use coffee\db\QueryWhere;
use coffee\db\QueryRelation;

class Query
{
    /**
     * 当前数据库连接对象
     * 
     * @var Connection
     */
    protected $connection;

    /**
     * 当前模型对象
     * 
     * @var Model
     */
    protected $model;

    /**
     * 当前数据表名称（不含前缀）
     * 
     * @var string
     */
    public $table = '';

    /**
     * 当前数据表别名
     * 
     * @var string
     */
    public $alias = '';

    /**
     * 当前数据表主键
     * 
     * @var string|array
     */
    public $pk;

    /**
     * 当前数据表前缀
     * 
     * @var string
     */
    public $prefix = '';

    /**
     * 当前查询参数
     * 
     * @var array
     */
    protected $options = [
        'field'         => '',
        'where'         => [],
        'order'         => '',
        'limit'         => '',
        'group'         => '',
        'having'        => '',
        'join'          => '',
        'union'         => '',
        'distinct'      => '',
    ];

    /**
     * 查询开关
     * 
     * @var array
     */
    protected $switchs = [
        'lock'          => false,
        'fetchSql'      => false,
        'cache'         => false,
    ];

    /**
     * 查询语句
     * 
     * @var string
     */
    protected $where = '';

    /**
     * 查询条件
     * 
     * @var array
     */
    protected $expressionsConvert = ['eq'=>'=','neq'=>'<>','gt'=>'>','lt'=>'<','elt'=>'<=','egt'=>'>=','like'=>'LIKE','not like'=>'NOT LIKE ','not between'=>'NOT BETWEEN ','between'=>'BETWEEN','in'=>'IN','not in'=>'NOT IN'];

    /**
     * 构造函数
     * 
     * @access public
     */
    public function __construct(Connection $connection = null)
    { 
        // 如果connection为空，则重新实例化
        if (is_null($connection)) {
            $this->connection = Db::connect();
        } else {
            $this->connection = $connection;
        }

        $this->queryWhere = QueryWhere::getInstance();

        // 获取数据表前缀
        $this->prefix = $this->connection->getConfig('prefix');
    }

    /**
     * 设置表名
     * 
     * @access public
     * @param  string $table 表名字
     * @return void
     */
    public function table(string $table = '')
    {
        if (!empty($table) && is_string($table)) { 
            $this->table = $table;
        }

        return $this;
    }

    /**
     * 设置表别名
     * 
     * @access public
     * @param  string $alias 表别名
     * @return void
     */
    public function alias(string $alias = '')
    {
        if (!empty($alias) && is_string($alias)) { 
            $this->alias = $alias;
        }

        return $this;
    }

    /**
     * 设置锁状态
     * 
     * @access public
     * @param  bool $lock 是否加锁
     * @return void
     */
    public function lock($lock = false)
    {
        if ($lock === true) { 
            $this->switchs['lock'] = true;
        }

        return $this;
    }

    /**
     * 设置是否开启缓存
     * 
     * @access public
     * @param  bool $cache 是否开启缓存
     * @return void
     */
    public function cache($cache = false)
    {
        if ($cache === true) { 
            $this->switchs['cache'] = true;
        }

        return $this;
    }

    /**
     * 设置是否只输出sql语句
     * 
     * @access public
     * @param  bool $cache 是否开启缓存
     * @return void
     */
    public function fetchSql($fetchSql = false)
    {
        if ($fetchSql === true) { 
            $this->switchs['fetchSql'] = true;
        }

        return $this;
    }

    /**
     * 设置field条件
     * 
     * @access public
     * @param  string|array     $fields 要筛选的字段名
     * @return void
     */
    public function field($fields = '')
    {
        if (is_string($fields)) { 
            $this->options['field'] = $fields;
        } else if (is_array($fields)) { 
            $this->options['field'] = implode(',', $fields);
        }

        return $this;
    }

    /**
     * 设置order条件
     * 
     * @access public
     * @param  string|array     $orders 排序条件集
     * @return void
     */
    public function order($orders = '')
    {
        if (is_string($orders)) { 
            $this->options['order'] = $orders;
        } else if (is_array($orders)) { 
            $ordersString = '';
            // 循环
            foreach ($orders as $field => $op) { 
                if ($op == 'desc' || $op == 'asc') { 
                    $ordersString .= ' `' . $field . '` ' . $op . ', ';
                }
            }
            $this->options['order'] = $ordersString;
        }

        return $this;
    }

    /**
     * 设置where条件
     * 
     * @access public
     * @param  string|array     $field 字段名
     * @param  string           $op 查询条件
     * @param  string           $condition 查询条件
     * @return Query
     */
    public function where($field, $op = null, $condition = null)
    {
        /**
         * where('id', 1);
         * where('id', '=', 1);
         * where('id', 'eq', 1);
         * where([
         *  'id' => 1,
         *  'name' => 'thinkphp',
         *  'email' => ['in', '1,2,3']
         * ])
         */

        $where = $this->queryWhere->parseWhereExp($field, $op, $condition);

        if (empty($where)) {
            return $this;
        }

        $this->options['where']['AND'] = isset($this->options['where']['AND']) ? array_merge($this->options['where']['AND'], $where) : $where;

        return $this;
    }

    /**
     * 设置whereOr条件
     * 
     * @access public
     * @param  string|array     $field 字段名
     * @param  string           $op 查询条件
     * @param  string           $condition 查询条件
     * @return Query
     */
    public function whereOr($field, $op = null, $condition = null)
    {
        $param = func_get_args();

        $where = $this->queryWhere->parseWhereExp($field, $op, $condition);

        if (empty($where)) {
            return $this;
        }

        if (!isset($this->options['where']['OR'])) { 
            $this->options['where']['OR'] = [];
        }

        $this->options['where']['OR'][] = $where;

        return $this;
    }

    /**
     * 一对一关联
     * 
     * @access public
     * @param  array        $config 关联参数
     * @param  bool         $is_join 是否使用join，默认使用联查
     * @return Query
     */
    public function join($config = [], $is_join = false)
    {
        if (empty($config)) { 
            return $this;
        }

        $configs['config'] = $config;
        $configs['is_join'] = $is_join;

        return QueryRelation::join($this, 'joinOne', $configs);
    }

    /**
     * 单条查询
     * 
     * @access public
     * @return void
     */
    public function find()
    {
        // 判断是否存在limit条件
        if (empty($this->options['limit'])) { 
            // 为空，则赋值
            $this->options['limit'] = '0, 1';
        } else {
            // 不为空，指定查询某页的前几条
            $limit = trim($this->options['limit']);

            $limits = explode(',', $limit);

            $limits[1] = 1;

            if (!is_numeric($limits[0])) { 
                $limits[0] = 0;
            }

            $this->options['limit'] = implode(', ', $limits);
        }

        return $this->generateSelectQuerySql();
    }

    /**
     * 多条查询
     * 
     * @access public
     * @return void
     */
    public function select()
    {
        return $this->generateSelectQuerySql();
    }

    /**
     * 统计查询
     * 
     * @access public
     * @return void
     */
    public function count($field = '*')
    {
        if ($field != '*') {
            $field = '`' . $field . '`';
        }

        $this->options['field'] = 'COUNT(' . $field . ')';

        return $this->generateSelectQuerySql();
    }

    /**
     * 最大值查询
     * 
     * @access public
     * @return void
     */
    public function max($field = '*')
    {
        if ($field != '*') {
            $field = '`' . $field . '`';
        }

        $this->options['field'] = 'MAX(' . $field . ')';

        return $this->generateSelectQuerySql();
    }

    /**
     * 最小值查询
     * 
     * @access public
     * @return void
     */
    public function min($field = '*')
    {
        if ($field != '*') {
            $field = '`' . $field . '`';
        }

        $this->options['field'] = 'MIN(' . $field . ')';

        return $this->generateSelectQuerySql();
    }

    /**
     * 平均分查询
     * 
     * @access public
     * @return void
     */
    public function avg($field = '*')
    {
        if ($field != '*') {
            $field = '`' . $field . '`';
        }

        $this->options['field'] = 'AVG(' . $field . ')';

        return $this->generateSelectQuerySql();
    }

    /**
     * 总和查询
     * 
     * @access public
     * @return void
     */
    public function sum($field = '*')
    {
        if ($field != '*') {
            $field = '`' . $field . '`';
        }

        $this->options['field'] = 'SUM(' . $field . ')';

        return $this->generateSelectQuerySql();
    }

    /**
     * 生成查询语句
     * 
     * @access public
     * @return void
     */
    public function generateSelectQuerySql ()
    {
        $field = '*';
        $where = '';
        $order = '';
        $limit = '';
        $alias = '';
        $lock = '';
        $cache = '';

        if (empty($this->table)) { 
            throw new \Exception ("table not null!");
        }

        // 判断是否有别名
        if (!empty($this->alias)) { 
            $alias = ' AS ' . $this->alias;
        }

        // field条件
        if (!empty($this->options['field'])) { 
            $field = $this->options['field'];
        }

        // where条件
        if (!empty($this->options['where'])) { 
            $where = $this->queryWhere->getWhereSql($this->options['where']);
        }

        var_dump($where);

        // order条件
        if (!empty($this->options['order'])) { 
            $order = ' ORDER BY ' . rtrim($this->options['order'], ', ');
        }

        // limit条件
        if (!empty($this->options['limit'])) { 
            $limit = ' LIMIT ' . $this->options['limit'];
        }

        // 判断是否需要加锁
        if ($this->switchs['lock'] === true) { 
            $lock = ' FOR UPDATE';
        }

        // 判断是否需要加锁
        if ($this->switchs['cache'] === true) { 
            $cache = ' SQL_CACHE ';
        }

        // 组装sql数据
        $sql = 'SELECT ' . $cache . $field . ' FROM `' . $this->prefix . $this->table . '` ' . $where . $order . $limit . $lock;

        if ($this->switchs['fetchSql'] === true) { 
            return $sql;
        }

        return $this->execute($sql);
    }

    /**
     * 执行语句
     * 
     * @access public
     * @return void
     */
    public function execute($sql)
    {
        var_dump($sql);
        $result = $this->connection->connect([])->query($sql);

        var_dump($result);
    }
}
