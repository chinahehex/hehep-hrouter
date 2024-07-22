<?php
namespace hehe\core\hrouter;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\MatchingResult;
use hehe\core\hrouter\base\RouteCache;
use hehe\core\hrouter\base\RouteMatcher;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\fast\FastRouteMatcher;

/**
 * 路由管理类
 *<B>说明：</B>
 *<pre>
 *  完成URL 解析
 *  支持url 路由
 *</pre>
 */
class RouteManager
{

    /**
     * 路由规则定义
     * @var Rule[]
     */
    public $rules = [];

    /**
     * 路由解析器定义
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $routeMatcher = [
        // 路由类
        'class'=>FastRouteMatcher::class,
        // url 是否加入上后缀
        'suffix'=>false,// url 地址后缀
        // url 是否加入域名
        'domain'=>false,// 生产url 地址时是否显示域名,
        // 是否合并路由解析
        'mergeRule'=>false,
        // 一次合并的条数
        'mergeLen'=>0,
        // 是否延迟加载规则
        'lazy'=>false,
    ];

    /**
     * 路由请求定义
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $routeRequest = [
        'class'=>'WebRouteRequest',
    ];

    /**
     * 是否开启路由缓存
     * @var bool
     */
    protected $onRouteCache = false;

    /**
     * 路由缓存
     * @var array
     */
    protected $routeCache = [
        'routeFile'=>[],
        'cacheDir'=>'',
        'timeout'=>0,
    ];

    /**
     * 路由解析器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var RouteMatcher
     */
    protected $_routeMatcher;

    protected $_routeCache;

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

    /**
     * 路由规则注入路由解析器
     */
    protected function injectRules()
    {

        if ($this->onRouteCache) {
            $routeCache = $this->getRouteCache();
            if ($routeCache->checkCacheStatus()) {
                $routeCache->injectRules();
            } else {
                $this->rulesAddtoRouter();
            }
        } else {
            $this->rulesAddtoRouter();
        }
    }

    /**
     * 注入路由规则至路由解析器
     */
    protected function rulesAddtoRouter()
    {
        /** @var Rule[] $rules */
        $rules = [];
        if (!empty($this->rules)) {
            $rules = array_merge($rules,$this->rules);
            $this->rules = [];
        }

        if (!empty(Route::$rules)) {
            $rules = array_merge($rules,Route::$rules);
            Route::$rules = [];
        }

        foreach ($rules as $rule) {
            $routeMatcher = $this->getRouteMatcher();
            $rule->setRouteMatcher($routeMatcher);
            if ($rule instanceof GroupRule) {
                $routeMatcher->runCallable($rule);
            } else {
                $routeMatcher->addRule($rule);
            }
        }
    }

    /**
     * 添加路由规则
     * @param string $uri
     * @param string $action
     * @param string $method
     * @param array $options
     * @return Rule
     */
    public function addRoute($uri = '',string $action = '',string $method = Route::ANY_METHOD,array $options = []):Rule
    {
        $rule = static::createRule($uri,$action,$method,$options);
        $this->register($rule);

        return $rule;
    }

    public function register(Rule $rule):void
    {
        $this->rules[] = $rule;
    }

