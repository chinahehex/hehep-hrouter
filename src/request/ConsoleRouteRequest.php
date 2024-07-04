<?php
namespace hehe\core\hrouter\request;
use hehe\core\hrouter\base\RouteRequest;

class ConsoleRouteRequest extends RouteRequest
{

    public function getPathinfo():string
    {
        $urlInfo = $this->resolveConsoleArgv();
        list($pathInfo,$query) = $urlInfo;
        $this->params = $query;

        return $pathInfo;
    }


    /**
     * 解析命令行数argv
     *<B>说明：</B>
     *<pre>
     * 返回['url地址','参数']
     *</pre>
     * @return array ['index/test',['name'=>'admin']]
     */
    protected function resolveConsoleArgv():string
    {
        $rawParams = $_SERVER['argv'];
        array_shift($rawParams);// 剔除入口文件
        if (isset($rawParams[0])) {
            $route = $rawParams[0];
            array_shift($rawParams);
        } else {
            $route = '';
        }

        $params = [];
        foreach ($rawParams as $param) {
            if (preg_match('/^--(\w+)(=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                $params[$name] = isset($matches[3]) ? $matches[3] : true;
            } else {
                $params[] = $param;
            }
        }

        return [$route, $params];
    }

    public function getMethod():string
    {
        return 'get';
    }

}
