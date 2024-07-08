<?php
namespace hehe\core\hrouter;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\base\RuleCollector;
use hehe\core\hrouter\easy\EasyRule;

class Route
{
    /**
     * 路由规则定义
     * @var array
     */
    public static $rules = [];


    /**
     * 当前的分组对象
     * @var GroupRule
     */
    public static $currentGroup;

    /**
     * 添加路由规则
     * @param string $uri
     * @param string $action
     * @param string $method
     * @param array $options
     * @return EasyRule
     */
    public static function addRoute(string $uri = '',string $action = '',string $method = '',array $options = []):EasyRule
    {
        $easyRule = RouteManager::createRule($uri,$action,$method,$options);
        static::register($easyRule);
        return $easyRule;
    }

    public static function register($rule)
    {
        if (!is_null(static::$currentGroup)) {
            static::$currentGroup->addRule($rule);
        } else {
            static::$rules[] = $rule;
        }
    }

    public static function get(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::GET_RULE_METHOD,$options);
    }

    public static function post(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::POST_RULE_METHOD,$options);
    }

    public static function put(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::PUT_RULE_METHOD,$options);
    }

    public static function patch(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::PATCH_RULE_METHOD,$options);
    }

    public static function delete(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::DELETE_RULE_METHOD,$options);
    }

    public static function head(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::HEAD_RULE_METHOD,$options);
    }

    public static function any(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,RuleCollector::ANY_RULE_METHOD,$options);
    }

    /**
     * 添加路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $rules 路由规则
     */
    public static function addRouteRules(?array $rules):void
    {
        if (is_null($rules)) {
            static::$rules = [];
        } else {
            static::$rules = array_merge(static::$rules,$rules);
        }
    }

    /**
     * 创建路由分组
     * @param string $uri
     * @param callable|null $action
     * @return GroupRule
     */
    public static function addGroup($uri = '',?callable $action = null):GroupRule
    {
        $groupRule = RouteManager::createGroup($uri,$action);
        static::register($groupRule);

        return $groupRule;
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
        return RouteManager::createRule($uri,$action,$method,$options);
    }

    public static function createGroup($uri = '',?callable $callable = null):GroupRule
    {
        return RouteManager::createGroup($uri,$callable);
    }
}