    public function get(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::GET_METHOD,$options);
    }

    public function post(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::POST_METHOD,$options);
    }

    public function put(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::PUT_METHOD,$options);
    }

    public function patch(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::PATCH_METHOD,$options);
    }

    public function delete(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::DELETE_METHOD,$options);
    }

    public function head(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::HEAD_METHOD,$options);
    }

    public function any(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,Route::ANY_METHOD,$options);
    }


    /**
     * 创建路由分组
     * @param string $uri
     * @param callable|null $action
     * @return GroupRule
     */
    public function addGroup($uri = '',?callable $action = null):GroupRule
    {
        $groupRule = static::createGroup($uri,$action);
        $this->register($groupRule);

        return $groupRule;
    }


    /**
     * 获取路由解析对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return RouteMatcher
     */
    public function getRouteMatcher():RouteMatcher
    {

        if (!is_null($this->_routeMatcher)) {
            return $this->_routeMatcher;
        }

        $router_config = $this->routeMatcher;

        // 路由检查
        if (isset($router_config['class']) && strpos($router_config['class'],'\\') !== false) {// 采用命名空间
            $routerClass = $router_config['class'];
        } else {
            $routerClass = __NAMESPACE__ . '\\' . $router_config['class'];
        }

        /** @var RouteMatcher $routeMatcher */
        $routeMatcher = new $routerClass($router_config);
        $this->_routeMatcher = $routeMatcher;

        return $this->_routeMatcher;
    }

    /**
     * 设置路由解析器配置
     * @param array $routerConfig 路由解析器配置
     */
    public function setRouteMatcher(array $routerConfig = []):self
    {
        $this->routeMatcher = array_merge($this->routeMatcher,$routerConfig);

        return $this;
    }

    public function setRouteRequest(array $routeRequest):self
    {
        $this->routeRequest = array_merge($this->routeRequest,$routeRequest);

        return $this;
    }


    /**
     * 设置路由缓存参数
     * @param array $routeCache
     */
    public function setRouteCache(array $routeCache): self
    {
        $this->routeCache = array_merge($this->routeCache,$routeCache);
        $this->onRouteCache = true;

        return $this;
    }

    /**
     * 获取路由缓存对象
     * @return array
     */
    public function getRouteCache(): RouteCache
    {
        if (!is_null($this->_routeCache)) {
            return $this->_routeCache;
        }

        $config = $this->routeCache;
        $config['routeMatcher'] = $this->getRouteMatcher();

        $this->_routeCache = new RouteCache($config);

        return $this->_routeCache;
    }

    /**
     * 创建路由请求对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param ?string $routeRequestClass 路由请求类路径
     * @return RouteRequest
     */
    public function createRouteRequest(?string $routeRequestClass = ''):RouteRequest
    {
        if (!empty($routeRequestClass)) {
            $class = $routeRequestClass;
        } else {
            $class = $this->routeRequest['class'];
            if (strpos($class,'\\') === false) {// 采用命名空间
                $class = __NAMESPACE__ . '\\requests\\' . $class;
            }
        }

        $routeRequestConfig = $this->routeRequest;
        unset($routeRequestConfig['class']);

        return new $class($routeRequestConfig);
    }

    /**
     * 批量注册路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $rules 路由规则配置
     * @return void
     */
    public function addRules(array $rules = []):void
    {
        $this->getRouteMatcher()->addRules($rules);
    }

    /**
     * 注册单个路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Rule $rule 路由规则配置
     * @param string $method 请求方式
     * @return void
     */
    public function addRule(Rule $rule):void
    {
        $this->addRules([$rule]);
    }

    /**
     * 生成路由规则对象
     *<B>说明：</B>
     *<pre>
     *  初始化路由规则
     *</pre>
     * @param array|string $uri 路由规则配置
     * @param string $action 正则规则
     * @param string $method 正则规则
     * @param array $options 正则规则
     * @return Rule
     */
    public static function createRule($uri = '' ,string $action = '',string $method = '',array $options = []):Rule
    {
        $ruleConfig = [];
        if (is_string($uri)) {
            $ruleConfig[Rule::RULE_ACTION_NAME] = $action;
            $ruleConfig[Rule::RULE_URI_NAME] = $uri;
            $ruleConfig[Rule::URL_METHOD_NAME] = $method;
            $ruleConfig = array_merge($ruleConfig,$options);
        } else {
            $ruleConfig = $uri;
            if (!isset($ruleConfig[Rule::URL_METHOD_NAME])) {
                $ruleConfig[Rule::URL_METHOD_NAME] = $method;
            }
        }

        return new Rule($ruleConfig);
    }

    public static function createGroup($uri = '',?callable $callable = null):GroupRule
    {
        if (is_array($uri)) {
            if (!isset($uri['callable'])) {
                $uri['callable'] = $callable;
            }

            return new GroupRule($uri);
        } else {
            $attrs = [];
            if ($uri instanceof \Closure) {
                $attrs['callable'] = $uri;
            } else if (is_string($uri)) {
                $attrs['uri'] = $uri;
                $attrs['callable'] = $callable;
            }

            return new GroupRule($attrs);
        }
    }

    public static function make($route = ''):self
    {
        $routeConfig = [];
        if (!empty($route)) {
            if (is_string($route) ) {
                $routeConfig['routeRequest'] = ['class'=>$route];
            } else if (is_array($route)) {
                $routeConfig = $route;
            }
        }

        return new static($routeConfig);
    }

    /**
     * 解析路由地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array|RouteRequest $routeRequest
     * @return MatchingResult
     */
    public function parseRequest($routeRequest = null):MatchingResult
    {
        // 注入路由规则
        $this->injectRules();

        // 创建路由请求对象
        if ($routeRequest instanceof RouteRequest) {
            if (is_null($routeRequest->getRouteManager())) {
                $routeRequest->setRouteManager($this);
            }
        } else {
            $routeRequest = $this->createRouteRequest($routeRequest);
            $routeRequest->setRouteManager($this);
        }

        // 匹配路由规则
        $routeMatcher = $this->getRouteMatcher();
        $matchResult = $routeMatcher->matchRequest($routeRequest);
        if ($matchResult !== false) {
            $matchingResult = new MatchingResult($matchResult[0],$matchResult[1],$matchResult[2]);
        } else {
            // 匹配不到路由规则
            $matchingResult = new MatchingResult($routeRequest->getRoutePathinfo());
        }

        $routeRequest->setMatchingResult($matchingResult);

        // 路由缓存处理
        if ($this->onRouteCache) {
            $routeCache = $this->getRouteCache();
            if (!$routeCache->checkCacheStatus()) {
                $routeCache->writeRules();
            }
        }

        return $matchingResult;
    }

    /**
     * @param string $url
     * @param array $params
     * @param array $options
     * @return string
     */
    public function buildUrL(string $url = '',array $params = [],array $options = [])
    {
        // 注入路由规则
        $this->injectRules();

        // 生成url地址
        $matchResult = $this->getRouteMatcher()->buildUrL($url,$params,$options);

        // 路由缓存处理
        if ($this->onRouteCache) {
            $routeCache = $this->getRouteCache();
            if (!$routeCache->checkCacheStatus()) {
                $routeCache->writeRules();
            }
        }

        return $matchResult;
    }

}

?>
