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
        ;
        include_once(dirname(__DIR__,1) . '/common/Route.php');
        include_once(dirname(__DIR__,1) . '/common/Route1.php');

        Route::setRouteCache([
            'routeFile'=>[
                dirname(__DIR__,1) . '/common/Route.php',
                dirname(__DIR__,1) . '/common/Route1.php'
            ],
            'cacheDir'=>'D:\work\logs',
            'timeout'=>0,
        ]);

//        Route::getRouteCache()->addRouteFile(
//            dirname(__DIR__,1) . 'common/Route.php',
//            dirname(__DIR__,1) . 'common/Route1.php');


        $routeRequest = Route::parseRequest($this->createRequest("news/list"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'news/list');
        $this->assertTrue(Route::buildUrL("news/list") === 'news/list');
//
        $routeRequest = Route::parseRequest($this->createRequest("news/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'news/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("news/get",['id'=>1]) === 'news/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("news/save"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'news/save');
        $this->assertTrue(Route::buildUrL("news/save") === 'news/save');


        $routeRequest = Route::parseRequest($this->createRequest("role/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'role/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("role/get",['id'=>1]) === 'role/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("blog/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'blog/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("blog/get",['id'=>1]) === 'blog/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("role/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'role/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("role/get",['id'=>1]) === 'role/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("user/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'user/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("user/get",['id'=>1]) === 'user/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("role/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'role/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("role/get",['id'=>1]) === 'role/get/1');

        $routeRequest = Route::parseRequest($this->createRequest("blog/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'blog/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("blog/get",['id'=>1]) === 'blog/get/1');


        $routeRequest = Route::parseRequest($this->createRequest("blog/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'blog/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("blog/get",['id'=>1]) === 'blog/get/1');


    }




}
