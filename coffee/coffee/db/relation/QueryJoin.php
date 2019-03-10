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

use coffee\db\relation\JoinOperation;

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
    { 
        $this->queryOperation = new QueryOperation();
    }

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
    public function join(array $configs = []) 
    { 
        // 遍历数组
        foreach ($configs as &$config) { 

            // 如果参数不合法，直接返回
            if (empty($config['table']) || empty($config['primary_key']) || empty($config['foreign_key'])) { 
                continue;
            }
            
            $config['is_join'] = true;

            $config['join'] = 'LEFT JOIN';

            // 更新规则
            $config = array_merge($this->config, $config);

            // 判断关联类型
            if ($config['type'] == 'right') { 
                $config['join'] = 'RIGHT JOIN';
            } else if ($config['type'] == 'inner') { 
                $config['join'] = 'INNER JOIN';
            }

            // 判断是否有查询条件
            if (!empty($config['where'])) { 
                $config['where']['AND'] = $this->queryOperation->where($config['where']);
            }

            // 筛选字段
            if (!empty($config['field'])) { 
                $config['field'] = $this->queryOperation->field($config['field']);
            }

            // 排序条件
            if (!empty($config['order'])) { 
                $config['order'] = $this->queryOperation->order($config['order']);
            }
        }

        var_dump($configs);
        return $configs;
    }
}