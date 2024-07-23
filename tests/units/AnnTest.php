<?php
namespace hrouter\tests\units;
use hehe\core\hcontainer\ContainerManager;
use hehe\core\hrouter\RouteManager;
use hrouter\tests\common\AdminController;
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
        $this->hcontainer->addScanRule(AdminController::class,RouteManager::class)
            ->startScan();
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testUserRule()
    {

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("admin/doadd"));
        $this->assertTrue($matchingResult->getUri() == "admin/add");

        $url = $this->getRouter()->buildUrl('admin/add');
        $this->assertTrue($url == "admin/doadd");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("admin/1"));
        $params = $matchingResult->getParams();

        $this->assertTrue($matchingResult->getUri() == "admin/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('admin/get',['id'=>1]);
        $this->assertTrue($url == "admin/1");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("admin/save"));
        $this->assertTrue($matchingResult->getUri() == "admin/save");

        $url = $this->getRouter()->buildUrl('admin/save');
        $this->assertTrue($url == "admin/save");

    }

    public function testRoleRule()
    {
        if (!$this->checkVersion()) {return;}
        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("role/doadd"));
        $this->assertTrue($matchingResult->getUri() == "role/add");

        $url = $this->getRouter()->buildUrl('role/add');
        $this->assertTrue($url == "role/doadd");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("role/1"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "role/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('role/get',['id'=>1]);
        $this->assertTrue($url == "role/1");
    }

    public function testLogRule()
    {
        if (!$this->checkVersion()) {return;}
        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("log/doadd"));
        $this->assertTrue($matchingResult->getUri() == "log/add");

        $url = $this->getRouter()->buildUrl('log/add');
        $this->assertTrue($url == "log/doadd");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("log/1"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "log/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('log/get',['id'=>1]);
        $this->assertTrue($url == "log/1");
    }

    public function testAuthRule()
    {
        if (!$this->checkVersion()) {return;}
        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("auth/doadd"));
        //var_dump($matchingResult->getUri());
        $this->assertTrue($matchingResult->getUri() == "auth/add");

        $url = $this->getRouter()->buildUrl('auth/add');
        $this->assertTrue($url == "auth/doadd");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("auth/1"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "auth/get" && $params['id'] == 1);

        $url = $this->getRouter()->buildUrl('auth/get',['id'=>1]);
        $this->assertTrue($url == "auth/1");
    }

    public function testGoodRestful()
    {
        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good","get"));
        $this->assertTrue($matchingResult->getUri() == "good/index");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good/create","get"));
        $this->assertTrue($matchingResult->getUri() == "good/create");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good","post"));
        $this->assertTrue($matchingResult->getUri() == "good/save");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good/1","get"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "good/read" && $params['id'] == 1);

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good/1/edit","get"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "good/edit" && $params['id'] == 1);

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good/1","put"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "good/update" && $params['id'] == 1);

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("good/1","delete"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "good/delete" && $params['id'] == 1);
    }

    public function testOrderRestful()
    {
        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order","get"));
        $this->assertTrue($matchingResult->getUri() == "my/order/index");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order/create","get"));
        $this->assertTrue($matchingResult->getUri() == "my/order/create");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order","post"));
        $this->assertTrue($matchingResult->getUri() == "my/order/save");

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order/1","get"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "my/order/read" && $params['id'] == 1);

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order/1/edit","get"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "my/order/edit" && $params['id'] == 1);

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order/1","put"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "my/order/update" && $params['id'] == 1);

        $matchingResult = $this->getRouter()->parseRequest($this->createRequest("my/order/1","delete"));
        $params = $matchingResult->getParams();
        $this->assertTrue($matchingResult->getUri() == "my/order/delete" && $params['id'] == 1);
    }






}
