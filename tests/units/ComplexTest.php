<?php
namespace hrouter\tests\units;
use hehe\core\hrouter\Route;
use hrouter\tests\common\AdminController;
use hrouter\tests\TestCase;

class ComplexTest extends TestCase
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
        Route::get("news/list","news/list");
        Route::get("news/get/<id:\d+>","news/get");
        Route::get("news/add","news/doadd");

        Route::get("user/list","user/list");
        Route::get("user/get/<id:\d+>","user/get");
        Route::get("user/add","user/doadd");

        Route::get("role/list","role/list");
        Route::get("role/get/<id:\d+>","role/get");
        Route::get("role/add","role/doadd");

        Route::get("adminb/list","adminb/list");
        Route::get("adminb/get/<id:\d+>","adminb/get");
        Route::get("adminb/add","adminb/doadd");

        Route::addGroup('blog',function (){
            Route::get("list","blog/list");
            Route::get("get/<id:\d+>","blog/get");
        })->asMergeRule();

        $routeRequest = Route::parseRequest($this->createRequest("news/list"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'news/list');
        $this->assertTrue(Route::buildUrL("news/list") === 'news/list');
//
        $routeRequest = Route::parseRequest($this->createRequest("news/get/1"));
        $this->assertTrue($routeRequest->getRouteUrl() === 'news/get' && ($routeRequest->getRouteParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("news/get",['id'=>1]) === 'news/get/1');

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
