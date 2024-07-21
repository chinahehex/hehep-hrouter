<?php
namespace hehe\core\hrouter\base;

/**
 * 路由请求类
 *<B>说明：</B>
 *<pre>
 * 请次请求都会创建新对象
 *</pre>
 */
class RouteRequest
{
    /**
     * 解析得到的url pathion地址
     * @var string
     */
    protected $url = '';

    /**
     * 解析后得到的参数,一般为问号的参数,比如?id=1
     * @var array
     */
    protected $params = [];

    /**
     * 路由解析器对象
     * @var RouteMatcher
     */
    protected $routeMatcher;

    /**
     * 匹配到的路由对象
     * @var Rule
     */
    protected $rule;

    /**
     * pathinfo 缓存地址
     * @var string
     */
    protected $_pathinfo;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * 获取路由地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return string
     */
    public function getRoutePathinfo():string
    {
        if (!is_null($this->_pathinfo)) {
            return $this->_pathinfo;
        }

        $pathinfo = $this->getPathinfo();
        $pathinfo = urldecode($pathinfo);
        if (!preg_match('%^(?:
                [\x09\x0A\x0D\x20-\x7E]              # ASCII
                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
                | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
                )*$%xs', $pathinfo)) {
            $pathinfo = utf8_encode($pathinfo);
        }

        // 解析?
        $urlparse = parse_url($pathinfo);

        $params = [];
        $url = $urlparse['path'];
        if (!empty($url) && substr($url,0,1) == '/') {
            $url = substr($url,1);
            if ($url === false) {
                $url = '';
            }
        }

        $this->_pathinfo = $url;

        if (isset($urlparse['query'])) {
            parse_str($urlparse['query'],$params);
        }

        $this->params = $params;

        return $this->_pathinfo;
    }

    /**
     * 获取完整url地址
     * @return string
     */
    public function getFullUrl():string
    {
        $host = $this->getHost();
        $pathinfo = $this->getRoutePathinfo();

        if (!empty($host)) {
            return $host . (!empty($pathinfo) ? '/' . $pathinfo : '') ;
        } else {
            return $pathinfo;
        }
    }

    /**
     * 设置解析的结果
     * @param $matchResult
     */
    public function setMatchResult(?array $matchResult):void
    {
        if (!empty($matchResult)) {
            list($url,$params,$rule) = $matchResult;
            $this->url = $url;
            $this->params = array_merge($this->params,$params);
            $this->rule = $rule;
        }
    }

    public function setRouteMatcher(RouteMatcher $routeMatcher):void
    {
        $this->routeMatcher = $routeMatcher;
    }

    public function getRouteMatcher():?RouteMatcher
    {
        return $this->routeMatcher;
    }

    public function getRouteUrl():string
    {
        return $this->url;
    }

    public function getRouteParams():array
    {
        return $this->params;
    }

    public function getRouteRule():?Rule
    {
        return $this->rule;
    }

    /**
     * 解析路由
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function parseRequest():void
    {
        $this->routeMatcher->matchRequest($this);
    }

    /**
     * 构建url 地址
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $url url 地址
     * @param array $params url 参数
     * @param array $options url 其他配置
     * @return string
     */
    public function buildUrl(string $url = '',array $params = [],array $options = []):string
    {
        return $this->routeMatcher->buildUrL($url,$params,$options);
    }

    /***** 继承此类以下方法即可 ******/

    public function getPathinfo():string
    {
        return '';
    }

    // 获取当前host
    public function getHost():string
    {
        return '';
    }

    public function getMethod():string
    {
        return '';
    }
}
