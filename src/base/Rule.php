<?php
namespace hehe\core\hrouter\base;


/**
 * 路由规则基类
 */
class Rule
{

    const RULE_ACTION_NAME = 'action';
    const RULE_URI_NAME = 'uri';
    const URL_METHOD_NAME = 'method';

    // 匹配 pathinfo 正则表达式/
    const PATHINFO_PARAMS_REGEX = '/<(\w+):?([^>]+)?>|\\{(\w+):?([^\\}]+)?\\}/';
    const PATHINFO_PARAMS_REGEX1 = '/<_?(\w+):?([^>]+)?>/';
    const PATHINFO_PARAMS_REGEX2 = '/\\{_?(\w+):?([^\\}]+)?\\}/';

    // 匹配action 正则表达式
    const ACTION_PARAMS_REGEX = '/<_?(\w+)>|\\{(\w+)\\}/';

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
     * url 正则表达式
     *<B>说明：</B>
     *<pre>
     * 用于匹配url 的正则表达式
     * 基本格式:#^(?P<controller>\w+)/(?P<action>\w+)$#u
     *</pre>
     */
    protected $uriRegex = "";

    /**
     * 验证pathinfo 地址的正则表达式
     *<B>说明：</B>
     *<pre>
     * 用于匹配action的正则表达式
     * 基本格式:#^(?P<controller>\w+)/(?P<action>\w+)$#u
     *</pre>
     */
    protected $actionRegex;

    /**
     * url 替换模板
     *<B>说明：</B>
     *<pre>
     * 基本格式:<controller>/<action>
     * 生成外部url 地址的模板
     *</pre>
     */
    protected $urlTemplate = "";

    /**
     * uri抽取的参数
     *<B>说明：</B>
     *<pre>
     * 基本格式:['controller'=>'\w+,'action'=>'\w+']
     *</pre>
     */
    protected $uriParams = [];

    /**
     * action抽取的参数
     *<B>说明：</B>
     *<pre>
     * 格式:基本格式:['controller'=>'<controller>,'action'=>'<action>']
     *</pre>
     */
    protected $actionParams = [];

    /**
     * 默认$_GET参数
     *<B>说明：</B>
     *<pre>
     * 默认参数不显示在action地址中
     *</pre>
     * @var array
     */
    public $defaults = [];

    /**
     * 参数解析名称
     * @var string
     */
    public $pvar = 'param';

    /**
     * 参数解析规则配置
     * @var array
     */
    public $prule = [];

    /**
     * 参数解析规则对象
     * @var ParamRule
     */
    protected $_paramRule;

    /**
     * 是否初始化
     * @var bool
     */
    protected $_init_status = false;

    /**
     * 私有变量集合
     * 私有变量不进入URL参数
     * @var array
     */
    protected $privateVars = [];

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

