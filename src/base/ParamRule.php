<?php
namespace hehe\core\hrouter\base;

/**
 * 参数解析规则基类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
abstract class ParamRule
{
    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }
    }

    protected function buildNames(array $names,string $defaultVale = ''):array
    {
        $varNames = [];
        foreach ($names as $key=>$var_name) {
            $pname = '';
            $regex = '';
            $defval = '';
            if (is_numeric($key)) {
                $pname = $var_name;
            } else {
                if (is_array($var_name)) {
                    $pname = $key;
                    $defval = isset($var_name['defval']) ? $var_name['defval'] : '';
                    $regex = isset($var_name['regex']) ? $var_name['regex'] : '';
                } else {
                    $pname = $key;
                    $defval = $var_name;
                }
            }

            if ($defval === '') {
                $defval = $defaultVale;
            }

            if ($regex !== '') {
                $regex = '/^' . $regex . '$/';
            }

            $varNames[$pname] = [$pname,$defval,$regex];
        }

        return $varNames;
    }

    // 解析参数
    abstract public function parse(string $param);

    // 构建参数
    abstract public function build(array &$params);
}
