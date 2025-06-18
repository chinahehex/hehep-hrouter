<?php
namespace hehe\core\hroute\fast;

use hehe\core\hroute\base\GroupRule;
use hehe\core\hroute\base\RouteMatcher;
use hehe\core\hroute\base\RouteRequest;
use hehe\core\hroute\base\Rule;
use hehe\core\hroute\Route;

/**
 * 快速路由解析器
 *<B>说明：</B>
 *<pre>
 *  解析url，解析类型有:路由规则，url映射，子域名映射
 *  此路由采用正则表达式处理，灵活，因此可能会存在性能问题
 *</pre>
 */
class FastRouteMatcher extends RouteMatcher
{

    protected $collectorClass = FastCollector::class;


    /**
     * 解析pathinfo
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param RouteRequest $routeRequest 路由请求对象
     * @return false|array
     */
    public function matchRequest(RouteRequest $routeRequest):?array
    {
        $matchResult = null;

        // 执行常量路由
        $constantUriRules = $this->getCollector()->getUriRules($routeRequest,$routeRequest->getRoutePathinfo(),'constant');
        if (!empty($constantUriRules)) {
            $matchResult = $this->matchUriRules($constantUriRules,$routeRequest);
        }

        $request_method = $routeRequest->getMethod();
        if ($matchResult === null) {
            foreach ([$request_method,Route::ANY_METHOD] as $method) {
                $rules = $this->getCollector()->getUriRules($routeRequest,$method);
                if ($this->mergeRule) {
                    $matchResult = $this->matchMergeUriRules($rules,$routeRequest,'uri.' .$method,$this->mergeLen);
                } else {
                    $matchResult = $this->matchUriRules($rules,$routeRequest);
                }

                if ($matchResult !== null) {
                    break;
                }
            }
        }

        return $matchResult;
    }

    public function matchAction(string $uri = '',array $params = [],array $options = []):?array
    {
        // 查找域名
        $matchResult = null;

        // 执行常量路由
        $constantActionRules = $this->getCollector()->getActionRules($uri,'constant');
        if (!empty($constantActionRules)) {
            $matchResult = $this->matchActionRules($constantActionRules,($constantActionRules[0])->getAction(),$params);
        }

        if ($matchResult === null) {
            foreach ([Route::GET_METHOD, Route::ANY_METHOD] as $method) {
                $rules = $this->getCollector()->getActionRules($method);
                if ($this->mergeRule) {
                    $matchResult = $this->matchMergeActionRules($rules,$uri,$params,'act.'.$method,$this->mergeLen);
                } else {
                    $matchResult = $this->matchActionRules($rules,$uri,$params);
                }

                if ($matchResult !== null) {
                    break;
                }
            }
        }


        return $matchResult;
    }



}
