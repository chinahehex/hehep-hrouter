<?php
namespace hehe\core\hrouter\fast;

use hehe\core\hrouter\base\Collector;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\Route;

class FastCollector extends Collector
{

    /**
     * action 缓存路由
     * 用于快速定位路由规则
     * @var array[请求类型][url][]
     */
    protected $constantActionRules = [];

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
    protected $constantUriRules = [];

    /**
     * 带变量的路由
     * @var array[请求类型][]
     */
    protected $variableUriRules = [];

    public function addRule(Rule $rule,array $methods = []):Collector
    {
        if (empty($methods)) {
            $methods = $rule->getMethods();
        }

        $uri = $rule->getUri();
        $action = $rule->getAction();

        foreach ($methods as $method) {
            if ($rule->getId() !== '') {
                $this->constantActionRules[$method][$rule->getId()] = $rule;
            }

            $this->variableUriRules[$method][$uri] = $rule;
            $this->variableActionRules[$method][$action] = $rule;
        }

        return $this;
    }

    public function initRule(Rule $rule):void
    {
        $hasUriVar = $rule->hasUriVar();
        $hasActionVar = $rule->hasActionVar();
        $methods = $rule->getMethods();
        $uri = $rule->getUri();
        $action = $rule->getAction();
        $id = $rule->getId();

        $groupRule = null;
        if ($rule->groupId !== '') {
            $groupRule = $this->groups[$rule->groupId];
        }

        foreach ($methods as $method) {
            if ($id !== '') {
                $this->constantActionRules[$method][$id] = $rule;
            }

            // 生成URL缓存
            if (!$hasActionVar) {
                $this->constantActionRules[$method][$action] = $rule;
                unset($this->variableActionRules[$method][$action]);
            } else {
                $this->variableActionRules[$method][$action] = $rule;
            }

            // 解析URL缓存
            if (!$hasUriVar) {
                $this->constantUriRules[$method][$uri] = $rule;
                unset($this->variableUriRules[$method][$uri]);
            } else {
                $this->variableUriRules[$method][$uri] = $rule;
            }

            if (!is_null($groupRule) && !($groupRule->falseGroup)) {
                unset($this->variableUriRules[$method][$uri]);
                unset($this->variableActionRules[$method][$action]);
            }

        }

        // 初始化分组收集器规则
        if (!is_null($groupRule) && !($groupRule->falseGroup)) {
            $groupRule->getCollector()->initGroupSubRule($rule);
        }
    }

    public function initGroupSubRule(Rule $rule):void
    {
        $hasUriVar = $rule->hasUriVar();
        $hasActionVar = $rule->hasActionVar();
        $methods = $rule->getMethods();
        $uri = $rule->getUri();
        $action = $rule->getAction();
        $id = $rule->getId();

        foreach ($methods as $method) {
            if ($id !== '') {
                $this->constantActionRules[$method][$id] = $rule;
            }

            // 生成URL缓存
            if (!$hasActionVar) {
                $this->constantActionRules[$method][$action] = $rule;
                unset($this->variableActionRules[$method][$action]);
            } else {
                $this->variableActionRules[$method][$action] = $rule;
            }

            // 解析URL缓存
            if (!$hasUriVar) {
                $this->constantUriRules[$method][$uri] = $rule;
                unset($this->variableUriRules[$method][$uri]);
            } else {
                $this->variableUriRules[$method][$uri] = $rule;
            }
        }
    }

    protected function getConstantActionRules(string $action):array
    {
        if (isset($this->constantActionRules[Route::GET_METHOD][$action])) {
            return [$this->constantActionRules[Route::GET_METHOD][$action]];
        } else if (isset($this->constantActionRules[Route::ANY_METHOD][$action])) {
            return [$this->constantActionRules[Route::ANY_METHOD][$action]];
        }

        return [];
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
    protected function getVarActionRules(...$methods):array
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

        return array_values($rules);
    }

    protected function getConstantUriRules(RouteRequest $routeRequest):array
    {

        $method = $routeRequest->getMethod();
        $uri = $routeRequest->getRoutePathinfo();
        // 非域名检查验证
        if (isset($this->constantUriRules[$method][$uri])) {
            return [$this->constantUriRules[$method][$uri]];
        } else if (isset($this->constantUriRules[Route::ANY_METHOD][$uri])) {
            return [$this->constantUriRules[Route::ANY_METHOD][$uri]];
        }

        $fullUrl = $routeRequest->getFullUrl();
        // 带域名检查验证
        if (isset($this->constantUriRules[$method][$fullUrl])) {
            return [$this->constantUriRules[$method][$fullUrl]];
        } else if (isset($this->constantUriRules[Route::ANY_METHOD][$fullUrl])) {
            return [$this->constantUriRules[Route::ANY_METHOD][$fullUrl]];
        }

        return [];
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
    protected function getVarUriRules(RouteRequest $routeRequest,...$methods):array
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

        return array_values($rules);
    }

    public function getUriRules(RouteRequest $routeRequest,string $method,string $type = ''):array
    {
        if ($type === 'constant') {
            return $this->getConstantUriRules($routeRequest,$method,$type);
        } else {
            return $this->getVarUriRules($routeRequest,$method);
        }
    }

    public function getActionRules(string $method,string $type = ''):array
    {
        if ($type === 'constant') {
            return $this->getConstantActionRules($method);
        } else {
            return $this->getVarActionRules($method);
        }
    }


}
