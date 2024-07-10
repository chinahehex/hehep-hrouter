<?php
namespace hehe\core\hrouter;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\Rule;


class Route
{
    const GET_RULE_METHOD = 'get';
    const POST_RULE_METHOD = 'post';
    const PUT_RULE_METHOD = 'put';
    const DELETE_RULE_METHOD = 'delete';
    const PATCH_RULE_METHOD = 'patch';
    const HEAD_RULE_METHOD = 'head';
    const ANY_RULE_METHOD = '*';
    const DEFAULT_RULE_METHOD = '*';

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
     * @return Rule
     */
    public static function addRoute(string $uri = '',string $action = '',string $method = self::ANY_RULE_METHOD,array $options = []):Rule
    {
        $rule = RouteManager::createRule($uri,$action,$method,$options);
        static::register($rule);

        return $rule;
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
        return static::addRoute($uri,$action,self::GET_RULE_METHOD,$options);
    }

    public static function post(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::POST_RULE_METHOD,$options);
    }

    public static function put(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::PUT_RULE_METHOD,$options);
    }

    public static function patch(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::PATCH_RULE_METHOD,$options);
    }

    public static function delete(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::DELETE_RULE_METHOD,$options);
    }

    public static function head(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::HEAD_RULE_METHOD,$options);
    }

    public static function any(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::ANY_RULE_METHOD,$options);
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
     * @return Rule
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
