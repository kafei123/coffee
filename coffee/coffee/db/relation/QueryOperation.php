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
 *      'where'         => ['id' => 1],
 *      'type'          => 'leftJoin',
 *      'is_join'       => true
 * ])
 */

namespace coffee\db\relation;

use coffee\db\QueryWhere;

class QueryOperation 
{ 
    /**
     * 构造函数
     * 
     * @access public
     */
    public function __construct()
    { 
        $this->queryWhere = QueryWhere::getInstance();
    }
    
    /**
     * 设置where条件
     * 
     * @access public
     * @param  array            $config 参数数组
     * @return Query
     */
    public function where($config)
    {
        $where = [];

        // 遍历参数
        foreach ($config as $field => $condition) { 
            $where[] = $this->queryWhere->parseWhereExp($field, $condition);
        }

        return $where;
    }

    /**
     * 设置field条件
     * 
     * @access public
     * @param  array            $config 参数数组
     * @return Query
     */
    public function field($field)
    {
        if (is_array($field)) { 
            $field = implode(',', $field);
        }

        return $field;
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
        $order = '';

        if (is_string($orders)) { 
            $order = $orders;
        } else if (is_array($orders)) { 
            $ordersString = '';
            // 循环
            foreach ($orders as $field => $op) { 
                if ($op == 'desc' || $op == 'asc') { 
                    $ordersString .= ' `' . $field . '` ' . $op . ', ';
                }
            }
            $order = $ordersString;
        }

        return $order;
    }

    /**
     * 生成join的sql语句
     * 
     * @access public
     * @param  array     $joins join配置信息
     * @return void
     */
    public function generateJoinSqlString($joins)
    {
        $joinString = '';

        foreach ($joins as $joinType => $joinItems) { 
            // 判断关联类型
            if ($joinType == 'join') { 
                // join关联
                $joinString = $this->generateJoinSqlStringJoin($joinItems);
            }
        }
    }

    /**
     * 生成join的sql语句
     * 
     * @access public
     * @param  array     $joins join配置信息
     * @return void
     */
    public function generateJoinSqlStringJoin($joins)
    {
        foreach ($joins as $join) { 
            // 获取表名
            $table_name = $join['table'];
            
            // 是否存在别名
            if (!empty($join['alias'])) { 
                $table_name = $join['alias'];
            }

            // 获取where条件
            if (!empty($join['where'])) { 
                $where = $this->generateJoinSqlStringJoinWhere($join['where']);
            }

            
            
        }
    }

    /**
     * 生成join的sql语句
     * 
     * @access public
     * @param  array     $joins join配置信息
     * @return void
     */
    public function generateJoinSqlStringJoinWhere($wheres)
    {
        return $this->queryWhere->getWhereSql($wheres);
    }
}
