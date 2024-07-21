<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\Route;

class GroupRule extends Rule
{

    const ROUTE_FLAG_NAME = '_hroute';

    /**
     * 是否伪分组
     * 伪分组只负责统一配置子路由，不负责验证
     * @var bool
     */
    public $falseGroup = false;

    /**
     * @var Rule[]
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
     * 分组规则收集器
     * @var Collector
     */
    public $collector;

    /**
     * 分组唯一标识
     * @var string
     */
    public $gid = '';

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->gid = $this->ruleId;
    }

    public function getCollector():Collector
    {
        if (is_null($this->collector)) {
            $class = $this->routeMatcher->getCollectorClass();
            $this->collector = new $class();
        }

        return $this->collector;
    }

    /**
     * 添加路由规则器至分组路由器
     * @param Rule $rule
     */
    public function addSubRule(Rule $rule):void
    {
        $this->subRules[] = $rule;
        $rule->asOptions(['groupId'=>$this->gid]);
    }

    public function getSubRules():array
    {
        return $this->subRules;
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

    public function asFalseGroup(bool $falseGroup = true):void
    {
        $this->falseGroup = $falseGroup;
    }

    public function asMergeRule(int $length = 0,bool $mergeRule = true):self
    {
        $this->mergeRule = $mergeRule;
        $this->mergeLen = $length;

        return $this;
    }

    /**
     * 分组的规则同步至子路由规则
     */
    protected function groupOptionAddtoSubRule()
    {

        $get_attributes = ['uri','action','method','suffix','domain','host','params','id','defaults'];

        // 开始同步分组配置给rule
        if (count($this->subRules) > 0) {
            $variableMethods = [];
            foreach ($this->subRules as $rule) {
                $options = $rule->getOptions($get_attributes);
                $opts = [];
                foreach ($options as $name=>$value) {
                    if ($name === 'uri') {
                        if ($this->uri !=='' && substr($value,0,1) !== '/') {
                            if (!empty($value)) {
                                $value = $this->uri . '/' . $value;
                            } else {
                                $value = $this->uri;
                            }
                        }
                    } else if ($name === 'action') {
                        if (!empty($value) && $this->prefix !== '' && substr($value,0,1) !== '/') {
                            $value = $this->prefix . $value;
                        }
                    }else if ($name === 'method') {
                        if (empty($value) && $this->method !== '') {
                            $value = $this->method;
                        }
                    } else if ($name === 'suffix') {
                        if (empty($value) && !empty($this->suffix)) {
                            $value = $this->suffix;
                        }
                    } else if ($name === 'params') {
                        if (!empty($this->params)) {
                            $value = array_merge($this->params,$value);
                        }
                    } else if ($name === 'domain') {
                        if ($this->domain === true && $value !== true) {
                            $value = $this->domain;
                        }
                    } else if ($name === 'host') {
                        if ($this->host !== '' && $value === '') {
                            $value = $this->host;
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

                $rule->asOptions($opts)->setRouteMatcher($this->routeMatcher);
                $variableMethods = array_merge($variableMethods,$rule->getMethods());
            }

            if (!empty($variableMethods)) {
                $this->method = implode(',',array_unique($variableMethods));
            } else {
                $this->method = '*';
            }

        }
    }

    /**
     * 分组规则转普通路由规则
     * @return Rule
     */
    protected function groupToRule():Rule
    {
        $get_attributes = ['uri','action','method','suffix','domain','host','params','id','defaults','router'];
        $rule = new Rule();
        $rule->asOptions($this->getOptions($get_attributes));

        return $rule;
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

    protected function initGroup()
    {
        // 同步路由解释器合并路由信息
        if ($this->routeMatcher->getMergeRule() && $this->mergeRule === false) {
            $this->mergeRule = true;
            $this->mergeLen = $this->routeMatcher->getMergeLen();
        }

        $this->groupOptionAddtoSubRule();

        $this->uri = $this->uri . $this->buildVarName(self::ROUTE_FLAG_NAME . ':.*?');
        if ($this->action !== '') {
            if (substr($this->action,0,1) !== '/') {
                $this->action = $this->action . $this->buildVarName(self::ROUTE_FLAG_NAME);
            }
        } else {
            $this->action = $this->uri;
        }

        $this->routeMatcher->getCollector()->addGroup($this);

        if ($this->falseGroup) {
            // 添加子路由至路由解析器
            $this->routeMatcher->addRules($this->subRules);
            // 分组转普通规则器
            $this->routeMatcher->addRule($this->groupToRule());
        } else {
            // 添加当前分组至路由解析器
            $this->routeMatcher->addRule($this);
            // 添加子路由至路由解析器
            $this->routeMatcher->addRules($this->subRules);
            // 添加子路由至分组路由收集器
            $this->getCollector()->addRules($this->subRules);
        }
    }

    public function parseRequest(string $pathinfo,?RouteRequest $routeRequest = null,array $matches = [])
    {
        if (empty($matches)) {
            // 验证此路由分组规则器
            if (!preg_match($this->buildRegex($this->uriRegex), $pathinfo, $matches)) {
                return false;
            }
        }

        $matchResult = false;
        $request_method = $routeRequest->getMethod();

        // 执行静态缓存路由
        $constantUriRules = $this->getCollector()->getUriRules($routeRequest,$routeRequest->getRoutePathinfo(),'constant');
        if (!empty($constantUriRules)) {
            $matchResult = $this->routeMatcher->matchUriRules($constantUriRules,$routeRequest);
        }

        if ($matchResult === false) {
            foreach ([$request_method,Route::ANY_METHOD] as $method) {
                $rules = $this->getCollector()->getUriRules($routeRequest,$method);
                if ($this->mergeRule) {
                    $matchResult = $this->routeMatcher->matchMergeUriRules($rules,$routeRequest,$this->gid . '.uri.' . $method,$this->mergeLen);
                } else {
                    $matchResult = $this->routeMatcher->matchUriRules($rules,$routeRequest);
                }

                if ($matchResult !== false) {
                    break;
                }
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
    public function parseUrL(string $url = '',array $params = [],array $matches = [])
    {
        if (empty($matches)) {
            // 匹配url 正则表达式,
            if ($this->actionRegex == '' || preg_match($this->buildRegex($this->actionRegex), $url, $matches) === 0) {
                return false;
            }
        }

        $matchResult = false;

        // 执行静态缓存路由
        $constantActionRules = $this->getCollector()->getActionRules($url,'constant');
        if (!empty($constantActionRules)) {
            $matchResult = $this->routeMatcher->matchActionRules($constantActionRules,($constantActionRules[0])->getAction(),$params);
        }

        if ($matchResult === false) {
            foreach ([Route::GET_METHOD,Route::ANY_METHOD] as $method) {
                // 匹配分组路由规则器
                $rules = $this->getCollector()->getActionRules($method);
                if ($this->mergeRule) {
                    $matchResult = $this->routeMatcher->matchMergeActionRules($rules,$url,$params,$this->gid . '.act.' . $method,$this->mergeLen);
                } else {
                    $matchResult = $this->routeMatcher->matchActionRules($rules,$url,$params);
                }

                if ($matchResult !== false) {
                    break;
                }
            }
        }


        if ($matchResult === false) {
            $matchResult = $this->parseActionMatches($matches,$url,$params);
        }

        return $matchResult;
    }


}
