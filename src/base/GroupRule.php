<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\easy\EasyRule;
use hehe\core\hrouter\Route;

class GroupRule extends EasyRule
{
    protected $completeMatch = false;

    /**
     * @var EasyRule[]
     */
    public $subRules = [];

    /**
     * 子路由闭包
     * @var callable
     */
    protected $callable;

    /**
     * action 的前缀
     * @var string
     */
    protected $prefix = '';

    /**
     * uri原始字符串
     * @var string
     */
    protected $rawUri = '';

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

    protected $variableUriRules = [];
    protected $variableActionRules = [];

    protected $_hroute = false;
    protected $_hroute_name = '_hroute';

    /**
     * 合并路由缓存
     * @var array
     */
    protected $mergeRuleRegexCache = [];

    public function __construct($attrs = [])
    {
        parent::__construct($attrs);

        $this->rawUri = $this->uri;
    }

    public function initGroup()
    {
        if ($this->completeMatch === false) {
            $this->uri = $this->uri . $this->buildFlagName($this->_hroute_name . ':.*');
            if ($this->action !== '') {
                if (substr($this->action,0,1) !== '/') {
                    $this->action = $this->action . $this->buildFlagName($this->_hroute_name);
                } else {
                    $this->_hroute = true;
                }
            } else {
                $this->action = $this->uri;
            }
        }

        $this->init();
        $this->syncRules();
    }

    /**
     * 执行闭包函数,收集路由规则器
     */
    public function runCallable()
    {
        if ($this->callable instanceof \Closure) {
            $prv_group = Route::$currentGroup;
            Route::$currentGroup = $this;
            ($this->callable)();
            Route::$currentGroup = $prv_group;
        }

        $this->initGroup();
    }

    public function asMergeRule(int $length = 0,bool $mergeRule = true):self
    {
        $this->mergeRule = $mergeRule;
        $this->mergeLen = $length;

        return $this;
    }

    /**
     * 分组的规则同步至路由规则器
     */
    protected function syncRules()
    {
        $rule_attributes = ["uri","action","method","suffix","domain","uriParams","id","defaults"];
        // 开始同步分组配置给rule
        if (count($this->subRules) > 0) {
            $variableMethods = [];
            $variableUriRules = [];
            foreach ($this->subRules as $rule) {
                $options = $rule->getOptions($rule_attributes);
                $opts = [];
                foreach ($options as $name=>$value) {
                    if ($name === 'uri') {
                        if ($this->rawUri !=='' && substr($value,0,1) !== '/') {
                            if (!empty($value)) {
                                $value = $this->rawUri . '/' . $value;
                            } else {
                                $value = $this->rawUri;
                            }
                        }
                    } else if ($name === 'action') {
                        if (!empty($value) && $this->prefix !== '' && substr($value,0,1) !== '/') {
                            $value = $this->prefix . $value;
                        }
                    } else if ($name === 'method') {
                        if (empty($value) && $this->method !== '') {
                            $value = $this->method;
                        }
                    } else if ($name === 'suffix') {
                        if (empty($value) && !empty($this->suffix)) {
                            $value = $this->suffix;
                        }
                    } else if ($name === 'uriParams') {
                        if (!empty($this->uriParams)) {
                            $value = array_merge($this->uriParams,$value);
                        }
                    } else if ($name === 'defaults') {
                        if (!empty($this->defaults)) {
                            $value = array_merge($this->defaults,$value);
                        }
                    } else if ($name === 'id') {
                        if (!empty($value) && !empty($this->id)) {
                            $value = $this->id . $value;
                        }
                    }

                    $opts[$name] = $value;
                }

                $rule->asOptions($opts)->init();
                if ($rule->hasUriFlag()) {
                    $this->addVariableUriRule($rule);
                    $variableMethods = array_merge($variableMethods,$rule->getArrMethod());
                }

                if ($rule->hasActionFlag()) {
                    $this->addVariableActionRule($rule);
                }
            }

            $this->method = implode(',',$variableMethods);
            $this->router->addRule($this);
            $this->router->addRules($this->subRules);
        }
    }

