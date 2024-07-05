<?php
namespace hehe\core\hrouter;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\Router;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\base\RuleCollector;
use hehe\core\hrouter\easy\EasyRouter;
use hehe\core\hrouter\easy\EasyRule;

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
     * @var array
     */
    public $rules = [];

    /**
     * 当前的分组对象
     * @var GroupRule
     */
    protected $currentGroup;

    /**
     * 默认路由
     * @var string
     */
    protected $defaultRouter = 'EasyRouter';

    /**
     * 路由解析器定义
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $customRouter = [
        // 路由类
        'class'=>EasyRouter::class,
        // url 是否加入上后缀
        'suffix'=>false,// url 地址后缀
        // url 是否加入域名
        'domain'=>false,// 生产url 地址时是否显示域名
    ];

    /**
     * 路由请求定义
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $routerRequest = [
        'class'=>'WebRouterRequest',
    ];

    /**
     * 路由解析器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Router
     */
    protected $_router;

    /**
     * 路由请求类名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $_routerRequestClass = '';

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

    protected function initRules()
    {
        if (!empty($this->rules)) {
            $this->getRouter()->addRules($this->rules);
            $this->rules = [];
        }
    }

    /**
     * 添加路由规则
     * @param string $uri
     * @param string $action
     * @param string $method
     * @param array $options
     * @return EasyRule
     */
    public function addRoute($uri = '',string $action = '',string $method = '',array $options = []):EasyRule
    {
        $easyRule = static::createRule($uri,$action,$method,$options);
        $this->registerRule($easyRule);
        return $easyRule;
    }

    public function registerRule(Rule $rule):void
    {
        $this->rules[] = $rule;
    }

    public function get(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::GET_RULE_METHOD,$options);
    }

    public function post(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::POST_RULE_METHOD,$options);
    }

    public function put(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::PUT_RULE_METHOD,$options);
    }

    public function patch(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::PATCH_RULE_METHOD,$options);
    }

    public function delete(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::DELETE_RULE_METHOD,$options);
    }

    public function head(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::HEAD_RULE_METHOD,$options);
    }

    public function any(string $uri = '',string $action = '',array $options = [])
    {
        return $this->addRoute($uri,$action,RuleCollector::ANY_RULE_METHOD,$options);
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
        Route::register($groupRule,$this);

        return $groupRule;
    }


    /**
     * 获取路由解析对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return Router
     */
    public function getRouter(string $router_key = ''):Router
    {

        if (!is_null($this->_router)) {
            return $this->_router;
        }

        $router_config = $this->customRouter;

        // 路由检查
        if (isset($router_config['class']) && strpos($router_config['class'],'\\') !== false) {// 采用命名空间
            $routerClass = $router_config['class'];
        } else {
            $routerClass = __NAMESPACE__ . '\\' . $router_config['class'];
        }

        /** @var Router $router */
        $router = new $routerClass($router_config);
        $router->addRules(Route::$rules);
        $this->_router = $router;

        return $router;
    }

    /**
     * 设置路由解析器配置
     * @param array $routerConfig 路由解析器配置
     */
    public function setRouterConfig(?array $routerConfig = []):void
    {
        $this->customRouter = $routerConfig;
    }

    /**
     * 创建路由请求对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $router_request_class 路由请求类路径
     * @return RouteRequest
     */
    public function createRouterRequest(?string $router_request_class = ''):RouteRequest
    {
        if (empty($router_request_class)) {
            $class = $this->getRouterRequestClass();
        } else {
            $class = $router_request_class;
        }

        $config = $this->routerRequest;
        unset($config['class']);
        $config['router'] = $this->getRouter();

        return new $class($config);
    }

    /**
     * 获取路由请求类名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return string
     */
    protected function getRouterRequestClass():string
    {
        if (!empty($this->_routerRequestClass)) {
            return $this->_routerRequestClass;
        }

        if (isset($this->routerRequest['class']) && strpos($this->routerRequest['class'],'\\') !== false) {// 采用命名空间
            $routerRequestClass = $this->routerRequest['class'];
        } else {
            $routerRequestClass = __NAMESPACE__ . '\\request\\' . $this->routerRequest['class'];
        }

        $this->_routerRequestClass = $routerRequestClass;

        return $this->_routerRequestClass;
    }

    /**
     * 批量注册路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $rules 路由规则配置
     * @param string $method
     * @return void
     */
    public function addRules(array $rules = [],string $method = ''):void
    {
        $this->getRouter()->addRules($rules,$method);
    }

    /**
     * 注册单个路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $rule 路由规则配置
     * @param string $method 请求方式
     * @return void
     */
    public function addRule(array $rule,string $method = ''):void
    {
        $this->addRules([$rule],$method);
    }

    /**
     * @param $uri
     * @param string $action
     * @param string $method
     * @param array $option
     * @return Rule|EasyRule
     */
    public function register($uri, string $action = '', string $method = '', array $option = [])
	{
        return $this->addRoute($uri,$action,$method,$option);
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
     * @return EasyRule
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
        }

        return new EasyRule($ruleConfig);
    }

    public static function createGroup($uri = '',?callable $callable = null):GroupRule
    {
        if (is_array($uri)) {
            return new GroupRule($uri);
        } else {
            $attrs = [];
            if (is_null($callable)) {
                $attrs['callable'] = $uri;
            } else {
                $attrs['uri'] = $uri;
                $attrs['callable'] = $callable;
            }

            return new GroupRule($attrs);
        }
    }

    /**
     * 解析路由地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array|RouteRequest $routerRequest
     * @return RouteRequest
     */
    public function parseRequest($routerRequest = null):RouteRequest
    {
        $this->initRules();
        if ($routerRequest instanceof RouteRequest) {
            if (is_null($routerRequest->getRouter())) {
                $routerRequest->setRouter($this->getRouter());
            }
        } else {
            $routerRequest = $this->createRouterRequest($routerRequest);
        }

        $routerRequest->parseRequest();

        return $routerRequest;
    }

    /**
     * @param string $url
     * @param array $params
     * @param array $options
     * @return string
     */
    public function buildUrL(string $url = '',array $params = [],array $options = [])
    {
        $this->initRules();

        return $this->getRouter()->buildUrL($url,$params,$options);
    }

}

?>
