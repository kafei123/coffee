<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

/**
 * Class QueryRelation
 * @package coffee
 * @method void joinOne() static 一对一关联 
 * @method void joinMore() static 一对多关联 
 * @method void joinOneThrough() static 远程一对一关联
 * @method void joinMoreThrough() static 远程一对多关联
 * @method void joinBelongsToMany() static 多对多关联
 * @method void joinBelongsToManyThrough() static 远程多对多关联
 * @method void joinWithCount() static 关联统计
 */

namespace coffee\db;

use coffee\db\relation\QueryJoin;

class QueryRelation
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
    private $config = [];

    /**
     * 执行关联
     * 
     * @access public
     * @param  object $query Query类
     * @param  string $type 类型
     * @param  array $config 关联条件
     * @return Query
     */
    public static function join(Query $query, $type = '', array $config = []) 
    { 
        $join = '';

        // 获取关联的主表的名字
        $table = $query->table;

        // 判断是否有别名
        if (isset($query->alias) && !empty($query->alias)) { 
            $table = $query->alias;
        }

        // 判断关联类型
        switch ($type) { 
            // 单条关联
            case 'joinOne':
                // 判断是否要求使用join关联
                if ($config['is_join'] === true) { 
                    $join = self::joinOneJoin($table, $config['config']);
                }
                break;
            
            default:
                # code...
                break;
        }

        return $query;
    }

    /**
     * 使用join关联
     * 
     * @access public
     * @param  string $table 主表名字
     * @param  array $config 关联条件
     * @return string
     */
    public static function joinOneJoin($table = '', array $config = []) 
    { 
        $queryJoin = QueryJoin::getInstance();

        $join = $queryJoin->join($table, $config);
    }
}