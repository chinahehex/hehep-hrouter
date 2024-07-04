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

    public function runCallable()
    {
        if ($this->callable instanceof \Closure) {
            ($this->callable)();
        }
    }

    protected function syncRules()
    {
        // 开始同步分组配置给rule
        if (count($this->items) > 0) {
            foreach ($this->items as $rule) {
                $options = $rule->getOptions(["uri","action","method","suffix","domain","uriParams"]);
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

                $rule->asOptions($options);
            }

            $this->router->addRules($this->items);
        }
    }

    public function addRule(Rule $rule):void
    {
        $this->items[] = $rule;
    }

    public function getRules(string $method):array
    {
        if (empty($method)) {
            return $this->items;
        }

        return array_filter($this->items, function ($rule) use ($method) {
            /** @var EasyRule $rule **/
            $rule_methods = $rule->getArrMethod();
            return in_array($method,$rule_methods);
        });
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
        $this->init();

        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!preg_match($this->uriRegex, $pathinfo, $matches)) {
            return false;
        }

        // 匹配分组子路由
        $rules = $this->router->ruleCollection->checkRules($this->items,$routeRequest->getMethod());
        $matchResult = $this->router->matchRequestRules($routeRequest,$rules);

        if ($matchResult === false) {

            // 匹配此分组规则
            $matchResult = parent::parseRequest($pathinfo,$routeRequest);
        }

        return $matchResult;
    }


}
