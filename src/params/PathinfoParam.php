<?php
namespace hehe\core\hrouter\params;

use hehe\core\hrouter\base\ParamRule;

/**
 * Pathinfo形式参数解析类
 *<B>说明：</B>
 *<pre>
 * 格式:名称/值1/名称/值2/名称/值3，如:news/get/id/1/status/1
 *</pre>
 */
class PathinfoParam extends ParamRule
{
    // 字段名与值的分割符
    protected $valueSplit = '/';

    // 参数之间的分隔符
    protected $paramSplit = '/';

    // 参数的前缀
    protected $prefix = '';

    // 所有参数默认值
    protected $defval = '';

    protected $names = [];

    protected $regex = '';

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->regex = '/(\w+)\\' . $this->valueSplit. '([^\\' . $this->valueSplit .']+)/';

        $this->names = $this->buildNames($this->names,$this->defval);
    }

    public function parse(string $param = '')
    {
        $vars = [];
        preg_replace_callback($this->regex, function($matches) use(&$vars){
            $vars[strtolower($matches[1])] = strip_tags($matches[2]);
        }, $param);

        foreach ($vars as $name=>$value) {
            if (isset($this->names[$name])) {
                list($pname,$defval,$var_regex) = $this->names[$name];
                if ($var_regex !== '') {
                    if (preg_match($var_regex,$value)) {
                        $vars[$pname] = $value;
                    } else {
                        $vars[$pname] = $defval;
                    }
                } else {
                    $vars[$pname] = $value;
                }
            }
        }

        return $vars;
    }

    public function build(array &$params)
    {
        $urlParams = [];
        if (count($this->names) > 0) {
            foreach ($this->names as $name=>$vars) {
                if (isset($params[$name])) {
                    $urlParams[] = $name . $this->valueSplit . $params[$name];
                    unset($params[$name]);
                }
            }
        } else {
            foreach ($params as $name=>$value) {
                $urlParams[] = $name . $this->valueSplit . $value;
                unset($params[$name]);
            }
        }

        $queryUrl = implode($this->paramSplit,$urlParams);

        if (!empty($queryUrl)) {
            return $this->prefix .  $queryUrl;
        } else {
            return '';
        }
    }
}
