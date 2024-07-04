<?php
namespace hehe\core\hrouter\easy;


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

    abstract public function parse(string $param);

    abstract public function format(array &$params);
}
