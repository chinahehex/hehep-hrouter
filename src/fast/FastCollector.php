<?php
namespace hehe\core\hrouter\fast;

use hehe\core\hrouter\base\Collector;
use hehe\core\hrouter\base\RouteMatcher;
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

    public function addRule(Rule $rule):void
    {
        $this->allRules[$rule->ruleId] = $rule;

        if ($rule->isGroupRule() && isset($this->groups[$rule->groupId])) {
            // 分组路由
            $this->addGroupRule($rule);
        } else {
            $this->addRouteRule($rule);
        }

    }

    protected function addRouteRule(Rule $rule)
    {
        if ($rule->hasInitStatus()) {
            return ;
        }

        $lazyStatus = $this->routeMatcher->getLazy();
        if (!$lazyStatus) {
            $rule->init();
            return ;
        }

        // 以下代码实现延迟加载逻辑
        $methods = $rule->getMethods();

        // 普通路由
        $uri = $rule->getUri();
        $action = $rule->getAction();
        foreach ($methods as $method) {
            if ($rule->getId() !== '') {
                $this->constantActionRules[$method][$rule->getId()] = $rule;
            }

            // 延迟加载
            $this->variableUriRules[$method][$uri] = $rule;
            $this->variableActionRules[$method][$action] = $rule;
        }
    }

    protected function addGroupRule(Rule $rule)
    {
        $groupRule = $this->groups[$rule->groupId];
        $groupRule->getCollector()->addRule($rule);
    }

    public function initRule(Rule $rule):void
    {
        $this->allRules[$rule->ruleId] = $rule;

        $this->intiRouteRule($rule);

        // 初始化分组收集器规则
        if ($rule->isGroupRule() && isset($this->groups[$rule->groupId])) {
            $groupRule = $this->groups[$rule->groupId];
            $groupRule->getCollector()->initRule($rule);
        }
    }

    protected function intiRouteRule(Rule $rule)
    {
        $hasUriVar = $rule->hasUriVar();
        $hasActionVar = $rule->hasActionVar();
        $methods = $rule->getMethods();
        $uri = $rule->getUri();
        $action = $rule->getAction();
        $id = $rule->getId();

        $groupRule = null;
        if ($rule->isGroupRule() && isset($this->groups[$rule->groupId])) {
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

    public function buildRouteCache(array $caches):array
    {

        $caches = $this->buildVarRouteCache($caches);
        if ($this->routeMatcher->getMergeRule()) {
            $caches['mergeCaches'] = $this->buildMergeCache();
        }

        return $caches;
    }

    public function restoreRouteCache(array $caches):void
    {

        $this->restoreVarRouteCache($caches);
        if ($this->routeMatcher->getMergeRule()) {
            $this->restoreMergeCache($caches);
        }

    }

    protected function buildVarRouteCache(array $caches):array
    {
        foreach (['constantActionRules','variableActionRules','constantUriRules','variableUriRules'] as $name) {
            $cacheItems = [];
            foreach ($this->{$name} as $method => $rules) {
                /** @var Rule $rule */
                foreach ($rules as $key=>$rule) {
                    $cacheItems[$method][$key] = $rule->ruleId;
                }
            }

            $caches[$name] = $cacheItems;
        }

        return $caches;
    }

    protected function restoreVarRouteCache(array $caches):void
    {
        foreach (['constantActionRules','variableActionRules','constantUriRules','variableUriRules'] as $name) {
            $cacheItems = [];
            foreach ($caches[$name] as $method => $rules) {
                /** @var Rule $rule */
                foreach ($rules as $key=>$ruleId) {
                    $rule = $this->allRules[$ruleId];
                    $cacheItems[$method][$key] = $rule;
                }
            }

            $this->{$name} = $cacheItems;
        }
    }

    public function buildMergeCache(string $prefix = ''):array
    {
        $cacheItems = [];
        foreach (['variableActionRules','variableUriRules'] as $name) {
            foreach ($this->{$name} as $method => $rules) {
                $rules = array_values($rules);
                $mergeLen = $this->routeMatcher->getMergeLen();
                $ruleList = ($mergeLen === 0) ? [$rules] : array_chunk($rules,$mergeLen);
                if ($name == 'variableActionRules') {
                    $key = 'act.' . $method;
                } else if ($name == 'variableUriRules') {
                    $key = 'uri.' . $method;
                }

                if ($prefix !== '') {
                    $key = $prefix . '.' . $key;
                }

                $cacheItems[$key]['-'] = count($rules);
                foreach ($ruleList as $index=>$mergeRules) {
                    if ($name == 'variableActionRules') {
                        $uriRegexs = $this->routeMatcher->mergeActionRulesRegex($mergeRules);
                    } else if ($name == 'variableUriRules') {
                        $uriRegexs = $this->routeMatcher->mergeUriRulesRegex($mergeRules);
                    }

                    $cacheItems[$key][$index] = $uriRegexs;
                }
            }
        }

        // 分组缓存
        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $groupCaches = $group->getCollector()->buildMergeCache($group->gid);
                $cacheItems = array_merge($cacheItems,$groupCaches);
            }
        }

        return $cacheItems;
    }

    public function restoreMergeCache(array $caches = []):void
    {

        // 合并缓存分组id转换
        $mergeCaches = [];
        foreach ($caches['mergeCaches'] as $key=>$mergeCache) {
            $keys = explode('.',$key);
            if (count($keys) == 3) {
                $group = $this->groups[$keys[0]];
                $gid = $group->gid;
                $keys[0] = $gid;
                $key = implode('.',$keys);
                $mergeCaches[$key] = $mergeCache;
            } else {
                $mergeCaches[$key] = $mergeCache;
            }
        }

        $this->mergeCaches = $mergeCaches;
    }




}
