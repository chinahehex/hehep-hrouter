<?php
namespace hehe\core\hrouter\params;

use hehe\core\hrouter\base\ParamRule;

/**
 * 分隔符形式参数解析类
 *<B>说明：</B>
 *<pre>
 *  格式:-值1-值2-值3，如:news/get/thread-121-1-2
 *</pre>
 */
class SplitParam extends ParamRule
{
    // 字段名与值的分割符
    protected $flag = '-';

    // 参数的前缀
    protected $prefix = '-';

    // 参数定义,支持定义默认值
    protected $names = [];

    // 所有参数默认值
    protected $defval = '';

    // 参数数量模式 fixed 固定数量,dynamic
    protected $mode = "fixed";

    protected $regex = '';

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);
        $this->regex = '/(' . $this->prefix .'([^' .  $this->flag . ']+))/';
        $this->names = array_values($this->buildNames($this->names,$this->defval));
    }

    public function parse(string $param)
    {
        $vars = [];
        if (preg_match_all($this->regex, $param, $matches)) {
            $matcheValues = $matches[2];
            foreach ($this->names as $index=>$var_name) {
                list($pname,$defval,$var_regex) = $var_name;
                if (isset($matcheValues[$index])) {
                    if ($var_regex !== '') {
                        if (preg_match($var_regex,$matcheValues[$index])) {
                            $vars[$pname] = $matcheValues[$index];
                        } else {
                            $vars[$pname] = $defval;
                        }
                    } else {
                        $vars[$pname] = $matcheValues[$index];
                    }
                }
            }
        }

        return $vars;
    }

    public function build(array &$params)
    {

        $vars = [];
        $flag = false;

        // 默认参数状态
        $names = array_reverse($this->names);
        foreach ($names as $index=>$var_name) {
            list($pname,$defval,$regex) = $var_name;

            if (isset($params[$pname])) {
                $flag = true;
                $vars[$index] = $params[$pname];
                unset($params[$pname]);
            } else {
                if ($flag) {
                    $vars[$index] = $defval;
                } else {
                    if ($this->mode === 'fixed') {
                        $vars[$index] = $defval;
                    }
                }
            }
        }

        $vars = array_reverse($vars);

        if (!empty($vars)) {
            return $this->prefix .  implode($this->flag,$vars);
        } else {
            return '';
        }
    }
}
