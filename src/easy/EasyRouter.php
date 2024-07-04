<?php
namespace hehe\core\hrouter\easy;

use hehe\core\hrouter\base\Router;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\base\RuleCollection;

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
        $executeResult = false;

        // 执行缓存路由
        $cacheReuqestRule = $this->ruleCollection->getCacheReuqestRule($routeRequest);
        if (!is_null($cacheReuqestRule)) {
            $executeResult = $this->matchRequestRules($routeRequest,[$cacheReuqestRule]);
        }

        // 遍历路由
        if ($executeResult === false) {
            // 执行请求方法路由规则
            $rules = $this->ruleCollection->getMethodRules(true,$routeRequest->getMethod());
            $executeResult = $this->matchRequestRules($routeRequest,$rules);
        }

        $params = [];
        if ($executeResult !== false) {
            list ($pathinfo, $params) = $executeResult;
        } else {
            // 匹配不到路由规则
            $pathinfo = $routeRequest->getRouterPathinfo();
        }

        $routeRequest->setRequestResult([$pathinfo,$params]);

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

        // 匹配路由规则
        $cacheUrlRule = $this->ruleCollection->getUrlCacheRule($uri);
        if (!is_null($cacheUrlRule)) {
            list($matchRule,$matchResult) = $this->matchUrlRules($cacheUrlRule->getAction(),$params,[$cacheUrlRule]);
        } else {
            list($matchRule,$matchResult) = $this->matchUrlRules($uri,$params,
                $this->ruleCollection->getGetRules(true));
        }

        $url = "";
        if ($matchResult === false) {
            $url = $uri;
        } else {
            list($url,$params) = $matchResult;
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
