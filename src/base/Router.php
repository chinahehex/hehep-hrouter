<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\easy\EasyRouter;
use hehe\core\hrouter\easy\EasyRule;
use hehe\core\hrouter\RouteManager;

/**
 * 路由基类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
abstract class Router
{

    /**
     * 地址中是否添加.html 后续
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string|boolean
     */
    protected $suffix = false;

    /**
     * 地址中是否添加域名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string|boolean
     */
    protected $domain = false;

    /**
     * @var RuleCollector
     */
    public $ruleCollector;

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
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

        $this->ruleCollector = new RuleCollector();

	}

	/**
	 * 添加路由规则
	 *<B>说明：</B>
	 *<pre>
	 *  略
	 *</pre>
	 * @param array $rules 路由规则
	 * @param string $method 方法
     * @return void
	 */
	public function addRules(array $rules = [],string $method = RuleCollector::DEFAULT_RULE_METHOD):void
	{
        if (empty($rules)) {
            return;
        }

        if (empty($method)) {
            $method = RuleCollector::DEFAULT_RULE_METHOD;
        }

        foreach ($rules as $rule) {

            $rule->setRouter($this);
            $rule_methods = $rule->getArrMethod();
            if (empty($rule_methods)) {
                $rule_methods[] = $method;
            }

            if ($rule instanceof GroupRule) {
                $rule->initGroup();
                $this->ruleCollector->addRule($rule,$rule_methods);
            } else {
                $this->ruleCollector->addRule($rule,$rule_methods);
            }
        }
	}

    /**
     * 获取默认域名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $options
     * @return string
     **/
    protected function getDomain(array $options = [],?Rule $rule = null):string
    {
        $domain = "";
        if (isset($options['domain'])) {
            $domain = $options['domain'];
        } else if (!is_null($rule)) {
            $domain = $rule->getDomain();
        }

        if (empty($domain)) {
            $domain = $this->domain;
        }

        return $domain;
    }

    /**
     * 获取默认后缀
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $uri
     * @param array $options
     * @return string
     **/
    protected function getSuffix(string $uri,array $options = [],?Rule $rule = null):string
    {

        $urlPath = parse_url($uri,PHP_URL_PATH);
        if (strpos($urlPath,'.') !== false) {
            // uri 存在后缀,直接返回
            return "";
        }

        $suffix = "";
        if (isset($options['suffix'])) {
            $suffix = $options['suffix'];
        } else if (!is_null($rule)) {
            $suffix = $rule->getSuffix();
        }

        if ($suffix === "") {
            $suffix = $this->suffix;
        }

        if (is_bool($suffix) ) {
            $suffix = $suffix === true ? 'html' : "";
        }

        return $suffix;
    }

    /**
     * 匹配请求路由规则
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param ?RouteRequest $routeRequest 控制器/方法
     * @param Rule[] $rules 路由规则对象
     * @return boolean|array
     * <pre>
     * boolean: false 未找到匹配路由
     * array:['controller/action',['name'=>'1212']], ['控制器/方法',‘参数’]
     *</pre>
     */
    public function matchRequestRules(?RouteRequest $routeRequest = null,?array $rules = [])
    {
        $matchResult = false;
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if ($rule->getDomain() === true) {
                    $pathinfo = $routeRequest->getFullUrl();
                } else {
                    $pathinfo = $routeRequest->getPathinfo();
                }

                $matchResult = $rule->parseRequest($pathinfo,$routeRequest);
                if ($matchResult !== false) {
                    break;
                }
            }
        }

        return $matchResult;
    }

    /**
     * 匹配生成URL地址路由规则
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $uri action 地址
     * @param array $params 参数
     * @param array $rules 匹配的规则列表
     * @return array
     * <pre>
     * [匹配到的规则,匹配结果]
     *</pre>
     */
    public function matchUrlRules(string $uri,array $params = [],?array $rules = []):array
    {

        $matchResult = false;
        $matchRule = null;

        if (!empty($rules)) {
            foreach ($rules as $rule) {
                /** @var EasyRule $rule */
                $matchResult = $rule->parseUrL($uri, $params);
                if ($matchResult !== false) {
                    $matchRule = $rule;
                    break;
                }
            }
        }

        return [$matchRule,$matchResult];
    }

    /**
     * 解析路由地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
	 * @param RouteRequest $routeRequest　路由请求对象
     * @return void
     */
    abstract public function parseRequest(RouteRequest $routeRequest);

    /**
     * 生成url地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $url url地址
     * @param array $params url参数
     * @param array $options url配置
     * @return string
     */
    abstract public function buildUrL(string $url = '',array $params = [],array $options = []);

}
