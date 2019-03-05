<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

/**
 * join([
 *      'table'         => 'users',
 *      'primary_key'   => 'id',
 *      'foreign_key'   => 'user_id',
 *      'field'         => 'id,name',
 *      'where'         => [
 *          'id' => 1
 *       ],
 *      'type'          => 'leftJoin',
 *      'is_join'       => true
 * ])
 */

namespace coffee\db\relation;

use coffee\db\QueryWhere;

class QueryJoin
{
    /**
     * 单例模式，储存实例化对象
     * 
     * @access private
     * @var  object
     */
    private static $instance = null;

    /**
     * 关联规则
     * 
     * @access private
     * @var  array
     */
    private $config = [
        'primary_table'     => '',
        'primary_key'       => '',
        'primary_alias'     => '',
        'table'             => '',
        'foreign_key'       => '',
        'alias'             => '',
        'field'             => '',
        'where'             => [],
        'type'              => 'left',
        'is_join'           => false
    ];

    /**
     * 当前查询参数
     * 
     * @var array
     */
    protected $options = [
        'field'         => '',
        'where'         => '',
        'order'         => '',
        'limit'         => '',
        'join'          => '',
        'union'         => '',
    ];

    /**
     * 私有化构造函数
     * 
     * @access private
     */
    private function __construct () 
    {}

    /**
     * 私有克隆函数，防止外办克隆对象
     * 
     * @access private
     */
    private function __clone () 
    {}

    /**
     * 获取实例
     * 
     * @access public
     * @param  object $query Query类
     * @return Query
     */
    public static function getInstance() 
    { 
        if (self::$instance === null) { 
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * join关联
     * 
     * @access public
     * @param  string $primary_table 主表名字
     * @param  array $configs 关联条件
     * @return Query
     */
    public function join($primary_table, array $configs = []) 
    { 
        // 遍历数组
        foreach ($configs as $config) { 

            // 如果参数不合法，直接返回
            if (empty($config['table']) || empty($config['primary_key']) || empty($config['foreign_key'])) { 
                continue;
            }

            $this->table_name = $config['table'];

            // 判断是否存在别名
            if (!empty($config['alias'])) { 
                $this->table_name = $config['alias'];
            }

            $join = 'left join';

            // 更新规则
            $config = array_merge($this->config, $config);

            // 判断关联类型
            if ($config['type'] == 'right') { 
                $join = 'right join';
            } else if ($config['type'] == 'inner') { 
                $join = 'inner join';
            }

            // 判断是否有查询条件
            if (!empty($config['where'])) { 
                $this->joinWhere();
            }

            // 筛选字段
            if (!empty($config['field'])) { 
                $this->joinField($config['field']);
            }

            // 排序条件
            if (!empty($config['order'])) { 
                $this->joinOrder($config['order']);
            }
        }
        var_dump($configs);
    }

    /**
     * 设置where条件
     * 
     * @access public
     * @param  string|array     $orders 排序条件集
     * @return void
     */
    public function joinWhere($orders = '')
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
    }

    /**
     * join关联 字段筛选
     * 
     * @access public
     * @param  string|array $field 字段筛选
     * @return string
     */
    public function joinField($fields = '') 
    {   
        if (is_array($fields)) { 
            foreach ($fields as &$field) { 
                $field = '`' . $this->table_name . '`.`' . $field . '`';
            }
        } else if (is_string($fields)) {
            $fields = explode(',', $fields);

            foreach ($fields as &$field) { 
                $field = '`' . $this->table_name . '`.`' . $field . '`';
            }
        }

        $this->options['field'] = implode(',', $fields);
    }

    /**
     * 设置order条件
     * 
     * @access public
     * @param  string|array     $orders 排序条件集
     * @return void
     */
    public function joinOrder($orders = '')
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
    }
}