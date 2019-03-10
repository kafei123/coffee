<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee\db;

class QueryWhere
{
    /**
     * 对象实例化
     * 
     * @var QueryWhere
     */
    public static $instance = null;

    /**
     * 查询语句
     * 
     * @var string
     */
    public $where = '';

    /**
     * 查询条件
     * 
     * @var array|string
     */
    public $option = [];

    /**
     * 符号查询
     * 
     * @var array
     */
    protected $expressionsConvert = ['='=>'=','<>'=>'<>','>'=>'>','<'=>'<','<='=>'<=','>='=>'>=','eq'=>'=','neq'=>'<>','gt'=>'>','lt'=>'<','elt'=>'<=','egt'=>'>=','like'=>'LIKE','not like'=>'NOT LIKE ','not between'=>'NOT BETWEEN ','between'=>'BETWEEN','in'=>'IN','not in'=>'NOT IN','null'=>'NULL','not null'=>'NOT NULL'];

    /**
     * 符号查询
     * 
     * @var array
     */
    protected $ops = ['BETWEEN', 'NOT BETWEEN', 'NULL', 'NOT NULL'];

    /**
     * 私有构造函数
     * 
     * @access private
     */
    private function __construct ()
    {}

    /**
     * 获取实例
     * 
     * @access public
     * @return QueryWhere
     */
    public static function getInstance ()
    {
        if (self::$instance === null) { 
            self::$instance = new self();
        }

        return self::$instance;
    }



    /**
     * 分析查询语句
     * 
     * @access public
     * @param  string|array     $field 查询字段
     * @param  string           $op 查询条件
     * @param  string           $condition 查询条件
     * @return string
     */
    public function parseWhereExp ($field, $op = null, $condition = null)
    {
        var_dump($op);
        $where = [];

        // 判断是否单字符串查询
        if (is_string($field) && is_string($op) && $condition === null) { 
            if ($op == 'null' || $op == 'not null') {
                // null的表达式查询
                $where[] = $this->getWhereExpression($field, $op, '');
            } else {
                // 默认查询
                $where[] = $this->getWhereArray($field, '=', $op);
            }
        } else if (is_string($field) && is_string($op) && (is_string($condition) || is_numeric($condition))) { 
            // 表达式查询
            $where[] = $this->getWhereExpression($field, $op, $condition);
        } else if (is_string($field) && is_array($op) && $condition === null) { 
            foreach ($op as $key => $value) { 
                $where[] = $this->getWhereArray($field, $key, $value);
            }
        } else if (is_array($field)) { 
            // 数组
            foreach ($field as $key => $value) { 
                if (is_array($value) && count($value) == 2) { 
                    var_dump($value);
                    // 表达式查询
                    $where[] = $this->getWhereExpression($key, $value[0], $value[1]);
                } else if (is_string($value) || is_numeric($value)) { 
                    // 普通查询
                    $where[] = $this->getWhereArray($key, '=', $value);
                }
            }
        }

        return $where;
    }

    /**
     * 表达式查询
     * 
     * @access public
     * @param  string           $field 查询字段
     * @param  string           $op 表达式
     * @param  string           $condition 查询条件
     * @return string
     */
    public function getWhereExpression ($field, $op, $condition)
    {
        $where = [];

        switch ($op) {
            case 'in':
                if (is_array($condition)) {
                    $condition = implode(',', $condition);
                }
                break;
            case 'not in':
                if (is_array($condition)) {
                    $condition = implode(',', $condition);
                }
                break;
            case 'like':
                if (is_array($condition)) {
                    throw new \InvalidArgumentException("LIKE expression Not support Array!");
                }
                break;
            case 'not like':
                if (is_array($condition)) {
                    throw new \InvalidArgumentException("Not Like expression Not support Array!");
                }
                break;
            case 'between':
                if (is_array($condition)) { 
                    $condition = implode(',', $condition);
                } else if (strpos($condition, ',') === false) { 
                    throw new \InvalidArgumentException("between expression Not support Array!");
                }
                break;
            case 'not between':
                if (is_array($condition)) {
                    $condition = implode(',', $condition);
                }
                break;
            default:
                break;
        }

        $where = $this->getWhereArray($field, $op, $condition);

        return $where;
    }