    /**
     * 添加路由规则器至分组路由器
     * @param Rule $rule
     */
    public function addRule(Rule $rule):void
    {
        $this->subRules[] = $rule;
    }

    protected function addVariableUriRule(Rule $rule)
    {
        $methods = $rule->getArrMethod();
        if (empty($methods)) {
            $methods[] = RuleCollector::ANY_RULE_METHOD;
        }

        foreach ($methods as $method) {
            $this->variableUriRules[$method][] = $rule;
        }
    }

    protected function addVariableActionRule(Rule $rule)
    {
        $methods = $rule->getArrMethod();
        if (empty($methods)) {
            $methods[] = RuleCollector::ANY_RULE_METHOD;
        }

        foreach ($methods as $method) {
            $this->variableActionRules[$method][] = $rule;
        }
    }


    public function asPrefix(string $prefix):self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix():string
    {
        return $this->prefix;
    }

    /**
     * 合并所有的路由，转换程一条正则表达式
     * @param Rule[] $rules
     * @return string
     */
    protected function mergeUriRulesRegex(array $rules = []):string
    {
        $uriRegexs = [];
        foreach ($rules as $index=>$rule) {
            $rule->init();
            $uriRegex = $this->buildRegex($rule->getUriRegex(),($this->completeMatch ? '$' : ''),"^");
            $uriParams = array_keys($rule->getUriParams());
            $replace = [];
            $search = [];
            foreach ($uriParams as $name) {
                $search[] = $this->buildFlagName($name);
                $replace[] = $this->buildFlagName($name . '_hehe_' . $index );
            }

            $uriRegexs[] = str_replace($search,$replace,$uriRegex);
        }

        return '#('. implode("|",$uriRegexs) . ')#';
    }

    /**
     * 合并所有的路由，转换程一条正则表达式
     * @param Rule[] $rules
     * @return string
     */
    protected function mergeActionRulesRegex(array $rules = []):string
    {
        $actionRegexs = [];
        foreach ($rules as $index=>$rule) {
            $rule->init();
            $actionRegex = $rule->getActionRegex();
            $actionParams = array_keys($rule->getActionParams());
            $replace = [];
            $search = [];
            foreach ($actionParams as $name) {
                $search[] = $this->buildFlagName($name);
                $replace[] = $this->buildFlagName($name . '_hehe_' . $index );
            }

            $actionRegexs[] = str_replace($search,$replace,$actionRegex);
        }

        return '#('. implode("|",$actionRegexs) . ')#';
    }

    /**
     * 匹配合并路由
     * @param array $rules
     * @param RouteRequest|null $routeRequest
     * @return array|false
     */
    protected function matchMergeUriRules(array $rules = [],?RouteRequest $routeRequest = null,string $cacheName = '')
    {

        // 分隔数组,避免合并字符串字符数量超过限制
        if ($this->mergeLen === 0) {
            $ruleList = array_chunk($rules,count($rules));
        } else {
            $ruleList = array_chunk($rules,$this->mergeLen);
        }

        foreach ($ruleList as $index=>$myRules) {
            $cache_name = $cacheName . $index;
            if (isset($this->mergeRuleRegexCache[$cache_name])) {
                $regex = $this->mergeRuleRegexCache[$cache_name];
            } else {
                $regex = $this->mergeUriRulesRegex($myRules);
            }

            if (!preg_match($regex,$routeRequest->getPathinfo(),$matches)) {
                continue;
            }

            // 分离出正常的参数
            $match_params = [];
            $rule_index = '';
            foreach ($matches as $key=>$value) {
                if (is_string($key) && $value !== '') {
                    list($name,$rule_index) = explode("_hehe_",$key);
                    $match_params[$name] = $value;
                }
            }

            /** @var EasyRule $rule */
            $rule = $myRules[$rule_index];

            return $rule->parseUriMatches($match_params,$routeRequest);
        }

        return false;
    }

