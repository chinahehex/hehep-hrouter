<?php
namespace hrouter\tests\units;
use hehe\core\hrouter\Route;
use hrouter\tests\common\AdminController;
use hrouter\tests\TestCase;

class RouteCacheTest extends TestCase
{
    protected function setUp()
    {
        // 初始化路由
        Route::intiRoute();
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        Route::resetRoute();
    }


    public function testRoute()
    {
//        require dirname(__DIR__,1) . '/common/Route.php';
//        require dirname(__DIR__,1) . '/common/Route1.php';

        Route::setRouteCache([
            'routeFile'=>[
                dirname(__DIR__,1) . '/common/Route.php',
                dirname(__DIR__,1) . '/common/Route1.php'
            ],
            'cacheDir'=>'D:\work\logs',
            'timeout'=>0,
        ]);

        Route::getRouteCache()->requireRouteFile();


        $routeRequest = Route::parseRequest($this->createRequest("newsa/list"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'newsa/list');
        $this->assertTrue(Route::buildUrL("newsa/list") === 'newsa/list');

        $routeRequest = Route::parseRequest($this->createRequest("newsa/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'newsa/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("newsa/get",['id'=>1]) === 'newsa/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("newsa/save"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'newsa/save');
        $this->assertTrue(Route::buildUrL("newsa/save") === 'newsa/save');


        $routeRequest = Route::parseRequest($this->createRequest("rolea/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'rolea/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("rolea/get",['id'=>1]) === 'rolea/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("bloga/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'bloga/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("bloga/get",['id'=>1]) === 'bloga/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("rolea/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'rolea/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("rolea/get",['id'=>1]) === 'rolea/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("usera/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'usera/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("usera/get",['id'=>1]) === 'usera/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("rolea/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'rolea/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("rolea/get",['id'=>1]) === 'rolea/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("bloga/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'bloga/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("bloga/get",['id'=>1]) === 'bloga/get/1');


        $routeRequest = Route::parseRequest($this->createRequest("bloga/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'bloga/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("bloga/get",['id'=>1]) === 'bloga/get/1');


    }




}
