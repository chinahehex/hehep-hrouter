<?php
namespace hehe\core\hrouter\base;

/**
 * 规则基类
 */
abstract class Rule
{

    const RULE_ACTION_NAME = 'action';
    const RULE_URI_NAME = 'uri';
    const URL_METHOD_NAME = 'method';

    /**
     * 只适用于解析URL(parseRequest)
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var int
     */
    const PARSING_ONLY = 1;

    /**
     * 只适用于URL 创建 (parseUrL)
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var int
     */
    const CREATION_ONLY = 2;

    /**
     * 规则适用范围
     *<B>说明：</B>
     *<pre>
     * PARSING_ONLY 1 表示适用解析url
     * CREATION_ONLY 2 表示适用创建url
     *</pre>
     * @var int
     */
    protected $mode;

    /**
     * pathinfo 正则表达式
     *<B>说明：</B>
     *<pre>
     *  基本格式:^(?<controller>\w+)/(?<action>\w+)$
     *</pre>
     */
    protected $uri = "";

    /**
     * url 规则
     *<B>说明：</B>
     *<pre>
     *  基本格式:<controller>/<action>
     *  比如:
     *  pathinfoRule:<controller:\w+>/<id:\d+>
     *  url 地址可以使用controller,id 参数
     *  url 最终格式可以有:
     *  post/<id>
     *  <controller>/<id>
     *</pre>
     */
    protected $action = "";

    /**
     * 请求类型
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    protected $method = '';

    /**
     * 路由id
     *<B>说明：</B>
     *<pre>
     * 全局唯一,用于生成URL地址
     *</pre>
     */
    protected $id = '';

    /**
     * 是否启用域名检测
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var bool
     */
    protected $domain = false;

    /**
     * http host
     * @var string
     */
    protected $host = '';

    /**
     * 后缀
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string|bool
     */
    protected $suffix = '';

    /**
     * 是否完全匹配
     * @var bool
     */
    protected $completeMatch = true;

    /**
     * @var Router
     */
    protected $router;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }
    }

    public function setRouter(Router $router):void
    {
        $this->router = $router;
    }

    public function init():void
    {
        
    }

    public function asDomain(string $host = '',bool $domain = true):self
    {
        $this->domain = $domain;
        $this->host = $host;

        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function asId(string $id):self
    {
        $this->id = $id;

        return $this;
    }

    public function getId():string
    {
        return $this->id;
    }

    public function asCompleteMatch(bool $completeMatch = true):self
    {
        $this->completeMatch = $completeMatch;

        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function asMethod(string $method):self
    {
        $this->method = $method;

        return $this;
    }

    public function asAction(string $action):self
    {
        $this->action = $action;

        return $this;
    }

    public function getArrMethod():array
    {
        $methods = [];
        if (!empty($this->method)) {
            if ($this->method === '*') {
                $methods[] = $this->method;
            } else if (strpos($this->method,'|') !== false) {
                $methods = explode('|',$this->method);
            } else if (strpos($this->method,',') !== false) {
                $methods = explode(',',$this->method);
            } else {
                $methods[] = $this->method;
            }
        }

        return $methods;
    }

    public function asSuffix($suffix = true):self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function getUri():string
    {
        return $this->uri;
    }

    public function getAction():string
    {
        return $this->action;
    }

    public function asOptions(array $options):self
    {
        if (!empty($options)) {
            foreach ($options as $name=>$value) {
                $this->{$name} = $value;
            }
        }

        return $this;
    }

    public function getOptions(array $propertys):array
    {
        $attrs = [];
        if (!empty($propertys)) {
            foreach ($propertys as $name) {
                $attrs[$name] = $this->{$name};
            }
        }

        return $attrs;
    }

   /**
    * 解析请求路由
    *<B>说明：</B>
    *<pre>
    * 略
    *</pre>
    * @param string $pathinfo 路由请求
    * @param RouteRequest $routeRequest 路由请求对象
    * @return RouteRequest
    */
    abstract function parseRequest(string $pathinfo,?RouteRequest $routeRequest = null);

    /**
     * 生成url地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $url url地址
     * @param array $params url参数
     * @return array|bool
     */
    abstract public function parseUrL(string $url = '',array $params = []);
}
