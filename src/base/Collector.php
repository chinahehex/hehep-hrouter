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
        $this->groups[$groupRule->hashId] = $groupRule;

        return $this;
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
     * 指定路由地址返回对应的常量路由
     * @param string $action
     * @return Rule[]|array
     */
    abstract public function getConstantActionRules(string $action):array;

    /**
     * 指定路由地址返回对应的变量路由
     * @param mixed ...$methods
     * @return Rule[]
     */
    abstract public function getVarActionRules(...$methods):array;

    /**
     * 指定路由规则返回对应的常量路由
     * @param RouteRequest $routeRequest
     * @return Rule[]|array
     */
    abstract public function getConstantUriRules(RouteRequest $routeRequest):array;

    /**
     * 指定路由规则返回对应的变量路由
     * @param RouteRequest $routeRequest
     * @param mixed ...$methods
     * @return array
     */
    abstract public function getVarUriRules(RouteRequest $routeRequest,...$methods):array;

}
