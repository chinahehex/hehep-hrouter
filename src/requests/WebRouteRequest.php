<?php
namespace hehe\core\hrouter\requests;

use hehe\core\hrouter\base\RouteRequest;
use Exception;

class WebRouteRequest extends RouteRequest
{

    public function getPathinfo():string
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } else if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } else if (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new Exception('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    public function getMethod():string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getHost():string
    {
        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
            $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        $httpType = $this->isSsl() ? 'https://' : 'http://';

        return $httpType . $host;
    }

    /**
     * 判断是否SSL协议
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return boolean
     */
    protected function isSsl()
    {
        if (isset ( $_SERVER ['HTTPS'] ) && ('1' == $_SERVER ['HTTPS'] || 'on' == strtolower ( $_SERVER ['HTTPS'] ))) {
            return true;
        } elseif (isset ( $_SERVER ['SERVER_PORT'] ) && ('443' == $_SERVER ['SERVER_PORT'])) {
            return true;
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return true;
        }

        return false;
    }


}
