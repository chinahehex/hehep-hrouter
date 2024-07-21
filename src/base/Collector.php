<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\Route;

abstract class Collector
{

    /**
     * 合并路由缓存
     * @var array
     */
    protected $mergeCaches = [];

    /**
     * @var GroupRule[]
     */
    protected $groups = [];

    /**
     * 所有路由规则对象
     * @var array
     */
    protected $allRules = [];

    public function getAllRules():array
    {
        return $this->allRules;
    }

    public function addRules(array $rules):void
    {
        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    public function setMergeCache(string $key,int $ruleIndex,array $regex):void
    {
        $this->mergeCaches[$key][$ruleIndex] = $regex;
    }

    public function getMergeCache(string $key,int $ruleIndex):?array
    {
        return isset($this->mergeCaches[$key][$ruleIndex]) ? $this->mergeCaches[$key][$ruleIndex] : null;
    }

    public function hasMergeCache(string $key,int $ruleIndex):bool
    {
        if (!isset($this->mergeCaches[$key][$ruleIndex])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 是否有效缓存
     * @param string $key
     * @param int $ruleSize
     * @return bool
     */
    public function isActiveCache(string $key,int $ruleSize):bool
    {
        if (!isset($this->mergeCaches[$key]) || !isset($this->mergeCaches[$key]['-'])) {
            return false;
        }

        if ((int)$this->mergeCaches[$key]['-']  !== $ruleSize) {
            return false;
        }

        return true;
    }

    public function initKeyCache(string $key,int $ruleSize):void
    {
        unset($this->mergeCaches[$key]);
        $this->mergeCaches[$key]['-'] = $ruleSize;
    }

    public function addGroup(GroupRule $groupRule):self
    {
        $this->groups[$groupRule->gid] = $groupRule;

        return $this;
    }
    

    public function buildCache(Router $router)
    {
        $caches = [];
        $caches = $this->buildAllRulesCache($router,$caches);
        $caches = $this->buildGroupRulesCache($router,$caches);
        $caches = $this->buildRouteCache($router,$caches);

        return $caches;
    }

    public function restoreCache(Router $router,array $caches):void
    {
        $this->restoreAllRulesCache($router,$caches);
        $this->restoreGroupRulesCache($router,$caches);
        $this->restoreRouteCache($router,$caches);

        // 所有路由规则id转换
        $allRules = [];
        foreach ($this->allRules as $rule) {
            $allRules[$rule->ruleId] = $rule;
        }

        $this->allRules = $allRules;

        // 所有分组id转换
        $groups = [];
        foreach ($this->groups as $group) {
            $groups[$group->gid] = $group;
        }

        $this->groups = $groups;
    }

    protected function buildAllRulesCache(Router $router,array $caches):array
    {
        $allRulesCache = [];
        $del_attrs =  ['_paramRule','collector','subRules','router','callable',];
        foreach ($this->allRules as $rule) {
            $properties = $rule->getAttributes();
            foreach ($del_attrs as $del_name) {
                unset($properties[$del_name]);
            }

            $allRulesCache[$rule->ruleId] = $properties;
        }

        $caches['allRules'] = $allRulesCache;

        return $caches;
    }

    protected function restoreAllRulesCache(Router $router,array $caches)
    {
        $allRules = [];
        foreach ($caches['allRules'] as $ruleId=>$properties) {
            if ($properties['gid'] !== '') {
                unset($properties['subRules']);
                $rule = new GroupRule($properties);
            } else {
                $rule = new Rule($properties);
            }

            $rule->setRouter($router);
            $allRules[$ruleId] = $rule;
        }

        $this->allRules = $allRules;
    }

    protected function buildGroupRulesCache(Router $router,array $caches):array
    {
        $groupsCache = [];
        $del_attrs =  ['_paramRule','collector','subRules','router','callable',];
        foreach ($this->groups as $group) {
            $properties = $group->getAttributes();
            foreach ($del_attrs as $del_name) {
                unset($properties[$del_name]);
            }
            $groupsCache[$group->gid] = [
                $properties,
                $group->getCollector()->buildCache($router)
            ];
        }

        $caches['groups'] = $groupsCache;

        return $caches;
    }

    protected function restoreGroupRulesCache(Router $router,array $caches)
    {
        $allGroups = [];
        foreach ($caches['groups'] as $ruleId=>$groupCache) {
            list($properties,$groupCache) = $groupCache;
            if (isset($this->allRules[$ruleId])) {
                $group = $this->allRules[$ruleId];
            } else {
                $group = new GroupRule($properties);
            }

            foreach ($groupCache['allRules'] as $subId=>$subProperties) {
                if (isset($this->allRules[$subId])) {
                    $rule = $this->allRules[$subId];
                } else {
                    $rule = new Rule($subProperties);
                }

                $rule->setRouter($router);
                $group->addSubRule($rule);
            }

            $group->setRouter($router);
            $group->getCollector()->restoreCache($router,$groupCache);

            $allGroups[$ruleId] = $group;
        }

        $this->groups = $allGroups;
    }

    /**
     * 添加路由规则至规则收集器
     * @param Rule $rule
     * @param array $methods
     * @return $this
     */
    abstract public function addRule(Rule $rule,array $methods = []):self;

    /**
     * 路由规则初始化事件
     * @param Rule $rule
     */
    abstract public function initRule(Rule $rule):void;


    /**
     * 指定路由地址返回对应的变量路由
     * @param string $method
     * @param string $type
     * @return Rule[]|array
     */
    abstract public function getActionRules(string $method,string $type = ''):array;

    /**
     * 指定路由规则返回对应的变量路由
     * @param RouteRequest $routeRequest
     * @param string $method
     * @param string $type
     * @return array|Rule[]
     */
    abstract public function getUriRules(RouteRequest $routeRequest,string $method,string $type = ''):array;
    abstract public function buildRouteCache(Router $router,$caches):array;
    abstract public function restoreRouteCache(Router $router,array $caches):void;

}
