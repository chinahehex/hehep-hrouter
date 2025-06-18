<?php
namespace hroute\tests;

use hehe\core\hroute\Route;
use hehe\core\hroute\RouteManager;
use hroute\tests\common\MyRouteReuqest;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RouteManager
     */
    protected $hroute;

    // 单个测试之前(每个测试方法之前调用)
    protected function setUp():void
    {
        //$this->hroute = new RouteManager();
        $this->getRouter();
    }

    protected function getRouter():RouteManager
    {
        if (is_null($this->hroute)) {
            $this->hroute = new RouteManager();
        }

        return $this->hroute;
    }

    protected function createRequest(string $url,$method = 'get',$my_host = '')
    {
        $request = new MyRouteReuqest();
        $request->my_url = $url;
        $request->my_method = $method;
        $request->my_host = $my_host;

        return $request;
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown():void
    {
        $this->hroute = null;
        Route::addRules(null);
    }

    protected function checkVersion()
    {
        if ((explode('.',phpversion()))[0] != 8) {
            $this->assertTrue(true);
            return false;
        } else {
            return true;
        }
    }

    // 整个测试类之前
    public static function setUpBeforeClass():void
    {

    }

    // 整个测试类之前
    public static function tearDownAfterClass():void
    {

    }


}
