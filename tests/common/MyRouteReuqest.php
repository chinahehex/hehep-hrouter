<?php
namespace hrouter\tests\common;

use hclient\transports\StreamTransport;
use hehe\core\hrouter\base\RouteRequest;

class MyRouteReuqest extends RouteRequest
{

    public $my_url = '';

    public $my_method = '';

    public $my_host = '';


    public function getPathinfo():string
    {
        return $this->my_url;
    }

    public function getMethod():string
    {
        return $this->my_method;
    }

    public function getHost():string
    {
        return $this->my_host;
    }

}