    public function asAction(string $action):self
    {
        $this->action = $action;

        return $this;
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
     *
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    public function init():void
    {
        if ($this->_init_status) {
            return ;
        }

        $this->_init_status = true;

        if (strpos($this->uri, '://') !== false) {
            list($host,$pathinfo) = explode('://',$this->uri);
            $this->host = $host;
            $this->domain = true;
        } else if ($this->domain === true) {
            $this->host = rtrim($this->host, '/');
            $this->uri = rtrim($this->host . '/' . $this->uri, '/');
        }

        // 判断action 是否类路径
        if (strpos($this->action,"@") !== false) {
            $this->mode = self::PARSING_ONLY;
        }

        // URL参数解析
        if (!empty($this->prule)) {
            $this->pvar = $this->prule['pvar'];
        }

        $this->buildUri();
        $this->buildAction();
    }

    /**
     * 验证路由地址(action)是否有变量
     * @return bool
     */
    public function hasActionFlag()
    {
        if (preg_match(self::ACTION_PARAMS_REGEX, $this->action)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证路由规则(uri)是否有变量
     * @return bool
     */
    public function hasUriFlag()
    {
        if (preg_match(self::PATHINFO_PARAMS_REGEX, $this->uri)) {
            return true;
        } else {
            return false;
        }
    }

    public function asParams(array $params = []):self
    {
        $this->uriParams = array_merge($this->uriParams,$params);

        return $this;
    }


    public function getUriParams():array
    {
        return $this->uriParams;
    }

    public function asDefaults(array $params = []):self
    {
        $this->defaults = array_merge($this->defaults,$params);

        return $this;
    }

    public function getUriRegex():string
    {
        return $this->uriRegex;
    }

    public function getActionRegex()
    {
        return $this->actionRegex;
    }

    public function getActionParams():array
    {
        return $this->actionParams;
    }

    protected function buildRegex(string $regex,$end = '$#u',$first = '#^'):string
    {
        return $first . $regex . $end;
    }

    public function asParamsRule(array $prule = []):self
    {
        if (isset($prule['pvar'])) {
            $this->pvar = $prule['pvar'];
            unset($prule['pvar']);
        }

        $this->prule = $prule;

        return $this;
    }

    protected function getParamRule():ParamRule
    {
        if (is_null($this->_paramRule)) {
            $class = $this->prule['class'];
            if (strpos($class,'\\') === false) {// 采用命名空间
                $class = str_replace('/','\\',dirname(str_replace('\\','/',__NAMESPACE__))) . '\\params\\' . ucfirst($class) . 'Param';
            }
            unset($this->prule['class']);
            $this->_paramRule = new $class($this->prule);
        }

        return $this->_paramRule;
    }

    /**
     * 从uri抽取参数规则
     *<B>说明：</B>
     *<pre>
     *  比如,"<controller:\w+>/<action:\w+>"
     *  返回参数格式:{"controller":"\w+","action":"\w+"}
     *</pre>
     */
    private function buildUriParams():void
    {

        if (preg_match_all(self::PATHINFO_PARAMS_REGEX, $this->uri, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!empty($match[1][0])) {
                    $name = $match[1][0];
                    $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                } else if (!empty($match[3][0])) {
                    $name = $match[3][0];
                    $pattern = isset($match[4][0]) ? $match[4][0] : '[^\/]+';
                }

                // 可选状态
                $optional_status = false;
                if (substr($name,-1) === '?') {
                    $name = substr($name,0,strlen($name) - 1);
                    $optional_status = true;
                } else if (substr($pattern,-1) === '?') {
                    $pattern = substr($pattern,0,strlen($pattern) - 1);
                    $optional_status = true;
                }

                if (substr($name,0,1) === '_') {
                    $name = substr($name,1);
                    $this->privateVars[$name] = $name;
                }

                if (isset($this->uriParams[$name])) {
                    if (substr($this->uriParams[$name],-1) === '?') {
                        $optional_status = true;
                        $pattern = substr($this->uriParams[$name],0,strlen($this->uriParams[$name]) - 1);
                    } else {
                        $pattern = $this->uriParams[$name];
                    }
                }

                if ($optional_status === true) {
                    if (substr($pattern,-1) === '/') {
                        $regex = substr($pattern,0,strlen($pattern) - 1);
                        $this->uriParams[$name] = ['pattern'=>"((?P<$name>$regex)/)?",'name'=>"<$name>/"];
                    } else if (substr($pattern,0,1) === '/') {
                        $regex = substr($pattern,1);
                        $this->uriParams[$name] = ['pattern'=>"(/(?P<$name>$regex))?",'name'=>"/<$name>"];
                    } else {
                        $this->uriParams[$name] = ['pattern'=>"(?P<$name>$pattern)?",'name'=>"<$name>"];
                    }
                }  else {
                    $this->uriParams[$name] = $pattern;
                }
            }
        }

    }

    /**
     * 从action抽取参数
     *<B>说明：</B>
     *<pre>
     *  比如,"<controller>/<action>"
     *  返回参数格式:{"controller":"<controller>","action":"<action>"}
     *</pre>
     */
    private function buildActionParams():void
    {

        if (preg_match_all(self::ACTION_PARAMS_REGEX, $this->action, $matches)) {
            if (!empty($matches[1][0])) {
                foreach ($matches[1] as $name) {
                    $this->actionParams[$name] = "<$name>";
                }
            }

            if (!empty($matches[2][0])) {
                foreach ($matches[2] as $name) {
                    $this->actionParams[$name] = "<$name>";
                }
            }

        }
    }

    public function buildFlagName($name):string
    {
        return "<{$name}>";
    }

    protected function buildUri():void
    {
        $this->uri = trim($this->uri,'/');

        // 从uri 提取相关参数
        $this->buildUriParams();

        /** 以下代码生成uri正则表达式 **/

        // 默认替换参数
        $replaceParams = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)',
        ];

        foreach ($this->uriParams as $name=>$pattern) {
            if (is_array($pattern)) {
                $replaceParams[$this->buildFlagName($name)] = $pattern['pattern'];
            } else {
                $replaceParams[$this->buildFlagName($name)] = "(?P<$name>$pattern)";
            }
        }

        $this->urlTemplate = preg_replace([self::PATHINFO_PARAMS_REGEX1,self::PATHINFO_PARAMS_REGEX2], ['<$1>','<$1>'], $this->uri);
        $uri_regex = '';

        if (empty($this->urlTemplate)) {
            $uri_regex = $this->uri;
        } else {
            $uri_regex = trim(strtr($this->urlTemplate, $replaceParams), '/');
        }

        $this->uriRegex = $uri_regex;
    }

    protected function buildAction():void
    {
        if (!is_string($this->action)) {
            return;
        }

        $this->action = trim($this->action, '/');
        $this->action = preg_replace([self::PATHINFO_PARAMS_REGEX1,self::PATHINFO_PARAMS_REGEX2], ['<$1>','<$1>'], $this->action);
        // 从action 提取相关参数
        $this->buildActionParams();

        /** 以下代码生成action正则表达式 **/
        $action_regex = '';
        if (empty($this->actionParams)) {
            $action_regex = $this->action;
        } else {
            $replaceParams = [];
            foreach ($this->actionParams  as $name=>$value) {
                $pattern = $this->uriParams[$name];
                if (is_array($pattern)) {
                    $pattern = $pattern['pattern'];
                    $replaceParams[$value] = $pattern;
                } else {
                    $replaceParams[$value] = "(?P<$name>$pattern)";
                }
            }

            $action_regex = strtr($this->action, $replaceParams);
        }

        $this->actionRegex = $action_regex;
    }



