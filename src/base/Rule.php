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

    // 合并规则变量标志
    const MERGE_VAR_FLAG = ':';

    const URI_FLAG_REGEX = '/<(\w+):?([^>]+)?>|\\{(\w+):?([^\\}]+)?\\}/';
    const URI_FLAG_REGEX1 = '/<_?(\w+):?([^>]+)?>/';
    const URI_FLAG_REGEX2 = '/\\{_?(\w+):?([^\\}]+)?\\}/';

    // 匹配action 正则表达式
    const ACTION_FLAG_REGEX = '/<_?(\w+)>|\\{(\w+)\\}/';

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
     * 路由规则
     *<B>说明：</B>
     *<pre>
     *  基本格式:<controller:\w+>/<action:\w+>
     *</pre>
     */
    protected $uri = "";

    /**
     * 路由地址
     *<B>说明：</B>
     *<pre>
     *  基本格式:<controller>/<action>
     *</pre>
     */
    protected $action = "";

    /**
     * action替换模板
     *<B>说明：</B>
     *<pre>
     * 基本格式:<controller>/<action>
     *</pre>
     */
    protected $actionTemplate = "";

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
     * html,shtml
     *</pre>
     * @var string|bool
     */
    protected $suffix = '';

    /**
     * 是否完全匹配
     * <B>说明：</B>
     *<pre>
     * 在正则表达式添加$结束符
     *</pre>
     * @var bool
     */
    protected $completeMatch = true;

    /**
     * uri路由规则对应的正则表达式
     *<B>说明：</B>
     *<pre>
     * 基本格式:#^(?P<controller>\w+)/(?P<action>\w+)$#u
     *</pre>
     */
    protected $uriRegex = "";

    protected $uriMergeRegex;

    /**
     * 路由地址对应的正则表达式
     *<B>说明：</B>
     *<pre>
     * 基本格式:#^(?P<controller>\w+)/(?P<action>\w+)$#u
     *</pre>
     */
    protected $actionRegex;

    protected $actionMergeRegex;

    /**
     * uri 替换模板
     *<B>说明：</B>
     *<pre>
     * 基本格式:<controller>/<action>
     * 生成外部url 地址的模板
     *</pre>
     */
    protected $urlTemplate = "";

    /**
     * 全部变量集合
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    protected $uriParams = [];

    /**
     * uri变量名集合
     * @var array
     */
    protected $uriVars = [];

    /**
     * 用户自定义变量集合
     *<B>说明：</B>
     *<pre>
     * 基本格式:['controller'=>'\w+,'action'=>'\w+']
     *</pre>
     * @var array
     */
    protected $params = [];

    /**
     * action抽取的变量
     *<B>说明：</B>
     *<pre>
     * 格式:基本格式:['controller'=>'<controller>,'action'=>'<action>']
     *</pre>
     */
    protected $actionParams = [];

    /**
     * action变量名集合
     * @var array
     */
    protected $actionVars = [];

    /**
     * 默认变量
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var array
     */
    protected $defaults = [];

    /**
     * 参数解析名称
     * @var string
     */
    protected $pvar = 'param';

    /**
     * 参数解析规则配置
     * @var array
     */
    protected $prule = [];

    /**
     * 私有变量集合
     * 私有变量不进入URL参数
     * @var array
     */
    protected $privateVars = [];

    /**
     * @var RouteMatcher
     */
    protected $routeMatcher;

    /**
     * 参数解析规则对象
     * @var ParamRule
     */
    protected $_paramRule;

    /**
     * 初始化状态
     * @var bool
     */
    protected $_initStatus = false;

    /**
     *  请求类型缓存
     * @var array
     */
    protected $_methods = [];

    /**
     * 分组id
     * @var string
     */
    public $groupId = '';

    /**
     * 规则唯一id
     * @var string
     */
    public $ruleId = '';

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

        $this->ruleId = spl_object_hash($this);
    }

    public function getAttributes()
    {
        return get_object_vars($this);
    }

    /**
     * 初始化规则
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    public function init():self
    {
        if ($this->_initStatus) {
            return $this;
        }

        $this->_initStatus = true;

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
        $this->buildPrule($this->prule);
        $this->buildUri();
        $this->buildAction();

        $this->routeMatcher->getCollector()->initRule($this);

        return $this;
    }


    /**
     * 是否已经初始化
     * @return bool
     */
    public function hasInitStatus()
    {
        return $this->_initStatus;
    }

    protected function buildPrule(array $prule):void
    {
        if (!empty($prule)) {
            $this->pvar = $prule['pvar'];
            unset($prule['pvar']);
            $this->prule = $prule;
        }
    }

    public function setRouteMatcher(RouteMatcher $routeMatcher):void
    {
        $this->routeMatcher = $routeMatcher;
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

    public function getCompleteMatch():bool
    {
        return $this->completeMatch;
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

    public function getMethods():array
    {

        if ($this->_initStatus && !empty($this->_methods)) {
            return $this->_methods;
        }

        $methods = [];
        if (!empty($this->method) ) {
            if (is_string($this->method)) {
                if ($this->method === '*') {
                    $methods[] = $this->method;
                } else if (strpos($this->method,'|') !== false) {
                    $methods = explode('|',$this->method);
                } else if (strpos($this->method,',') !== false) {
                    $methods = explode(',',$this->method);
                } else {
                    $methods[] = $this->method;
                }
            } else if (is_array($this->method)) {
                $methods = $this->method;
            }

            $this->_methods = $methods;
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

    /**
     * 验证路由规则(uri)是否有变量
     * @return bool
     */
    public function hasUriVar()
    {
        if (count($this->uriVars) > 0) {
            return true;
        } else {
            return false;
        }
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

    /**
     * 验证路由地址(action)是否有变量
     * @return bool
     */
    public function hasActionVar()
    {
        if (count($this->actionVars) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function asOptions(array $options):self
    {
        if (!empty($options)) {
            foreach ($options as $name=>$value) {
                if ($name === 'prule') {
                    $this->asParamsRule($value);
                } else {
                    $this->{$name} = $value;
                }
            }
        }

        return $this;
    }

    public function getOptions(array $names):array
    {
        $attrs = [];
        if (!empty($names)) {
            foreach ($names as $name) {
                $attrs[$name] = $this->{$name};
            }
        }

        return $attrs;
    }

    public function asParams(array $params = []):self
    {
        $this->params = array_merge($this->params,$params);

        return $this;
    }

    public function getParams():self
    {
        return $this->params;
    }

    public function getUriVars()
    {
        return $this->uriVars;
    }

    public function asDefaults(array $params = []):self
    {
        $this->defaults = array_merge($this->defaults,$params);

        return $this;
    }

    public function getUriMergeRegex()
    {
        return $this->uriMergeRegex;
    }

    /**
     * 去除合并参数两边的关键词
     * @param string $name
     * @param string $value
     * @return string
     */
    public function trimMergeVar(string $name,string $value):string
    {
        $value = trim($value,self::MERGE_VAR_FLAG);
        if ($this->uriParams[$name]['split'] === 'right' ){
            $value = rtrim($value,'/');
        } else if ($this->uriParams[$name]['split'] === 'left' ) {
            $value = ltrim($value,'/');
        }

        return $value;
    }

    public function getMergeActionRegex():string
    {
        return $this->actionMergeRegex;
    }

    public function getActionVars()
    {
        return $this->actionVars;
    }

    public function asParamsRule(array $prule = []):self
    {
        $this->buildPrule($prule);

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
    protected function buildUriParams(string $uri):void
    {
        $this->uriParams = [];
        if (preg_match_all(self::URI_FLAG_REGEX, $uri, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            $matcheParams = [];
            foreach ($matches as $match) {
                if (!empty($match[1][0])) {
                    $name = $match[1][0];
                    $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                } else if (!empty($match[3][0])) {
                    $name = $match[3][0];
                    $pattern = isset($match[4][0]) ? $match[4][0] : '[^\/]+';
                }

                $matcheParams[$name] = $pattern;
            }

            $this->uriVars = $this->buildFlagUriParams($matcheParams);
        }

        $this->buildFlagUriParams($this->params);
    }

    protected function buildFlagUriParams(array $params = []):array
    {
        $var_names = [];
        foreach ($params as $name=>$pattern) {
            // 可选状态
            $optional_status = false;
            if (substr($name,-1) === '?') {
                $name = substr($name,0,strlen($name) - 1);
                $optional_status = true;
            } else if (substr($pattern,-1) === '?') {
                $pattern = substr($pattern,0,strlen($pattern) - 1);
                $optional_status = true;
            }

            // 提取私有变量
            if (substr($name,0,1) === '_') {
                $name = substr($name,1);
                $this->privateVars[$name] = $name;
            }

            if (isset($this->uriParams[$name])) {
                $flagParam = $this->uriParams[$name];
                if ($flagParam['optional'] == true) {
                    $optional_status = true;
                }

                if ($pattern !=='' && $pattern !== '[^\/]+') {

                } else {
                    $pattern = $this->uriParams[$name]['regex'];
                }
            }

            $merge_flag = self::MERGE_VAR_FLAG;

            if ($optional_status === true) {
                if (substr($pattern,-1) === '/') {
                    $regex = substr($pattern,0,strlen($pattern) - 1);
                    $this->uriParams[$name] = ['pattern'=>"((?P<$name>$regex)/)?",'split'=>'right','fist_regex'=>"(?P<$name>{$merge_flag}($pattern)?)",'last_regex'=>"(?P<$name>($pattern)?{$merge_flag})",'regex'=>$regex,'optional'=>true,'name'=>"<$name>/"];
                } else if (substr($pattern,0,1) === '/') {
                    $regex = substr($pattern,1);
                    $this->uriParams[$name] = ['pattern'=>"(/(?P<$name>$regex))?",'split'=>'left','fist_regex'=>"(?P<$name>{$merge_flag}($pattern)?)",'last_regex'=>"(?P<$name>($pattern)?{$merge_flag})",'regex'=>$regex,'optional'=>true,'name'=>"/<$name>"];
                } else {
                    $this->uriParams[$name] = ['pattern'=>"(?P<$name>$pattern)?",'split'=>'','fist_regex'=>"(?P<$name>{$merge_flag}($pattern)?)",'last_regex'=>"(?P<$name>($pattern)?{$merge_flag})",'regex'=>$pattern,'optional'=>true,'name'=>"<$name>"];
                }
            }  else {
                $this->uriParams[$name] = ['pattern'=>"(?P<$name>$pattern)",'split'=>'','fist_regex'=>"(?P<$name>{$merge_flag}($pattern))",'last_regex'=>"(?P<$name>($pattern){$merge_flag})",'regex'=>$pattern,'optional'=>false,'name'=>"<$name>"];
            }

            $var_names[] = $name;
        }

        return $var_names;
    }


    /**
     * 从action抽取参数
     *<B>说明：</B>
     *<pre>
     *  比如,"<controller>/<action>"
     *  返回参数格式:{"controller":"<controller>","action":"<action>"}
     *</pre>
     */
    protected function buildActionParams(string $action):void
    {

        if (preg_match_all(self::ACTION_FLAG_REGEX, $action, $matches)) {
            if (!empty($matches[1][0])) {
                foreach ($matches[1] as $name) {
                    $this->actionParams[$name] = "<$name>";
                    $this->actionVars[] = $name;
                }
            }

            if (!empty($matches[2][0])) {
                foreach ($matches[2] as $name) {
                    $this->actionParams[$name] = "<$name>";
                    $this->actionVars[] = $name;
                }
            }
        }
    }

    public function buildVarName($name):string
    {
        return "<{$name}>";
    }

    public function buildRegex(string $regex,$end = '$#u',$first = '#^'):string
    {
        return $first . $regex . $end;
    }

    protected function buildUri():void
    {
        $this->uri = trim($this->uri,'/');

        $uri = $this->uri;
        // 从uri 提取相关参数
        $this->buildUriParams($uri);
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

        $index = 0;
        $fist_rule = null;
        $last_rule = null;
        $uriVarCount = count($this->uriVars);
        foreach ($this->uriVars as $name) {
            $pattern = $this->uriParams[$name];
            if ($index === 0) {
                $fist_rule = $pattern;
            }

            $replaceParams[$this->buildVarName($name)] = $pattern['pattern'];
            if (($uriVarCount - 1) == $index) {
                $last_rule = $pattern;
            }

            $index++;
        }

        $this->urlTemplate = preg_replace([self::URI_FLAG_REGEX1,self::URI_FLAG_REGEX2], ['<$1>','<$1>'], $uri);
        $uri_regex = '';
        if (empty($this->urlTemplate)) {
            $uri_regex = $uri;
        } else {
            $uri_regex = trim(strtr($this->urlTemplate, $replaceParams), '/');
        }

        $this->uriRegex = $uri_regex;

        // 处理合并路由正则表达式
        if (!empty($fist_rule) && substr($this->uriRegex,0,strlen($fist_rule['pattern'])) == $fist_rule['pattern']) {
            $this->uriMergeRegex = str_replace($fist_rule['pattern'],$fist_rule['fist_regex'],$this->uriRegex);
        } else {
            $this->uriMergeRegex = self::MERGE_VAR_FLAG . $this->uriRegex;
        }

        if (!empty($last_rule) && substr($this->uriMergeRegex,-(strlen($last_rule['pattern']))) == $last_rule['pattern']) {
            $this->uriMergeRegex = str_replace($last_rule['pattern'],$last_rule['last_regex'],$this->uriMergeRegex);
        } else {
            $this->uriMergeRegex = $this->uriMergeRegex . self::MERGE_VAR_FLAG;
        }

    }

    protected function buildAction():void
    {
        if (!is_string($this->action)) {
            return;
        }

        $this->action = trim($this->action, '/');
        $this->actionTemplate = preg_replace([self::URI_FLAG_REGEX1,self::URI_FLAG_REGEX2], ['<$1>','<$1>'], $this->action);
        $this->action = $this->actionTemplate;
        $action = $this->action;

        // 从action 提取相关参数
        $this->buildActionParams($action);

        $fist_rule = null;
        $last_rule = null;

        /** 以下代码生成action正则表达式 **/
        $action_regex = '';
        if (empty($this->actionVars)) {
            $action_regex = $action;
        } else {
            $replaceParams = [];
            $index = 0;
            $actionVarCount = count($this->actionVars);
            foreach ($this->actionVars  as $name) {
                $pattern = $this->uriParams[$name];
                $replaceParams[$this->buildVarName($name)] = $pattern['pattern'];
                if ($index === 0) {
                    $fist_rule = $pattern;
                }

                if (($actionVarCount - 1) == $index) {
                    $last_rule = $pattern;
                }
            }

            $action_regex = strtr($action, $replaceParams);
        }

        $this->actionRegex = $action_regex;


        // 处理合并路由正则表达式
        if (!empty($fist_rule) && substr($this->actionRegex,0,strlen($fist_rule['pattern'])) == $fist_rule['pattern']) {
            $this->actionMergeRegex = str_replace($fist_rule['pattern'],$fist_rule['fist_regex'],$this->actionRegex);
        } else {
            $this->actionMergeRegex = self::MERGE_VAR_FLAG . $this->actionRegex;
        }

        if (!empty($last_rule) && substr($this->actionMergeRegex,-(strlen($last_rule['pattern']))) == $last_rule['pattern']) {
            $this->actionMergeRegex = str_replace($last_rule['pattern'],$last_rule['last_regex'],$this->actionMergeRegex);
        } else {
            $this->actionMergeRegex = $this->actionMergeRegex . self::MERGE_VAR_FLAG;
        }
    }

    /**
     * 解析Uri匹配到的参数
     * @param array $matches
     * @param ?RouteRequest $routeRequest
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
                    if (isset($this->uriParams[$name]) && $this->uriParams[$name]['optional'] === true) {
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


        if ($this->actionTemplate !== '') {
            $url = strtr($this->actionTemplate, $action_replace_params);
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
            $flag_name = $this->buildVarName($name);
            if (!empty($this->pvar) && $name == $this->pvar) {
                $replaceParams[$flag_name] =  $this->getParamRule()->build($urlParams);
            } else if (isset($urlParams[$name])) {
                if (isset($this->defaults[$name]) && strcmp($this->defaults[$name],$urlParams[$name]) === 0) {
                    if ($value['optional'] === true) {
                        $replaceParams[$flag_name] =  "";
                    } else {
                        $replaceParams[$flag_name] = $urlParams[$name];
                    }
                } else {
                    if ($value['optional'] === true) {
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
    public function parseRequest(string $pathinfo,?RouteRequest $routeRequest = null,array $matches = [])
    {
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (empty($matches)) {
            if (!preg_match($this->buildRegex($this->uriRegex,($this->completeMatch ? '$#' : '#')), $pathinfo, $matches)) {
                return false;
            }
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
    public function parseUrL(string $url = '',array $params = [],array $matches = [])
    {
        if ($this->mode === self::PARSING_ONLY) {
            return false;
        }

        // url 最终替换参数
        if (empty($matches)) {
            // 匹配url 正则表达式,
            if ($this->actionRegex == '' || preg_match($this->buildRegex($this->actionRegex), $url, $matches) === 0) {
                return false;
            }
        }

        return $this->parseActionMatches($matches,$url,$params);
    }
}
