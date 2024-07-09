<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\easy\EasyRule;

class RuleCollector
{
    const GET_RULE_METHOD = 'get';
    const POST_RULE_METHOD = 'post';
    const PUT_RULE_METHOD = 'put';
    const DELETE_RULE_METHOD = 'delete';
    const PATCH_RULE_METHOD = 'patch';
    const HEAD_RULE_METHOD = 'head';
    const ANY_RULE_METHOD = '*';
    const DEFAULT_RULE_METHOD = '*';
    const MAP_RULE_METHOD = 'action';
    const REQUEST_RULE_METHOD = 'request';
    const DOMAIN_RULE_METHOD = 'domain';

    public $rules = [];

    /**
     * action 缓存路由
     * 用于快速定位路由规则
     * @var array[请求类型][url][]
     */
    protected $staticActionRules = [];

    /**
     * action 变量路由
     * @var array[]
     */
    protected $variableActionRules = [];

    /**
     * uri 缓存路由
     * 用于快速定位路由规则
     * @var array[请求类型][url][]
     */
    protected $staticUriRules = [];

    /**
     * 带变量的路由
     * @var array[请求类型][]
     */
    protected $variableUriRules = [];

    /**
     * 检测路由有效性
     * @param EasyRule[] $rules
     * @param RouteRequest $routeRequest
     */
    public function checkRules(array $rules,string $method):array
    {
        return array_filter($rules, function ($rule) use ($method) {
            $rule_method = $rule->getArrMethod();
            return (in_array($method,$rule_method) || in_array('*',$rule_method));
        });
    }

    public function addRule(Rule $rule,$methods)
    {
        if (is_string($methods)) {
            $methods = explode(',',$methods);
        }

        $uri_flag = $rule->hasUriFlag();
        $action_flag = $rule->hasActionFlag();

        foreach ($methods as $method) {
            if ($rule->getId() !== '') {
                $this->staticActionRules[$method][$rule->getId()] = $rule;
            }

            // 生成URL缓存
            if (!$action_flag) {
                $this->staticActionRules[$method][$rule->getAction()] = $rule;
            } else {
                $this->variableActionRules[$method][] = $rule;
            }

            // 解析URL缓存
            if (!$uri_flag) {
                $this->staticUriRules[$method][$rule->getUri()] = $rule;
                // 如果是分组
                if ($rule instanceof GroupRule) {
                    $this->variableUriRules[$method][] = $rule;
                }
            } else {
                $this->variableUriRules[$method][] = $rule;
            }

        }

    }

    public function getStaticActionRule(string $action):?EasyRule
    {
        if (isset($this->staticActionRules[self::GET_RULE_METHOD][$action])) {
            return $this->staticActionRules[self::GET_RULE_METHOD][$action];
        } else if (isset($this->staticActionRules[self::ANY_RULE_METHOD][$action])) {
            return $this->staticActionRules[self::ANY_RULE_METHOD][$action];
        }

        return null;
    }

    /**
     * 获取指定请求类型的请求路由
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $methods 请求类型集合
     * @return Rule[]
     */
    public function getVariableActionRules(...$methods):array
    {
        $rules = [];
        foreach ($methods as $method) {
            if (empty($method)) {
                continue;
            }

            $method = strtolower($method);
            if (isset($this->variableActionRules[$method])) {
                $rules = array_merge($rules,$this->variableActionRules[$method]);
            }
        }

        return $rules;
    }

    public function getStaticUriRule(RouteRequest $routeRequest)
    {

        $method = $routeRequest->getMethod();

        $uri = $routeRequest->getPathinfo();
        // 非域名也检查一次
        if (isset($this->staticUriRules[$method][$uri])) {
            return $this->staticUriRules[$method][$uri];
        } else if (isset($this->staticUriRules[self::ANY_RULE_METHOD][$uri])) {
            return $this->staticUriRules[self::ANY_RULE_METHOD][$uri];
        }

        $fullUrl = $routeRequest->getFullUrl();
        // 带域名的检查一次
        if (isset($this->staticUriRules[$method][$fullUrl])) {
            return $this->staticUriRules[$method][$fullUrl];
        } else if (isset($this->staticUriRules[self::ANY_RULE_METHOD][$fullUrl])) {
            return $this->staticUriRules[self::ANY_RULE_METHOD][$fullUrl];
        }

        return null;
    }



    /**
     * 获取指定请求类型的请求路由
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $methods 请求类型集合
     * @return Rule[]
     */
    public function getVariableUriRules(...$methods):array
    {
        $rules = [];
        foreach ($methods as $method) {
            if (empty($method)) {
                continue;
            }

            $method = strtolower($method);
            if (isset($this->variableUriRules[$method])) {
                $rules = array_merge($rules,$this->variableUriRules[$method]);
            }
        }

        return $rules;
    }


}
