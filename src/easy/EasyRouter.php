<?php
namespace hehe\core\hrouter\easy;

use hehe\core\hrouter\base\Router;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\base\RuleCollector;

/**
 * esay url路由控制类
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
class EasyRouter extends Router
{

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

        // 执行静态缓存路由
        $staticUriRule = $this->ruleCollector->getStaticUriRule($routeRequest);
        if (!is_null($staticUriRule)) {
            $matchResult = $this->matchUriRules([$staticUriRule],$routeRequest);
        }

        // 遍历变量路由
        if ($matchResult === false) {
            // 执行请求方法路由规则
            $rules = $this->ruleCollector->getVariableUriRules($routeRequest->getMethod());
            $matchResult = $this->matchUriRules($rules,$routeRequest);
        }

        // 变量全局路由
        if ($matchResult === false) {
            // 执行请求方法路由规则
            $rules = $this->ruleCollector->getVariableUriRules(RuleCollector::ANY_RULE_METHOD);
            $matchResult = $this->matchUriRules($rules,$routeRequest);
        }

        if ($matchResult !== false) {
            $routeRequest->setMatchResult($matchResult);
        } else {
            // 匹配不到路由规则
            $routeRequest->setMatchResult([$routeRequest->getRouterPathinfo(),[],null]);
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

        // 执行静态缓存路由
        $staticActionRule = $this->ruleCollector->getStaticActionRule($uri);
        if (!is_null($staticActionRule)) {
            $matchResult = $this->matchActionRules([$staticActionRule],$staticActionRule->getAction(),$params);
        }

        // 遍历GET请求类型变量路由
        if ($matchResult === false) {
            $matchResult = $this->matchActionRules($this->ruleCollector->getVariableActionRules(RuleCollector::GET_RULE_METHOD),$uri,$params);
        }

        // 变量*请求类型变量路由
        if ($matchResult === false) {
            $matchResult = $this->matchActionRules($this->ruleCollector->getVariableActionRules(RuleCollector::ANY_RULE_METHOD),$uri,$params);
        }

        $url = "";
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