    /**
     * 匹配合并路由
     * @param array $rules
     * @param RouteRequest|null $routeRequest
     * @return array|false
     */
    protected function matchMergeActionRules(array $rules = [],string $uri = '',array $params = [],string $cacheName = '')
    {


        if ($this->mergeLen === 0) {
            $ruleList = array_chunk($rules,count($rules));
        } else {
            $ruleList = array_chunk($rules,$this->mergeLen);
        }

        foreach ($ruleList as $index=>$myRules) {
            $cache_name = $cacheName . $index;
            if (isset($this->mergeRuleRegexCache[$cache_name])) {
                $regex = $this->mergeRuleRegexCache[$cache_name];
            } else {
                $regex = $this->mergeActionRulesRegex($myRules);
            }

            if (!preg_match($regex,$uri,$matches)) {
                continue;
            }

            // 分离出正常的参数
            $match_params = [];
            $rule_index = '';
            foreach ($matches as $key=>$value) {
                if (is_string($key) && $value !== '') {
                    list($name,$rule_index) = explode("_hehe_",$key);
                    $match_params[$name] = $value;
                }
            }

            /** @var EasyRule $rule */
            $rule = $myRules[$rule_index];

            return $rule->parseActionMatches($match_params,$uri,$params);
        }

        return false;
    }

    public function parseRequest(string $pathinfo,?RouteRequest $routeRequest = null)
    {
        // 验证此路由分组规则器
        if (!preg_match($this->buildRegex($this->uriRegex), $pathinfo, $matches)) {
            return false;
        }

        $matchResult = false;
        // 匹配分组路由规则器
        if (isset($this->variableUriRules[$routeRequest->getMethod()])) {
            $rules = $this->variableUriRules[$routeRequest->getMethod()];
            if ($this->mergeRule) {
                $matchResult = $this->matchMergeUriRules($rules,$routeRequest,'uri.' . $routeRequest->getMethod());
            } else {
                $matchResult = $this->router->matchUriRules($rules,$routeRequest);
            }
        }

        if ($matchResult === false && isset($this->variableUriRules[RuleCollector::ANY_RULE_METHOD])) {
            $rules = $this->variableUriRules[RuleCollector::ANY_RULE_METHOD];
            if ($this->mergeRule) {
                $matchResult = $this->matchMergeUriRules($rules,$routeRequest,'uri.' . RuleCollector::ANY_RULE_METHOD);
            } else {
                $matchResult = $this->router->matchUriRules($rules,$routeRequest);
            }
        }

        if ($matchResult === false) {
            $matchResult = $this->parseUriMatches($matches,$routeRequest);
        }

        return $matchResult;
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

        // url 最终替换参数
        $matches = [];
        // 匹配url 正则表达式,
        if ($this->actionRegex == '' || preg_match($this->buildRegex($this->actionRegex), $url, $matches) === 0) {
            return false;
        }

        $matchResult = false;
        // 匹配分组路由规则器
        if (isset($this->variableActionRules[RuleCollector::GET_RULE_METHOD])) {
            $rules = $this->variableActionRules[RuleCollector::GET_RULE_METHOD];
            if ($this->mergeRule) {
                $matchResult = $this->matchMergeActionRules($rules,$url,$params,"action." . RuleCollector::GET_RULE_METHOD);
            } else {
                $matchResult = $this->router->matchActionRules($rules,$url,$params);
            }
        }

        if ($matchResult === false && isset($this->variableActionRules[RuleCollector::ANY_RULE_METHOD])) {
            $rules = $this->variableActionRules[RuleCollector::ANY_RULE_METHOD];
            if ($this->mergeRule) {
                $matchResult = $this->matchMergeActionRules($rules,$url,$params,"action." . RuleCollector::ANY_RULE_METHOD);
            } else {
                $matchResult = $this->router->matchActionRules($rules,$url,$params);
            }
        }

        if ($matchResult === false) {
            $matchResult = $this->parseActionMatches($matches,$url,$params);
        }

        return $matchResult;
    }


}