    /**
     * 查询条件
     * 
     * @access public
     * @param  string           $field 查询字段
     * @param  string           $op 查询条件
     * @param  string           $condition 查询条件
     * @return string
     */
    public function getWhereArray ($field, $op, $condition)
    {
        if (isset($this->expressionsConvert[$op])) { 
            $op = $this->expressionsConvert[$op];
        } else {
            throw new \InvalidArgumentException("`{$op}` Expression Not Existent!");
        }

        $where = [
            'field' => $field,
            'op' => $op,
            'condition' => $condition,
        ];

        return $where;
    }

    /**
     * 查询条件
     * 
     * @access public
     * @param  string           $field 查询字段
     * @param  string           $op 查询条件
     * @param  string           $condition 查询条件
     * @return string
     */
    public function getWhereArrayBetween ($field, $op, $condition)
    {
        if (isset($this->expressionsConvert[$op])) { 
            $op = $this->expressionsConvert[$op];
        } else {
            throw new \InvalidArgumentException("`{$op}` Expression Not Existent!");
        }

        $where = [
            'field' => $field,
            'op' => $op,
            'condition' => $condition,
        ];

        return $where;
    }
    
    /**
     * 生成where语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getWhereSql ($wheres)
    {
        $sql = '';

        foreach ($wheres as $logic => $where) { 

            if ($logic == 'AND') { 
                $sql .= $this->getWhereSqlAnd($sql, $where);
            } else if ($logic == 'OR') {
                $sql .= $this->getWhereSqlOr($sql, $where);
            }
        }

        $sql = ' WHERE ' . rtrim($sql, 'AND ');

        return $sql;
    }
    
    /**
     * 生成where的and语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getWhereSqlAnd ($sql, $where)
    {
        // 遍历查询条件
        foreach ($where as $param) { 
            $sql .= $this->getWhereSqlString('AND', $param);
        }

        return $sql;
    }
    
    /**
     * 生成where的or语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getWhereSqlOr ($sql, $where)
    {
        foreach($where as $whereItems) {
            $sqlor = '';

            foreach ($whereItems as $param) { 
                $sqlor .= $this->getWhereSqlString('OR', $param);
            }
            $sqlor = rtrim($sqlor, 'OR ');

            $sqlor = '(' . $sqlor . ') AND ';

            $sql .= $sqlor;
        }

        return $sql;
    }

    /**
     * 生成where的sql语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getWhereSqlString ($logic, $param)
    {
        $sql = '';

        // 特殊查询，特殊处理
        if (in_array($param['op'], $this->ops)) {
            $sql = $this->getSpecialWhereSql($param) . ' ' . $logic . ' ';
        } else { 
            $sql = '`' . $param['field'] . '` ' . $param['op'] . ' "' . $param['condition'] . '" ' . $logic . ' ';
        }

        return $sql;
    }

    /**
     * 生成特殊语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getSpecialWhereSql ($param)
    {
        $sql = '';

        if ($param['op'] == 'BETWEEN' || $param['op'] == 'NOT BETWEEN') { 
            $sql = $this->getBetweenSql($param);
        } else if ($param['op'] == 'NULL' || $param['op'] == 'NOT NULL') { 
            $sql = $this->getNullSql($param);
        }

        return $sql;
    }

    /**
     * 生成BETWEEN语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getBetweenSql ($param)
    {
        if (strpos($param['condition'], ',') === false) { 
            return '';
        }

        $condition = explode(',', $param['condition']);

        return ' `' . $param['field'] . '` ' . $param['op'] . ' "' . $condition[0] . '" AND "' . $condition[1] . '" ';
    }

    /**
     * 生成Null语句
     * 
     * @access public
     * @param  array           $wheres 查询条件
     * @return string
     */
    public function getNullSql ($param)
    {
        return ' `' . $param['field'] . '` IS ' . $param['op'] . ' ';
    }
}
