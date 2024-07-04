<?php
namespace hehe\core\hrouter\easy\params;

use hehe\core\hrouter\easy\ParamRule;

/**
 * 分隔符形式参数
 *<B>说明：</B>
 *<pre>
 *  name/admin/id/1
 *</pre>
 *<B>示例：</B>
 *<pre>
 * // 路由配置
 * $routes =  [
 *    'site/<controller:\w+>/<action:\w+>' => 'site/<controller>/<action>',
'http://<user_id:\w+>.digpage.com/post/<id:\d+>.html' => 'login/index',
 * ]
 *</pre>
 *<B>日志：</B>
 *<pre>
 *  略
 *</pre>
 *<B>注意事项：</B>
 *<pre>
 *  略
 *</pre>
 */
class SingleParam extends ParamRule
{

    // pathinfo 参数匹配正则表达式/
    const PATHINFO_PARAMS_REGEX = '/<(\w+):?([^>]+)?>/';

    // URL参数匹配正则表达式/
    const URL_PARAMS_REGEX = '<(\\w+)>';


    // 参数左边分隔符
    const PARAMS_LEFT_FLAG = "<";

    // 参数左边分隔符
    const PARAMS_RIGHT_FLAG = ">";

    // 字段名与值的分割符
    protected $valueSplit = '/';

    // 参数之间的分隔符
    protected $paramSplit = '/';

    // 参数的前缀
    protected $prefix = '/';


    protected $matching = [
        [
            // ershouche/pr-10-1533
            'pattern'=>'<pingpai:\w+>-<chenxi:\w+>/<jiage:\w+>',// 虚正则
            'reg'=>'',// 正则表达式参数
            'column'=>'',// 出现的字段
        ]
    ];

    protected function formatPatternParams($patternParams)
    {
        $replaceParams = [];
        foreach ($patternParams as $name=>$pattern) {
            $replaceParams[self::PARAMS_LEFT_FLAG . $name . self::PARAMS_RIGHT_FLAG] = "(?P<$name>$pattern)";
        }

        return $replaceParams;
    }

    /**
     * 格式化pathinfoRule参数
     *<B>说明：</B>
     *<pre>
     *  比如,"<controller:\\\\w+>/<action:\\\\w+>"
     *  返回参数格式:{"controller":"\\w+","action":"\\w+"}
     *</pre>
     */
    private function matchParams($pattern)
    {
        $patternParams = [];
        if (preg_match_all(self::PATHINFO_PARAMS_REGEX, $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1][0];
                $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                $patternParams[$name] = $pattern;
            }
        }

        return $patternParams;
    }


    public function __construct($rule = [])
    {
        if (!empty($rule)) {
            foreach ($rule as $attr=>$value) {
                $this->$attr = $value;
            }
        }

        $this->init();
    }


    protected function init()
    {
        foreach ($this->matching as $index=>$match) {
            $urlParams = $this->matchParams($match['pattern']);
            $patternParams =  $this->formatPatternParams($urlParams);
            $regTemplate = preg_replace(self::PATHINFO_PARAMS_REGEX, '<$1>', $match['pattern']);
            $regTemplate = '#^' . trim(strtr($regTemplate, $patternParams), '/') . '$#u';
            $match['reg'] = $regTemplate;
            $match['column'] = $urlParams;
            $this->matching[$index] = $match;
        }

    }

    public function parse(string $param)
    {
        $var = [];
        foreach ($this->matching as $rule) {
            if (!preg_match($rule['reg'], $param, $matches)) {
                continue;
            }

            $columns = array_keys($rule['column']);
            foreach ($columns as $column) {
                if (isset($matches[$column])) {
                    $var[$column] = $matches[$column];
                }
            }
        }

        return $var;
    }

    public function format(array &$params)
    {
        $urlParams = [];
        foreach ($params as $name=>$value) {
            $urlParams[] = $name . $this->valueSplit . $value;
        }

        $queryUrl = implode($this->paramSplit,$urlParams);

        if (!empty($queryUrl)) {
            return $this->prefix .  $queryUrl;
        } else {
            return '';
        }
    }
}
