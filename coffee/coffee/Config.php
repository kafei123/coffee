<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

class Config
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 配置前缀
     * @var string
     */
    protected $prefix = 'app';

    /**
     * 配置文件目录
     * @var string
     */
    protected $path;

    /**
     * 配置文件后缀
     * @var string
     */
    protected $ext;

    /**
     * 构造方法
     * @access public
     */
    public function __construct($path = '', $ext = '.php')
    {
        $this->path   = $path;
        $this->ext    = $ext;
    }

    public function index ()
    {
        
    }
}
