<?php
namespace hehe\core\hrouter\base;


use hehe\core\hrouter\base\RuleCollector;

/**
 * 路由基类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
abstract class Router
{
    const MERGE_SPLIT_NAME = '_hehe_';

    /**
     * 是否合并路由
     * @var bool
     */
    protected $mergeRule = false;

    /**
     * 一次合并的路由数量
     * @var int
     */
    protected $mergeLen = 0 ;

    /**
     * 地址中是否添加.html 后续
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string|boolean
     */
    protected $suffix = false;

    /**
     * 地址中是否添加域名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string|boolean
     */
    protected $domain = false;

    /**
     * 是否延迟加载规则
     * @var bool
     */
    protected $lazy = false;

    /**
     * 规则收集器类路径
     * @var string
     */
    protected $collectorClass = '';

    /**
     * 路由规则收集器
     * @var Collector
     */
    public $collector;


    /**
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs 配置参数
     */
    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

    }

    public function getCollectorClass():string
    {
        return $this->collectorClass;
    }

    public function getCollector():Collector
    {
        if (is_null($this->collector)) {
            $class = $this->collectorClass;
            $this->collector = new $class();
        }

        return $this->collector;
    }

    /**
     * 添加路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $rules 路由规则
     * @return void
     */
    public function addRules(array $rules = []):void
    {
        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    public function runCallable(GroupRule $rule)
    {
        $rule->runCallable();
    }

    public function getMergeRule():bool
    {
        return $this->mergeRule;
    }

    public function getMergeLen():int
    {
        return $this->mergeLen;
    }

    /**
     * 获取默认域名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $options
     * @param ?Rule $rule
     * @return string
     **/
    protected function getDomain(array $options = [],?Rule $rule = null):string
    {
        $domain = "";
        if (isset($options['domain'])) {
            $domain = $options['domain'];
        } else if (!is_null($rule)) {
            $domain = $rule->getHost();
        }

        if (empty($domain)) {
            $domain = $this->domain;
        }

        return $domain;
    }

    /**
     * 获取默认后缀
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $uri
     * @param array $options
     * @param ?Rule $rule
     * @return string
     **/
    protected function getSuffix(string $uri,array $options = [],?Rule $rule = null):string
    {

        $urlPath = parse_url($uri,PHP_URL_PATH);
        if (strpos($urlPath,'.') !== false) {
            // uri 存在后缀,直接返回
            return "";
        }

        $suffix = "";
        if (isset($options['suffix'])) {
            $suffix = $options['suffix'];
        } else if (!is_null($rule)) {
            $suffix = $rule->getSuffix();
        }

        if ($suffix === "") {
            $suffix = $this->suffix;
        }

        if (is_bool($suffix) ) {
            $suffix = $suffix === true ? 'html' : "";
        }

        return $suffix;
    }

    /**
     * 合并所有的路由，转换程一条正则表达式
     * @param Rule[] $rules
     * @return array
     */
    protected function mergeUriRulesRegex(array $rules = []):array
    {
        $uriRegexs = [];
        $mergeRegexs = [];
        foreach ($rules as $index=>$rule) {
            $rule->init();
            $uriRegex = $rule->getUriMergeRegex();
            $mergeRegexs[] = $uriRegex;
            $replace = [];
            $search = [];
            foreach ($rule->getUriVars() as $name) {
                $search[] = $rule->buildVarName($name);
                $replace[] = $rule->buildVarName($name . self::MERGE_SPLIT_NAME . $index );
            }

            $uriRegexs[] = str_replace($search,$replace,$rule->buildRegex($uriRegex,($rule->getCompleteMatch() ? '$' : ''),"^"));
        }

        return ['#('. implode("|",$uriRegexs) . ')#',$mergeRegexs];
    }

    /**
     * 合并所有的路由，转换程一条正则表达式
     * @param Rule[] $rules
     * @return array
     */
    protected function mergeActionRulesRegex(array $rules = []):array
    {
        $actionRegexs = [];
        $mergeRegexs = [];
        foreach ($rules as $index=>$rule) {
            $rule->init();
            $actionRegex = $rule->getMergeActionRegex();
            $mergeRegexs[] = $actionRegex;
            $replace = [];
            $search = [];
            foreach ($rule->getActionVars() as $name) {
                $search[] = $rule->buildVarName($name);
                $replace[] = $rule->buildVarName($name . self::MERGE_SPLIT_NAME . $index );
            }

            $actionRegexs[] = str_replace($search,$replace,$rule->buildRegex($actionRegex,'$','^'));
        }

        return ['#('. implode("|",$actionRegexs) . ')#',$mergeRegexs];
    }

    /**
     * 匹配合并路由
     * @param array $rules
     * @param ?RouteRequest|null $routeRequest
     * @param string $cacheKey 缓存key
     * @return array|false
     */
    public function matchMergeUriRules(array $rules = [],?RouteRequest $routeRequest = null,string $cacheKey = '',int $mergeLen = 0)
    {
        if (empty($rules)) {
            return false;
        }

        $ruleSize = count($rules);
        $cacheStauts = $this->getCollector()->isActiveCache($cacheKey,$ruleSize);
        if (!$cacheStauts) {
            // 缓存失效,清空上次缓存
            $this->getCollector()->initKeyCache($cacheKey,$ruleSize);
        }

        // 分隔数组,避免合并字符串字符数量超过限制
        $ruleList = ($mergeLen === 0) ? [$rules] : array_chunk($rules,$mergeLen);

        foreach ($ruleList as $index=>$mergeRules) {
            if (empty($mergeRules)) {
                continue;
            }

            if ($this->getCollector()->hasMergeCache($cacheKey,$index)) {
                $uriRegexs = $this->getCollector()->getMergeCache($cacheKey,$index);
            } else {
                $uriRegexs = $this->mergeUriRulesRegex($mergeRules);
                $this->getCollector()->setMergeCache($cacheKey,$index,$uriRegexs);
            }

            list($regex,$mergeRegexs) = $uriRegexs;

            $pathinfo = $routeRequest->getRoutePathinfo();
            if (!preg_match($regex,Rule::MERGE_VAR_FLAG.$pathinfo . Rule::MERGE_VAR_FLAG,$matches)) {
                //continue;
                $pathinfo = $routeRequest->getFullUrl();
                if (!preg_match($regex,Rule::MERGE_VAR_FLAG.$pathinfo . Rule::MERGE_VAR_FLAG,$matches)) {
                    continue;
                }
            }

            // 分离出正常的参数
            $matchParams = [];
            $rule_index = '';
            foreach ($matches as $key=>$value) {
                if (is_string($key) && $value !== '') {
                    list($name,$rule_index) = explode(self::MERGE_SPLIT_NAME,$key);
                    /** @var Rule $rule */
                    $rule = $mergeRules[$rule_index];
                    $value = $rule->trimMergeVar($name,$value);
                    $matchParams[$name] = $value;
                }
            }

            if ($rule_index === '') {
                $rule_index = array_search(Rule::MERGE_VAR_FLAG. $pathinfo . Rule::MERGE_VAR_FLAG, $mergeRegexs);
            }

            /** @var Rule $rule */
            $rule = $mergeRules[$rule_index];
            return $rule->parseRequest($pathinfo,$routeRequest,$matchParams);
        }

        return false;
    }

    /**
     * 匹配合并路由
     * @param array $rules
     * @param string $uri
     * @param array $params
     * @param string $cacheKey 缓存key
     * @return array|false
     */
    public function matchMergeActionRules(array $rules = [],string $uri = '',array $params = [],string $cacheKey = '',int $mergeLen = 0)
    {
        if (empty($rules)) {
            return false;
        }

        $ruleSize = count($rules);
        $cacheStauts = $this->getCollector()->isActiveCache($cacheKey,$ruleSize);
        if (!$cacheStauts) {
            // 缓存失效,清空上次缓存
            $this->getCollector()->initKeyCache($cacheKey,$ruleSize);
        }

        // 分隔数组,避免合并字符串字符数量超过限制
        $ruleList = ($mergeLen === 0) ? [$rules] : array_chunk($rules,$mergeLen);

        foreach ($ruleList as $index=>$mergeRules) {
            if (empty($mergeRules)) {
                continue;
            }

            if ($this->getCollector()->hasMergeCache($cacheKey,$index)) {
                $actionRegexs = $this->getCollector()->getMergeCache($cacheKey,$index);
            } else {
                $actionRegexs = $this->mergeActionRulesRegex($mergeRules);
                $this->getCollector()->setMergeCache($cacheKey,$index,$actionRegexs);
            }

            list($regex,$mergeRegexs) = $actionRegexs;

            if (!preg_match($regex,Rule::MERGE_VAR_FLAG.$uri.Rule::MERGE_VAR_FLAG,$matches)) {
                continue;
            }

            // 分离出正常的参数
            $matchParams = [];
            $rule_index = '';
            foreach ($matches as $key=>$value) {
                if (is_string($key) && $value !== '') {
                    list($name,$rule_index) = explode(self::MERGE_SPLIT_NAME,$key);
                    /** @var Rule $rule */
                    $rule = $mergeRules[$rule_index];
                    $value = $rule->trimMergeVar($name,$value);
                    $matchParams[$name] = $value;
                }
            }

            if ($rule_index === '') {
                $rule_index = array_search(Rule::MERGE_VAR_FLAG . $uri . Rule::MERGE_VAR_FLAG, $mergeRegexs);
            }

            /** @var Rule $rule */
            $rule = $mergeRules[$rule_index];
            return $rule->parseUrL($uri,$params,$matchParams);
        }

        return false;
    }

    /**
     * 匹配请求路由规则
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Rule[] $rules 路由规则对象
     * @param ?RouteRequest $routeRequest 控制器/方法
     * @return boolean|array
     */
    public function matchUriRules(array $rules = [],?RouteRequest $routeRequest = null)
    {
        $matchResult = false;
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $rule->init();
                if ($rule->getDomain() === true) {
                    $pathinfo = $routeRequest->getFullUrl();
                } else {
                    $pathinfo = $routeRequest->getRoutePathinfo();
                }

                $matchResult = $rule->parseRequest($pathinfo,$routeRequest);
                if ($matchResult !== false) {
                    break;
                }
            }
        }

        return $matchResult;
    }

    /**
     * 匹配生成URL地址路由规则
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param array $rules 匹配的规则列表
     * @param string $uri action 地址
     * @param array $params 参数
     * @return array
     */
    public function matchActionRules(array $rules = [],string $uri,array $params = [])
    {

        $matchResult = false;

        if (!empty($rules)) {
            foreach ($rules as $rule) {
                /** @var Rule $rule */
                $matchResult = $rule->init()->parseUrL($uri, $params);
                if ($matchResult !== false) {
                    break;
                }
            }
        }

        return $matchResult;
    }



    /**
     * 生成URL 地址
     *<B>说明：</B>
     *<pre>
     * 示例1：创建url地址
     * $url = $this->buildUrL('user/login',['id'=>'ok']);
     * $url:user/login?id=ok
     *
     * 示例2：添加后缀
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['suffix'=>true]);
     * or
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['suffix'=>'.html']);
     *
     * $url:user/login.html?id=ok
     *
     * 示例3：添加域名,如果路由规则中已经存在域名，也会显示
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['domain'=>true]);
     * or
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['domain'=>'http://www.baidu.com']);
     *
     * $url:http://www.baidu.com/user/login?id=ok
     *
     * 示例4：添加锚点
     * $url = $this->buildUrL('user/login',['id'=>'ok','#'=>'add']);
     *
     * 示例5：当前页面url
     * $url = $this->buildUrL('',['id'=>'ok','#'=>'add']);
     *
     *</pre>
     * @param string $uri url 地址
     * @param array $params url 参数
     * @param array $options url 配置
     * @return string
     */
    public function buildUrL(string $uri = '',array $params = [],array $options = [])
    {
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#']);

        // 查找后缀
        $suffix = "";
        if (strpos($uri,'.') !== false) {
            list($uri,$suffix) = explode('.',$uri);
        }

        // 查找域名
        $matchResult = $this->matchAction($uri,$params,$options);

        $url = "";
        $matchRule = null;

        if ($matchResult !== false) {
            list($url,$params,$matchRule) = $matchResult;
        } else {
            $url = $uri;
        }

        // 解析url域名
        if (preg_match('/^http(s?):\/\//i',$url) === 0) {
            $domain = $this->getDomain($options,$matchRule);
            $url = $domain != '' ? $domain  . $url : $url;
        }

        // 解析url 文件名后缀
        if ($url !== '') {
            if ($suffix === '') {
                $suffix = $this->getSuffix($url,$options,$matchRule);
            }

            $url = $suffix != '' ? $url  . "." . $suffix : $url;
        }

        // url 参数
        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        // 解析锚点
        $url .= $anchor;

        return $url;
    }

    /**
     * 添加路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Rule $rule 路由规则
     * @return void
     */
    abstract public function addRule(Rule $rule):void;

    /**
     * 解析路由地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param RouteRequest $routeRequest 路由请求对象
     * @return void
     */
    abstract public function parseRequest(RouteRequest $routeRequest):RouteRequest;

    /**
     * 匹配action路由
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $uri url地址
     * @param array $params url参数
     * @param array $options url配置
     * @return array|bool
     */
    abstract public function matchAction(string $uri = '',array $params = [],array $options = []);



}
