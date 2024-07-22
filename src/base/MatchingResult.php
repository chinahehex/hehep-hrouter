<?php
namespace hehe\core\hrouter\base;

class MatchingResult
{

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * 匹配规则
     * @var Rule
     */
    protected $rule;

    public function __construct(string $action = '',array $params = [],?Rule $rule = null)
    {
        $this->action = $action;
        $this->params = $params;
        $this->rule = $rule;
    }

    public function getUri()
    {
        return $this->action;
    }


    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return Rule
     */
    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    /**
     * @param Rule $rule
     */
    public function setRule(?Rule $rule): void
    {
        $this->rule = $rule;
    }


    /**
     * 匹配是否成功
     * @return bool
     */
    public function isMatchSuccess()
    {
        if (is_null($this->rule)) {
            return false;
        } else {
            return true;
        }
    }


}
