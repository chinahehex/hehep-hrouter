<?php
namespace hehe\core\hrouter\request;
use hehe\core\hrouter\base\RouteRequest;

class AliasRouteRequest extends RouteRequest
{
    protected $varName = '';

    public function getPathinfo():string
    {
        $pathInfo = '';

        if (isset($_GET[$this->varName])) { // 判断URL里面是否有兼容模式参数
            $pathInfo = $_GET[$this->varName];
        }

        return $pathInfo;
    }


    public function getMethod():string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

}
