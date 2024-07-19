<?php
namespace hehe\core\hrouter\routine;

use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\Router;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\Route;

/**
 * 常规路由解析器
 *<B>说明：</B>
 *<pre>
 *  解析url，解析类型有:路由规则，url映射，子域名映射
 *  此路由采用正则表达式处理，灵活，因此可能会存在性能问题
 *</pre>
 *<B>示例：</B>
 *<pre>
 * 略
 *</pre>
 */
class RoutineRouter extends Router
{

    
    /**
     * 构造函数
     * 用于初始化对象。这个构造函数继承自父类，并接受一个数组参数来传递属性。
     * 
     * @param array $attrs 初始化对象时的属性数组，可以为空。
     */
    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
    }


    public function runCallable(GroupRule $rule)
    {
        $rule->asFalseGroup(true);
        $rule->runCallable();
    }
    
    
    /**
     * 添加路由规则。
     * 
     * 本方法用于向规则收集器添加新的路由规则。它接受一个Rule对象作为参数，
     * 并通过该对象获取定义的处理方法。如果规则没有明确指定任何方法，
     * 则默认添加GET方法。
     * 
     * @param Rule $rule 要添加的路由规则对象。
     * @return void
     */
    public function addRule(Rule $rule):void
    {
        // 从规则对象中获取定义的方法列表
        $rule_methods = $rule->getMethods();
        
        // 如果方法列表为空，则默认添加GET方法
        if (empty($rule_methods)) {
            $rule_methods[] = Route::DEFAULT_METHOD;
        }
        
        // 将规则及其方法添加到规则收集器中
        $this->ruleCollector->addRule($rule,$rule_methods);
    }

    protected function getMethodRules(string $method):array
    {
        if (isset($this->rules[$method])) {
            return $this->rules[$method];
        } else {
            return [];
        }
    }


    /**
     * 解析pathinfo
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param RouteRequest $routeRequest 路由请求对象
     * @return RouteRequest
     */
    public function parseRequest(RouteRequest $routeRequest):RouteRequest
    {
        $matchResult = false;

        $request_method = $routeRequest->getMethod();
        if ($matchResult === false) {
            foreach ([$request_method, Route::ANY_METHOD] as $method) {
                $rules = $this->ruleCollector->getRules($method);
                if ($this->mergeRule) {
                    $matchResult = $this->matchMergeUriRules($rules,$routeRequest,'uri.' .$method,$this->mergeLen);
                } else {
                    $matchResult = $this->matchUriRules($rules,$routeRequest);
                }

                if ($matchResult !== false) {
                    break;
                }
            }
        }

        if ($matchResult !== false) {
            $routeRequest->setMatchResult($matchResult);
        } else {
            // 匹配不到路由规则
            $routeRequest->setMatchResult([$routeRequest->getRoutePathinfo(),[],null]);
        }

        return $routeRequest;
    }

	public function matchAction(string $uri = '',array $params = [],array $options = [])
	{

        // 查找域名
        $matchResult = false;
        $staticActionRule = $this->ruleCollector->getConstantActionRule($uri);
        if (!is_null($staticActionRule)) {
            $matchResult = $this->matchActionRules([$staticActionRule],$staticActionRule->getAction(),$params);
        }

        if ($matchResult === false) {
            foreach ([Route::GET_METHOD, Route::ANY_METHOD] as $method) {
                $rules = $this->ruleCollector->getRules($method);
                if ($this->mergeRule) {
                    $matchResult = $this->matchMergeActionRules($rules,$uri,$params,"act." . $method,$this->mergeLen);
                } else {
                    $matchResult = $this->matchActionRules($rules,$uri,$params);
                }

                if ($matchResult !== false) {
                    break;
                }
            }
        }


        return $matchResult;
	}



}
