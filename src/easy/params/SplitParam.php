<?php
namespace hehe\core\hrouter\easy\params;

use hehe\core\hrouter\easy\ParamRule;

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
    protected $defaultValue = '';

    // 参数数量模式 fixed 固定数量,dynamic
    protected $mode = "fixed";

    public function parse(string $param)
    {
        $regex = '/(' . $this->prefix .'([^' .  $this->flag . ']+))/';
        $vars = [];
        if (preg_match_all($regex, $param, $queryMatches)) {
            $queryMatcheValues = $queryMatches[2];
            $index = 0;
            foreach ($this->names as $key=>$name) {
                $pname = '';
                if (is_numeric($key)) {
                    $pname = $name;
                } else {
                    $pname = $key;
                }

                if (isset($queryMatcheValues[$index])) {
                    $vars[$pname] = $queryMatcheValues[$index];
                }

                $index++;
            }
        }

        return $vars;
    }

    public function format(array &$params)
    {

        $vars = [];
        $flag = false;
        $index = 0;

        // 默认参数状态
        $names = array_reverse($this->names);
        foreach ($names as $key=>$name) {
            $pname = '';
            $def_val = '';
            if (is_numeric($key)) {
                $def_val = '';
                $pname = $name;
            } else {
                $def_val = $name;
                $pname = $key;
            }

            if ($def_val === '') {
                $def_val = $this->defaultValue;
            }

            if (isset($params[$pname])) {
                $flag = true;
                $vars[$index] =  $params[$pname];
                unset($params[$pname]);
            } else {
                if ($flag) {
                    $vars[$index] = $def_val;
                } else {
                    if ($this->mode == 'fixed') {
                        $vars[$index] = $def_val;
                    }
                }
            }

            $index++;
        }

        $vars = array_reverse($vars);

        if (!empty($vars)) {
            return $this->prefix .  implode($this->flag,$vars);
        } else {
            return '';
        }
    }
}
