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

//    public function testok()
//    {
//        $url = 'http://www.baidu.com';
//        $url = 'news_id';
//        $url = 'http://www.baidu.com:80/user/ok';
////        $url = 'www.baidu.com/user/ok';
////        $url = 'http://www.baidu.com/user/ok.html';
////        $url = 'http://www.baidu.com/user/ok?id=1';
//        $url = 'http://www.baidu.com/user/ok?id=1#red';
////        $url = 'user/ok';
////        $url = 'user/ok.html';
//        $info = parse_url($url);
//        var_dump($info);
//    }


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


        $matchingResult = Route::parseRequest($this->createRequest("newsa/list"));
        $this->assertTrue($matchingResult->getUri() === 'newsa/list');
        $this->assertTrue(Route::buildUrL("newsa/list") === 'newsa/list');

        $matchingResult = Route::parseRequest($this->createRequest("newsa/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'newsa/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("newsa/get",['id'=>1]) === 'newsa/get/1');

        $matchingResult = Route::parseRequest($this->createRequest("newsa/save"));
        $this->assertTrue($matchingResult->getUri() === 'newsa/save');
        $this->assertTrue(Route::buildUrL("newsa/save") === 'newsa/save');


        $matchingResult = Route::parseRequest($this->createRequest("rolea/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'rolea/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("rolea/get",['id'=>1]) === 'rolea/get/1');

        $matchingResult = Route::parseRequest($this->createRequest("bloga/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'bloga/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("bloga/get",['id'=>1]) === 'bloga/get/1');

        $matchingResult = Route::parseRequest($this->createRequest("rolea/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'rolea/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("rolea/get",['id'=>1]) === 'rolea/get/1');

        $matchingResult = Route::parseRequest($this->createRequest("usera/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'usera/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("usera/get",['id'=>1]) === 'usera/get/1');

        $matchingResult = Route::parseRequest($this->createRequest("rolea/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'rolea/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("rolea/get",['id'=>1]) === 'rolea/get/1');

        $matchingResult = Route::parseRequest($this->createRequest("bloga/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'bloga/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("bloga/get",['id'=>1]) === 'bloga/get/1');


        $matchingResult = Route::parseRequest($this->createRequest("bloga/get/1"));
        $this->assertTrue($matchingResult->getUri() === 'bloga/get' && ($matchingResult->getParams())['id'] == 1);
        $this->assertTrue(Route::buildUrL("bloga/get",['id'=>1]) === 'bloga/get/1');


    }




}
