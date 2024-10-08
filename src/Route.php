<?php
namespace hehe\core\hrouter;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\MatchingResult;
use hehe\core\hrouter\base\RouteCache;
use hehe\core\hrouter\base\RouteMatcher;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;


/**
 * @method static void setRouteMatcher(array $routerConfig = [])
 * @method static void setRouteRequest(array $routeRequest)
 * @method static void setRouteCache(array $routeCache)
 * @method static MatchingResult parseRequest($routeRequest = null)
 * @method static string buildUrL(string $url = '',array $params = [],array $options = [])
 * @method static RouteCache getRouteCache()
 * @method static RouteMatcher getRouteMatcher()
 * @method static RouteRequest createRouteRequest()
 */
class Route
{
    const GET_METHOD = 'get';
    const POST_METHOD = 'post';
    const PUT_METHOD = 'put';
    const DELETE_METHOD = 'delete';
    const PATCH_METHOD = 'patch';
    const HEAD_METHOD = 'head';
    const ANY_METHOD = '*';
    const DEFAULT_METHOD = '*';

    /**
     * 所有请求类型集合
     * @var array
     */
    public static $allMethod = [
        self::GET_METHOD,
        self::POST_METHOD,
        self::PUT_METHOD,
        self::DELETE_METHOD,
        self::PATCH_METHOD,
        self::HEAD_METHOD,
        self::ANY_METHOD,
    ];

    /**
     * 路由规则定义
     * @var Rule[]
     */
    public static $rules = [];

    /**
     * @var RouteManager
     */
    public static $routeManager;

    /**
     * 路由请求类
     * @var string
     */
    public static $routeRequest;

    /**
     * 当前的分组对象
     * @var GroupRule
     */
    public static $currentGroup;


    public static function register($rule)
    {
        if (!is_null(static::$currentGroup)) {
            static::$currentGroup->addSubRule($rule);
        } else if (!is_null(static::$routeManager)) {
            static::$routeManager->register($rule);
        } else {
            static::$rules[] = $rule;
        }
    }

    /**
     * 添加路由规则
     * @param string $uri
     * @param string $action
     * @param string $method
     * @param array $options
     * @return Rule
     */
    public static function addRoute($uri = '',string $action = '',string $method = self::ANY_METHOD,array $options = []):Rule
    {
        $rule = RouteManager::createRule($uri,$action,$method,$options);
        static::register($rule);

        return $rule;
    }

    /**
     * 添加路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param ?array $rules 路由规则
     */
    public static function addRules(?array $rules):void
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
     * @param ?callable $action
     * @return GroupRule
     */
    public static function addGroup($uri = '',?callable $action = null):GroupRule
    {
        $groupRule = RouteManager::createGroup($uri,$action);
        static::register($groupRule);

        return $groupRule;
    }


    public static function get(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::GET_METHOD,$options);
    }

    public static function post(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::POST_METHOD,$options);
    }

    public static function put(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::PUT_METHOD,$options);
    }

    public static function patch(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::PATCH_METHOD,$options);
    }

    public static function delete(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::DELETE_METHOD,$options);
    }

    public static function head(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::HEAD_METHOD,$options);
    }

    public static function any(string $uri = '',string $action = '',array $options = [])
    {
        return static::addRoute($uri,$action,self::ANY_METHOD,$options);
    }

    /**
     * 创建路由规则对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array|string $uri 路由规则表达式
     * @param string $action 路由地址表达式
     * @param string $method 请求类型
     * @param array $options 路由配置
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

    public static function intiRoute($route = ''):?RouteManager
    {
        if (is_null($route)) {
            static::resetRoute();
            return null;
        }

        if ($route instanceof RouteManager) {
            static::$routeManager = $route;
        } else {
            static::$routeManager = RouteManager::make($route);
        }

        return static::$routeManager;
    }

    public static function resetRoute():void
    {
        static::$routeManager = null;
        static::$rules = [];

        return;
    }

    public static function __callStatic($method, $params)
    {
        if (is_null(static::$routeManager)) {
            static::intiRoute();
        }

        return call_user_func_array([static::$routeManager,$method],$params);
    }

}
