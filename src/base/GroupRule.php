<?php
namespace hehe\core\hrouter\base;

use hehe\core\hrouter\easy\EasyRule;

class GroupRule extends EasyRule
{
    /**
     * @var EasyRule[]
     */
    public $items = [];

    /**
     *
     * @var callable|string
     */
    protected $callable;

    /**
     * action 的前缀
     * @var string
     */
    protected $prefix = '';

    protected $rawUri = '';

    public function __construct($attrs = [])
    {
        parent::__construct($attrs);

        $this->rawUri = $this->uri;
        if (!$this->hasUriFlag()) {
            $this->uri = $this->uri . '<route:.*>';
            if (is_string($this->action)) {
                if ($this->action === '') {
                    $this->action = $this->rawUri . '<route>';;
                }
            }
        }
    }

    public function initGroup()
    {
        $this->init();

        $this->syncRules();
    }

    /**
     * 执行闭包函数,收集路由规则器
     */
    public function runCallable()
    {
        if ($this->callable instanceof \Closure) {
            ($this->callable)();
        }
    }

    /**
     * 分组的规则同步至路由规则器
     */
    protected function syncRules()
    {
        // 开始同步分组配置给rule
        if (count($this->items) > 0) {
            foreach ($this->items as $rule) {
                $options = $rule->getOptions(["uri","action","method","suffix","domain","uriParams","id"]);
                if (!empty($options['uri']) && substr($options['uri'],0,1) !== '/') {
                    $options['uri'] = $this->rawUri . '/' . $options['uri'];
                }

                if (!empty($options['action']) && $this->prefix !== '') {
                    $options['action'] = $this->prefix . $options['action'];
                }

                if (empty($options['method']) && $this->method !== '') {
                    $options['method'] = $this->action;
                }

                if (empty($options['suffix']) && !empty($this->suffix)) {
                    $options['suffix'] = $this->suffix;
                }

                if (empty($options['domain']) && !empty($this->domain)) {
                    $options['domain'] = $this->domain;
                }

                if (!empty($this->uriParams)) {
                    $options['uriParams'] = array_merge($this->uriParams,$options['uriParams']);
                }

                if (!empty($options['id']) && !empty($this->id)) {
                    $options['id'] = $this->id . $options['id'];
                }

                $rule->asOptions($options);
            }

            $this->router->addRules($this->items);
        }
    }

    /**
     * 添加路由规则器至分组路由器
     * @param Rule $rule
     */
    public function addRule(Rule $rule):void
    {
        $this->items[] = $rule;
    }

    public function asPrefix(string $prefix):self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix():string
    {
        return $this->prefix;
    }

    public function parseRequest(string $pathinfo,?RouteRequest $routeRequest = null)
    {
        // 验证此路由分组规则器
        if (!preg_match($this->uriRegex, $pathinfo, $matches)) {
            return false;
        }

        var_dump("ddddd");

        // 匹配分组路由规则器
        $rules = $this->router->ruleCollector->checkRules($this->items,$routeRequest->getMethod());
        $matchResult = $this->router->matchUriRules($routeRequest,$rules);

        if ($matchResult === false) {
            $matchResult = parent::parseRequest($pathinfo,$routeRequest);
        }

        return $matchResult;
    }


}
