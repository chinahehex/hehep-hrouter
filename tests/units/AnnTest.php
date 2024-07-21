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

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("admin/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "admin/add");

        $url = $this->getRouter()->buildUrl('admin/add');
        $this->assertTrue($url == "admin/doadd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("admin/1"));
        $params = $routerRequest->getRouteParams();
        var_dump($routerRequest->getRouteUrl());

        $this->assertTrue($routerRequest->getRouteUrl() == "admin/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('admin/get',['id'=>1]);
        $this->assertTrue($url == "admin/1");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("admin/save"));
        $this->assertTrue($routerRequest->getRouteUrl() == "admin/save");

        $url = $this->getRouter()->buildUrl('admin/save');
        $this->assertTrue($url == "admin/save");

    }

    public function testRoleRule()
    {
        if (!$this->checkVersion()) {return;}
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("role/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "role/add");

        $url = $this->getRouter()->buildUrl('role/add');
        $this->assertTrue($url == "role/doadd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("role/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "role/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('role/get',['id'=>1]);
        $this->assertTrue($url == "role/1");
    }

    public function testLogRule()
    {
        if (!$this->checkVersion()) {return;}
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("log/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "log/add");

        $url = $this->getRouter()->buildUrl('log/add');
        $this->assertTrue($url == "log/doadd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("log/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "log/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('log/get',['id'=>1]);
        $this->assertTrue($url == "log/1");
    }

    public function testAuthRule()
    {
        if (!$this->checkVersion()) {return;}
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("auth/doadd"));
        $this->assertTrue($routerRequest->getRouteUrl() == "auth/add");

        $url = $this->getRouter()->buildUrl('auth/add');
        $this->assertTrue($url == "auth/doadd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("auth/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "auth/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('auth/get',['id'=>1]);
        $this->assertTrue($url == "auth/1");
    }

    public function testGoodRestful()
    {
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "good/index");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good/create","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "good/create");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good","post"));
        $this->assertTrue($routerRequest->getRouteUrl() == "good/save");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good/1","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "good/read" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good/1/edit","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "good/edit" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good/1","put"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "good/update" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("good/1","delete"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "good/delete" && $params['id'] == 1);
    }

    public function testOrderRestful()
    {
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/index");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order/create","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/create");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order","post"));
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/save");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order/1","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/read" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order/1/edit","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/edit" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order/1","put"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/update" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("my/order/1","delete"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "my/order/delete" && $params['id'] == 1);
    }






}
