<?php
namespace hrouter\tests\units;
use hehe\core\hcontainer\ContainerManager;
use hehe\core\hrouter\RouteManager;
use hrouter\tests\TestCase;

class AnnTest extends TestCase
{
    /**
     * @var \hehe\core\hcontainer\ContainerManager
     */
    protected $hcontainer;

    protected function setUp()
    {
        parent::setUp();
        $this->hcontainer = new ContainerManager();
        $this->hcontainer->addScanRule(TestCase::class,RouteManager::class)
            ->startScan();
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testUserRule()
    {

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("admin/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "admin/add");

        $url = $this->hrouter->buildUrl('admin/add');
        $this->assertTrue($url == "admin/doadd");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("admin/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "admin/get" && $params['id'] == 1);

        $url = $this->hrouter->buildUrl('admin/get',['id'=>1]);
        $this->assertTrue($url == "admin/1");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("admin/save"));
        $this->assertTrue($routerRequest->getRouteUrl() == "admin/save");

        $url = $this->hrouter->buildUrl('admin/save');
        $this->assertTrue($url == "admin/save");

    }

    public function testRoleRule()
    {
        if (!$this->checkVersion()) {return;}
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("role/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "role/add");

        $url = $this->hrouter->buildUrl('role/add');
        $this->assertTrue($url == "role/doadd");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("role/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "role/get" && $params['id'] == 1);

        $url = $this->hrouter->buildUrl('role/get',['id'=>1]);
        $this->assertTrue($url == "role/1");
    }

    public function testLogRule()
    {
        if (!$this->checkVersion()) {return;}
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("log/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "log/add");

        $url = $this->hrouter->buildUrl('log/add');
        $this->assertTrue($url == "log/doadd");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("log/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "log/get" && $params['id'] == 1);

        $url = $this->hrouter->buildUrl('log/get',['id'=>1]);
        $this->assertTrue($url == "log/1");
    }

    public function testAuthRule()
    {
        if (!$this->checkVersion()) {return;}
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("auth/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "auth/add");

        $url = $this->hrouter->buildUrl('auth/add');
        $this->assertTrue($url == "auth/doadd");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("auth/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "auth/get" && $params['id'] == 1);

        $url = $this->hrouter->buildUrl('auth/get',['id'=>1]);
        $this->assertTrue($url == "auth/1");
    }






}
