<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\easy\EasyRule;

class RuleCollection
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

    public function addRule(Rule $rule,$methods)
    {
        if (is_string($methods)) {
            $methods = explode(',',$methods);
        }

        foreach ($methods as $method) {
            $this->rules[$method][] = $rule;
        }

        $this->addCacheRule($rule);
    }

    /**
     * 路由导航
     *<B>说明：</B>
     *<pre>
     *  解析出请求中的模块，控制器，方法名称
     *</pre>
     * @param string $methods 模块/控制器/方法
     * @return Rule[]
     */
    public function getRules(...$methods):array
    {
        $rules = [];
        foreach ($methods as $method) {
            if (empty($method)) {
                continue;
            }

            $method = strtolower($method);
            if (isset($this->rules[$method])) {
                $rules = array_merge($rules,$this->rules[$method]);
            }
        }

        return $rules;
    }

    public function getUrlCacheRule(string $key):?EasyRule
    {
        if (isset($this->rules[self::MAP_RULE_METHOD][$key])) {
            return $this->rules[self::MAP_RULE_METHOD][$key];
        } else {
            return null;
        }
    }

    public function getCacheReuqestRule(RouteRequest $routeRequest)
    {
        // 先从域名缓存中读取有效规则
//        $domain_key = $routeRequest->getFullUrl();
//        if (isset($this->rules[self::DOMAIN_RULE_METHOD][$domain_key])
//            && $this->checkRule($this->rules[self::DOMAIN_RULE_METHOD][$domain_key])) {
//            return $this->rules[self::DOMAIN_RULE_METHOD][$domain_key];
//        }

        $request_key = $routeRequest->getPathinfo();
        if (isset($this->rules[self::REQUEST_RULE_METHOD][$request_key])) {
            $rules = $this->checkRules($this->rules[self::REQUEST_RULE_METHOD][$request_key],$routeRequest->getMethod());
            return isset($rules[0]) ?  $rules[0] : null;
        }

        return null;
    }

    /**
     * 检测路由有效性
     * @param EasyRule[] $rules
     * @param RouteRequest $routeRequest
     */
    public function checkRules(array $rules,string $method):array
    {
        $ruleList = [];
        foreach ($rules as $rule) {
            $methods = $rule->getArrMethod();
            if (in_array($method,$methods)){
                $ruleList[] = $rule;
            }
        }

        return $ruleList;
    }

    /**
     * @param EasyRule $rule
     */
    public function addCacheRule(Rule $rule):void
    {
        $actionId = $rule->getActionId();
        if ($actionId !== '') {
            $this->rules[self::MAP_RULE_METHOD][$actionId] = $rule;
        }

        $uriId = $rule->getUriId();
        if ($uriId !== '') {
            $this->rules[self::REQUEST_RULE_METHOD][$uriId][] = $rule;
        }

    }

    public function getGetRules(bool $getAay = false)
    {
        return $this->getMethodRules($getAay,self::GET_RULE_METHOD);
    }

    public function getMethodRules(bool $getAay = false,...$methods)
    {
        if ($getAay) {
            $methods[] = self::ANY_RULE_METHOD;
        }

        return $this->getRules(...$methods);
    }

}
