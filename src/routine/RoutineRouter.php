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
     * 构造方法
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $attrs 配置参数
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
     * 添加路由规则
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Rule $rule 路由规则
     * @return void
     */
    public function addRule(Rule $rule):void
    {
        $rule_methods = $rule->getMethods();
        if (empty($rule_methods)) {
            $rule_methods[] = Route::DEFAULT_METHOD;
        }

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
    public function parseRequest(RouteRequest $routeRequest)
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

    /**
     * 生成URL 地址
     *<B>说明：</B>
     *<pre>
     * 示例1：创建url地址
     * $url = $this->buildUrL('user/login',['id'=>'ok']);
     * $url:user/login?id=ok
     *
     * 示例2：添加后缀
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['suffix'=>true]);
     * or
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['suffix'=>'.html']);
     *
     * $url:user/login.html?id=ok
     *
     * 示例3：添加域名,如果路由规则中已经存在域名，也会显示
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['domain'=>true]);
     * or
     * $url = $this->buildUrL('user/login',['id'=>'ok'],['domain'=>'http://www.baidu.com']);
     *
     * $url:http://www.baidu.com/user/login?id=ok
     *
     * 示例4：添加锚点
     * $url = $this->buildUrL('user/login',['id'=>'ok','#'=>'add']);
     *
     * 示例5：当前页面url
     * $url = $this->buildUrL('',['id'=>'ok','#'=>'add']);
     *
     *</pre>
     * @param string $uri url 地址
     * @param array $params url 参数
     * @param array $options url 配置
     * @return string
     */
	public function buildUrL(string $uri = '',array $params = [],array $options = [])
	{
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#']);

        // 查找后缀
        $suffix = "";
        if (strpos($uri,'.') !== false) {
            list($uri,$suffix) = explode('.',$uri);
        }

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

        $url = "";
        $matchRule = null;

        if ($matchResult !== false) {
            list($url,$params,$matchRule) = $matchResult;
        } else {
            $url = $uri;
        }

        // 解析url域名
        if (preg_match('/^http(s?):\/\//i',$url) === 0) {
            $domain = $this->getDomain($options,$matchRule);
            $url = $domain != '' ? $domain  . $url : $url;
        }

        // 解析url 文件名后缀
        if ($url !== '') {
            if ($suffix === '') {
                $suffix = $this->getSuffix($url,$options,$matchRule);
            }

            $url = $suffix != '' ? $url  . "." . $suffix : $url;
        }

        // url 参数
        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        // 解析锚点
        $url .= $anchor;

        return $url;
	}



}
