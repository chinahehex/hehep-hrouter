<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\Route;

abstract class Collector
{
    /**
     * @var RouteMatcher
     */
    protected $routeMatcher;

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

    public function setRouteMatcher(RouteMatcher $routeMatcher):void
    {
        $this->routeMatcher = $routeMatcher;
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
    public function checkMergeCacheStatus(string $key,int $ruleSize):bool
    {
        if (!isset($this->mergeCaches[$key]) || !isset($this->mergeCaches[$key]['-'])) {
            return false;
        }

        if ((int)$this->mergeCaches[$key]['-']  !== $ruleSize) {
            return false;
        }

        return true;
    }

    public function resetKeyCache(string $key,int $ruleSize):void
    {
        unset($this->mergeCaches[$key]);
        $this->mergeCaches[$key]['-'] = $ruleSize;
    }

    public function addGroup(GroupRule $groupRule):self
    {
        $this->groups[$groupRule->gid] = $groupRule;

        return $this;
    }


    public function buildCache()
    {
        $caches = [];
        $caches = $this->buildAllRulesCache($caches);
        $caches = $this->buildGroupRulesCache($caches);
        $caches = $this->buildRouteCache($caches);

        return $caches;
    }

    public function restoreCache(array $caches):void
    {
        $this->restoreAllRulesCache($caches);
        $this->restoreGroupRulesCache($caches);
        $this->restoreRouteCache($caches);

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

    protected function buildAllRulesCache(array $caches):array
    {
        $allRulesCache = [];
        $del_attrs =  ['_paramRule','collector','subRules','routeMatcher','callable',];
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

    protected function restoreAllRulesCache(array $caches)
    {
        $allRules = [];
        foreach ($caches['allRules'] as $ruleId=>$properties) {
            if (isset($properties['gid'])) {
                unset($properties['subRules']);
                $rule = new GroupRule($properties);
            } else {
                $rule = new Rule($properties);
            }

            $rule->setRouteMatcher($this->routeMatcher);
            $allRules[$ruleId] = $rule;
        }

        $this->allRules = $allRules;
    }

    protected function buildGroupRulesCache(array $caches):array
    {
        $groupsCache = [];
        $del_attrs =  ['_paramRule','collector','subRules','routeMatcher','callable',];
        foreach ($this->groups as $group) {
            $properties = $group->getAttributes();
            foreach ($del_attrs as $del_name) {
                unset($properties[$del_name]);
            }
            $groupsCache[$group->gid] = [
                $properties,
                $group->getCollector()->buildCache()
            ];
        }

        $caches['groups'] = $groupsCache;

        return $caches;
    }

    protected function restoreGroupRulesCache(array $caches)
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

                $rule->setRouteMatcher($this->routeMatcher);
                $group->addSubRule($rule);
            }

            $group->setRouteMatcher($this->routeMatcher);
            $group->getCollector()->restoreCache($groupCache);

            $allGroups[$ruleId] = $group;
        }

        $this->groups = $allGroups;
    }

    /**
     * 添加路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Rule $rule 路由规则
     * @param RouteMatcher $routeMatcher 路由解析器
     * @return void
     */
    abstract public function addRule(Rule $rule):Collector;

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
    abstract public function buildRouteCache(array $caches):array;
    abstract public function restoreRouteCache(array $caches):void;

}