    /**
     * 解析Uri匹配到的参数
     * @param array $matches
     * @param RouteRequest $routeRequest
     */
    public function parseUriMatches(array $matches,?RouteRequest $routeRequest = null):array
    {
        // 合并默认参数
        foreach ($this->defaults as $name => $value) {
            if (!isset($matches[$name]) || $matches[$name] === '') {
                $matches[$name] = $value;
            }
        }

        $params = $this->defaults;

        // pathinofo 规则参数
        $action_replace_params = [];
        foreach ($matches as $name => $value) {
            if (isset($this->actionParams[$name])) {
                $flag_name = $this->actionParams[$name];
                // 判断是否默认值
                if (isset($this->defaults[$name]) && strcmp($this->defaults[$name],$value) === 0) {
                    $action_replace_params[$flag_name] = "";
                } else {
                    if (isset($this->uriParams[$name]) && is_array($this->uriParams[$name])) {
                        $action_replace_params[$flag_name] = $value === "" ? "" :
                            str_replace($flag_name,$value,$this->uriParams[$name]['name']);
                    } else {
                        $action_replace_params[$flag_name] = $value;
                    }
                }

                unset($params[$name]);
            } elseif (isset($this->uriParams[$name])) {
                // 如果是私有参数，则不出现在params 中
                if (!isset($this->privateVars[$name])) {
                    $params[$name] = $value;
                }
            }
        }


        if ($this->action !== '') {
            $url = strtr($this->action, $action_replace_params);
        } else {
            $url = strtr($this->urlTemplate, $action_replace_params);
        }

        // 解析路由参数
        if (!empty($this->pvar) && isset($params[$this->pvar])) {
            $raw_params = trim($params[$this->pvar],'/');
            unset($params[$this->pvar]);
            $params = array_merge($params,$this->getParamRule()->parse($raw_params));
        }

        return [$url, $params,$this];
    }

    /**
     * @param array $matches
     * @param string $url
     * @param array $params
     */
    public function parseActionMatches(array $matches = [],string $url = '',array $params = []):array
    {
        $replaceParams = [];
        $urlRegexParams = [];

        if (!empty($matches)) {
            foreach ($this->actionParams as $name => $val) {
                if (isset($matches[$name])) {
                    $urlRegexParams[$name] = $matches[$name];
                }
            }
        }

        // 合并参数，目的是保留$params参数顺序
        $urlParams = $params;
        foreach ($urlRegexParams as $key=>$value) {
            if (!isset($urlParams[$key])) {
                $urlParams[$key] = $value;
            }
        }

        foreach ($this->defaults as $key=>$value) {
            if (!isset($urlParams[$key])) {
                $urlParams[$key] = $value;
            }
        }

        foreach ($this->uriParams as $name => $value) {
            $flag_name = $this->buildFlagName($name);
            if (!empty($this->pvar) && $name == $this->pvar) {
                $replaceParams[$flag_name] =  $this->getParamRule()->build($urlParams);
            } else if (isset($urlParams[$name])) {
                if (isset($this->defaults[$name]) && strcmp($this->defaults[$name],$urlParams[$name]) === 0) {
                    if (is_array($value)) {
                        $replaceParams[$flag_name] =  "";
                    } else {
                        $replaceParams[$flag_name] = $urlParams[$name];
                    }
                } else {
                    if (is_array($value)) {
                        $replaceParams[$flag_name] = str_replace($flag_name,$urlParams[$name],$value['name']);
                    } else {
                        $replaceParams[$flag_name] = $urlParams[$name];
                    }
                }

                unset($urlParams[$name]);
            } else {
                $replaceParams[$flag_name] =  "";
            }
        }

        $url = trim(strtr($this->urlTemplate, $replaceParams), '/');

        return [$url,$urlParams,$this];
    }

    /**
     * 解析请求路由
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $pathinfo 路由地址 控制器/方法
     * @return array [URL地址,URL 参数]
     */
    public function parseRequest(string $pathinfo,?RouteRequest $routeRequest = null)
    {
        $this->init();

        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!preg_match($this->buildRegex($this->uriRegex,($this->completeMatch ? '$#' : '#')), $pathinfo, $matches)) {
            return false;
        }

        // 解析匹配结果
        $matcheResult = $this->parseUriMatches($matches,$routeRequest);

        return $matcheResult;
    }

    /**
     * 解析url地址
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $url url 地址
     * @param array $params url 参数
     * @return array [URL地址,URL 参数]
     */
    public function parseUrL(string $url = '',array $params = [])
    {
        $this->init();

        if ($this->mode === self::PARSING_ONLY) {
            return false;
        }

        // url 最终替换参数
        $matches = [];
        // 匹配url 正则表达式,
        if ($this->actionRegex == '' || preg_match($this->buildRegex($this->actionRegex), $url, $matches) === 0) {
            return false;
        }

        return $this->parseActionMatches($matches,$url,$params);
    }
}
