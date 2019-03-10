<?php
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

class Request
{
    /**
     * 配置参数
     * 
     * @var array
     */
    protected $config = [];

    /**
     * 当前请求URL
     * 
     * @var string
     */
    protected $url = '';

    /**
     * 请求类型
     * 
     * @var string
     */
    protected $method = '';

    /**
     * 主机名（含端口）
     * 
     * @var string
     */
    protected $host = '';

    /**
     * 当前请求域名
     * 
     * @var string
     */
    protected $domin = '';

    /**
     * 路由参数
     * 
     * @var string
     */
    protected $route = '';

    /**
     * 构造函数
     * 
     * @access public
     * @param array $config 配置
     * @return void
     */
    public function __construct(array $config = []) 
    { 
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 容器注册
     * 
     * @access public
     * @param App $app coffee\\App
     * @param Config $config coffee\\Config
     * @return Request
     */
    public function __make(App $app, Config $config)  
    { 
        $request = new static($config->get('app.'));

        return $request;
    }

    /**
     * 获取当前的请求信息
     * 
     * @access public
     * @return Request
     */
    public function obtainRequestInfo ()  
    { 

    }

    /**
     * 获取端口
     * 
     * @access public
     * @return array
     */
    public function port ()  
    { 
        if (empty($this->port)) { 
            $this->port = $this->getPort();
        }

        return $this->port;
    }

    /**
     * 获取端口
     * 
     * @access public
     * @return array
     */
    public function getPort () 
    { 
        return $this->server('SERVER_PORT');
    }

    /**
     * 获取主机名
     * 
     * @access public
     * @return array
     */
    public function host ()  
    { 
        if (empty($this->host)) { 
            $this->host = $this->getHost();
        }

        return $this->host;
    }

    /**
     * 获取主机名
     * 
     * @access public
     * @return array
     */
    public function getHost () 
    { 
        return $this->server('HTTP_HOST') . ':' . $this->server('SERVER_PORT');
    }

    /**
     * 获取URL
     * 
     * @access public
     * @return array
     */
    public function url ()  
    { 
        if (empty($this->url)) { 
            $this->url = $this->getUrl();
        }

        return $this->url;
    }

    /**
     * 生成URL
     * 
     * @access public
     * @return array
     */
    public function getUrl () 
    { 
        return $this->server('REQUEST_SCHEME') . '://' . $this->server('HTTP_HOST') . ':' . $this->server('SERVER_PORT') . $this->server('REQUEST_URI');
    }

    /**
     * 获取GET参数
     * 
     * @access public
     * @param string $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function get ($name = null, $default = null)  
    { 
        if (empty($this->get)) { 
            $this->get = $this->getMethodParams('GET');
        }

        return $this->getParam($this->get, $name, $default);
    }

    /**
     * 获取POST参数
     * 
     * @access public
     * @param string $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function post ($name = null, $default = null)  
    { 
        if (empty($this->post)) { 
            $this->post = $this->getMethodParams('POST');
        }

        return $this->getParam($this->post, $name, $default);
    }

    /**
     * 获取REQUEST参数
     * 
     * @access public
     * @param string $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function request ($name = null, $default = null)  
    { 
        if (empty($this->request)) { 
            $this->request = $this->getMethodParams('REQUEST');
        }

        return $this->getParam($this->request, $name, $default);
    }

    /**
     * 获取INPUT参数
     * 
     * @access public
     * @param string $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function input ($name = null, $default = null)  
    { 
        if (empty($this->input)) { 
            $this->input = $this->getInput();
        }

        return $this->getParam($this->input, $name, $default);
    }

    /**
     * 返回INPUT参数
     * 
     * @access public
     * @param string $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function getInput ()  
    { 
        $input = file_get_contents("php://input");

        // 判断当前请求内容类型
        if (empty($this->contentType)) { 
            $this->contentType = $_SERVER['CONTENT_TYPE'];
        }

        // 判断格式
        if (false !== strpos($this->contentType(), 'application/json') || 0 === strpos($input, '{"')) {
            return (array) json_decode($input, true);
        } elseif (strpos($input, '=')) {
            parse_str($input, $data);
            return $data;
        }

        return [];
    }

    /**
     * 获取指定参数
     * 
     * @access public
     * @param array $data 数据集合
     * @param string $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function getParam ($data = [], $name = null, $default = null)  
    { 
        if ($name === false) { 
            // 返回全部
            return $data;
        }

        if (!isset($data[$name])) { 
            return $default;
        }

        return !empty($data[$name]) ? $data[$name] : $default;
    }

    /**
     * 获取参数
     * 
     * @access public
     * @param string $method 请求类型
     * @return array
     */
    public function getMethodParams ($method = null)  
    { 
        if (empty($method)) { 
            // 获取当前的请求类型
            if (empty($this->method)) { 
                $this->method = $_SERVER['REQUEST_METHOD'];
            }
            $method = $this->method;
        }

        // method 大写
        $method = strtoupper($method);

        $params = [];

        switch ($method) { 
            case 'GET':
                $params = $_GET;
                break;
            case 'POST':
                $params = $_POST;
                break;
            case 'REQUEST':
                $params = $_REQUEST;
                break;
            default:
                $params = $this->input(false);
                break;
        }

        return $this->filterParams($params);
    }

    /**
     * 过滤参数
     * 
     * @access public
     * @param string $method 请求类型
     * @return array
     */
    public function filterParams ($params = [])  
    { 
        // 判断是否有过滤函数
        if (!empty($params)) { 
            // 没有，使用默认的
            foreach ($params as &$param) { 
                $param = htmlspecialchars($param);
            }
        }

        return $params;
    }

    /**
     * 获取SERVER数据
     * 
     * @access public
     * @param string|bool $name 参数名
     * @param string $default 默认值
     * @return array
     */
    public function server ($name = false, $default = null)  
    { 
        if (empty($this->server)) { 
            $this->server = $_SERVER;
        }

        if ($name === false) { 
            // 返回全部
            return $this->server;
        }

        if (!isset($this->server[$name])) { 
            return $default;
        }

        return !empty($this->server[$name]) ? $this->server[$name] : $default;
    }

    /**
     * 获取当前请求内容类型
     * 
     * @access public
     * @return array
     */
    public function contentType ()  
    { 
        if (empty($this->contentType)) { 
            $this->contentType = $this->server('CONTENT_TYPE');
        }

        // 判断是否存在;号
        if (strpos($this->contentType, ';')) { 
            // 存在，进行切割
            list($type) = explode(';', $this->contentType);
        } else { 
            // 不存在，直接赋值
            $type = $this->contentType;
        }

        return trim($type);
    }

    /**
     * 获取路由参数
     * 
     * @access public
     * @return array
     */
    public function getRoute () 
    { 
        return $this->getMethodParams('GET');
    }

    /**
     * 获取当前请求类型
     * 
     * @access public
     * @return array
     */
    public function getMethod () 
    { 
        return $this->server('REQUEST_METHOD');
    }
}
