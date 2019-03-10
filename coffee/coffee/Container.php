<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class Container
{
    /** 
     * 容器对象实例
     * 
     * @var Container
     */
    protected static $instance;

    /**
     * 容器中的对象实例
     * 
     * @var array
     */
    protected $instances = [];

    /**
     * 容器绑定标识
     * 
     * @var array
     */
    protected $bind = [
        'app'               => App::class,
        'config'            => Config::class,
        'route'             => Route::class,
        'log'               => Log::class,
        'request'           => Request::class
    ];

    /**
     * 容器标识别名
     * 
     * @var array
     */
    protected $name = [];

    /**
     * 获取当前容器的实例（单例）
     * 
     * @access public
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 设置当前容器的实例
     * 
     * @access public
     * @param  object        $instance
     * @return void
     */
    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }

    /**
     * 获取容器中的对象实例
     * 
     * @access public
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     * @return object
     */
    public static function get ($abstract, $vars = [], $newInstance = false)
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * 
     * @access public
     * @param  string  $abstract    类标识、接口
     * @param  mixed   $concrete    要绑定的类、闭包或者实例
     * @return Container
     */
    public static function set ($abstract, $concrete = null)
    {
        return static::getInstance()->bindTo($abstract, $concrete);
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * 
     * @access public
     * @param  string|array  $abstract    类标识、接口
     * @param  mixed         $concrete    要绑定的类、闭包或者实例
     * @return $this
     */
    public function bindTo($abstract, $concrete = null)
    {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } elseif ($concrete instanceof Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            if (isset($this->bind[$abstract])) {
                $abstract = $this->bind[$abstract];
            }
            $this->instances[$abstract] = $concrete;
        } else {
            $this->bind[$abstract] = $concrete;
        }

        return $this;
    }

    /**
     * 绑定一个类实例当容器
     * 
     * @access public
     * @param  string           $abstract    类名或者标识
     * @param  object|\Closure  $instance    类的实例
     * @return $this
     */
    public function instance($abstract, $instance)
    {
        if ($instance instanceof \Closure) {
            $this->bind[$abstract] = $instance;
        } else {
            if (isset($this->bind[$abstract])) {
                $abstract = $this->bind[$abstract];
            }

            $this->instances[$abstract] = $instance;
        }

        return $this;
    }

    /**
     * 创建类的实例
     * 
     * @access public
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     * @return object
     */
    public function make($abstract, $vars = [], $newInstance = false)
    {
        // 防止有人将 vars 当成 newInstance 来使用
        if ($vars === true) {
            // 如果 vars 是true，则相当于 newInstance = true
            $newInstance = true;
            $vars = [];
        }

        // 判断是否存在该容器的标识名
        $abstract = isset($this->name[$abstract]) ? $this->name[$abstract] : $abstract;

        // 判断是否存在该实例，并且是否不用每次创建新的实例
        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        // 判断是否存在该容器标识
        if (isset($this->bind[$abstract])) {
            $concrete = $this->bind[$abstract];
            // 直接实例化
            $this->name[$abstract] = $concrete;
            return $this->make($concrete, $vars, $newInstance);
        } else {
            $object = $this->invokeClass($abstract, $vars);
        }

        // 判断是否不用每次创建新的实例
        if (!$newInstance) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * 
     * @access public
     * @param  string    $class 类名
     * @param  array     $vars  参数
     * @return mixed
     */
    public function invokeClass($class, $vars = [])
    {
        try {
            // 实例化反射类
            $reflect = new ReflectionClass($class);

            // 判断是否存在__make方法
            if ($reflect->hasMethod('__make')) {
                // 存在，则实例化__make方法
                $method = new ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    $args = $this->bindParams($method, $vars);
                    return $method->invokeArgs(null, $args);
                }
            }

            // 获取构造函数
            $constructor = $reflect->getConstructor();

            // 如果存在，则进行绑定，如果不存在，则赋予空数组
            $args = $constructor ? $this->bindParams($constructor, $vars) : [];

            // 返回实例
            return $reflect->newInstanceArgs($args);

        } catch (Exception $e) {
            throw new Exception('class not find: ' . $class);
        }
    }

    /**
     * 绑定参数
     * 
     * @access protected
     * @param  \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param  array                                 $vars    参数
     * @return array
     */
    protected function bindParams($reflect, $vars = [])
    {
        // 判断是否存在参数
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 判断类型数组，数字数组时按顺序绑定参数
        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();

        foreach ($params as $param) {
            $name       = $param->getName();
            $lowerName  = Loader::parseName($name);
            $class      = $param->getClass();

            if ($class) {
                var_dump($class->getName());
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif (0 == $type && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif (0 == $type && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new Exception('method param miss:' . $name);
                
            }
        }

        return $args;
    }

    

    /**
     * 获取对象类型的参数值
     * 
     * @access protected
     * @param  string   $className  类名
     * @param  array    $vars       参数
     * @return mixed
     */
    protected function getObjectParam($className, &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    public function __set($name, $value)
    {
        $this->bindTo($name, $value);
    }

    public function __get($name)
    {
        return $this->make($name);
    }
}
