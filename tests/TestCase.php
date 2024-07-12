<?php
namespace hrouter\tests;

use hehe\core\hrouter\Route;
use hehe\core\hrouter\RouteManager;
use hrouter\tests\common\MyRouteReuqest;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RouteManager
     */
    protected $hrouter;

    // 单个测试之前(每个测试方法之前调用)
    protected function setUp()
    {
        $this->hrouter = new RouteManager();
    }

    protected function getRouter():RouteManager
    {
        if (is_null($this->hrouter)) {
            $this->hrouter = new RouteManager();
        }

        return $this->hrouter;
    }

    protected function createRequest(string $url,$method = '',$my_host = '')
    {
        $request = new MyRouteReuqest();
        $request->my_url = $url;
        $request->my_method = $method;
        $request->my_host = $my_host;

        return $request;
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        $this->hrouter = null;
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
    public static function setUpBeforeClass()
    {

    }

    // 整个测试类之前
    public static function tearDownAfterClass()
    {

    }


}
