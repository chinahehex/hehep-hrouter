<?php
namespace hrouter\tests\units;
use hehe\core\hrouter\Route;
use hehe\core\hrouter\RouteManager;
use hrouter\tests\common\AdminController;
use hrouter\tests\TestCase;

class ExampleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testNoRouter()
    {
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("index/name"));
        $this->assertTrue($routerRequest->getRouteUrl() == "index/name");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/add?id=1"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/add/id/1"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add/id/1");
    }

    public function testAddRule()
    {
        $this->hrouter->addRoute("<controller:\w+>/<action:\w+>",'<controller>/<action>');
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/add"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add");
    }

    public function testRuleParam()
    {
        $this->hrouter->addRoute("<controller:\w+>/<id:\d+>",'<controller>/detail');
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/1"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/detail");

        $url = $this->hrouter->buildUrl('user/detail',['id'=>1]);
        $this->assertTrue($url == "user/1");
    }

    public function testRuleDate()
    {
        $this->hrouter->addRoute('news/<year:\d{4}>/<category>','news/<category>');
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/2014/list"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && $params['year'] == '2014');

        $url = $this->hrouter->buildUrl('news/list',['year'=>2015]);
        $this->assertTrue($url == "news/2015/list");
    }

    public function testMoreRule()
    {
        $this->hrouter->addRoute('<controller:(news|evaluate)>/<id:\d+>/<action:(add|edit|del)>','<controller>/<action>');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/1/add"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/add" && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('news/add',['id'=>2]);
        $this->assertTrue($url == "news/2/add");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/1/edit"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/edit" && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('news/edit',['id'=>2]);
        $this->assertTrue($url == "news/2/edit");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("evaluate/1/edit"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "evaluate/edit" && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('evaluate/edit',['id'=>2]);
        $this->assertTrue($url == "evaluate/2/edit");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/1/edit"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/1/edit");

    }

    public function testHostRule()
    {
        $this->hrouter->addRoute('http://www.hehep.cn/news/<id:\d+>','news/get')->asDomain();

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/1",'','http://www.hehep.cn'));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/get" && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('news/get',['id'=>2]);

        $this->assertTrue($url == "http://www.hehep.cn/news/2");

    }

    public function testHost1Rule()
    {
        $this->hrouter->addRoute('http://<module:\w+>.hehep.cn/news/<id:\d+>','<module>/news/get')->asDomain();

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/1",'','http://content.hehep.cn'));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/news/get" && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('content/news/get',['id'=>2]);

        $this->assertTrue($url == "http://content.hehep.cn/news/2");

    }

    public function testHParamRule1()
    {
        // thread-119781-1-1.html
        $this->hrouter->addRoute('<controller:\w+>/<action:\w+>/thread-<id:\d+>-<status:\d+>-<type:\d+>','<controller>/<action>');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/add/thread-121-1-2"));

        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);

        $this->assertTrue($url == "news/add/thread-122-1-1");
    }

    public function testHParamRule2()
    {
        // thread-119781-1-1.html
        //$this->hrouter->register('<controller:\w+>/<action:\w+>/thread<param:(-?.*)>','<controller>/<action>');
        $this->hrouter->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:(.*)>',
            'action'=>'<controller>/<action>',
            'pvar'=>'param',
            'prule'=>['class'=>'split','names'=>['id','status','type']]
        ]);
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");
    }

    public function testHParamRule4()
    {
        // thread-119781-1-1.html
        //$this->hrouter->register('<controller:\w+>/<action:\w+>/thread<param:(-?.*)>','<controller>/<action>');
        $this->hrouter->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:(.*)>',
            'action'=>'<controller>/<action>',
            'pvar'=>'param',
            'prule'=>['class'=>'split','names'=>['id','status','type'=>"0"]]
        ]);
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-0");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122--1");

    }

    public function testHParamRule6()
    {
        // thread-119781-1-1.html
        //$this->hrouter->register('<controller:\w+>/<action:\w+>/thread<param:.*>','<controller>/<action>');
        $this->hrouter->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
            'action'=>'<controller>/<action>',
            'pvar'=>'param',
            'prule'=>['class'=>'split','names'=>['id','status'=>0,'type'=>"0"]]
        ]);
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-0");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-0-1");
    }

    public function testHParamRule7()
    {
        // 动态参数
        // thread-119781-1-1.html
        //$this->hrouter->register('<controller:\w+>/<action:\w+>/thread<param:(-?.*)>','<controller>/<action>');
        $this->hrouter->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:(.*)>',
            'action'=>'<controller>/<action>',
            'pvar'=>'param',
            'prule'=>['class'=>'split','mode'=>'dynamic','names'=>['id','status'=>"0",'type']]
        ]);
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-0-1");

        $url = $this->hrouter->buildUrl('news/add',['id'=>122]);
        $this->assertTrue($url == "news/add/thread-122");
    }

    public function testHParamRule3()
    {
        // user/get/id/1/status/1/type/1/

        $this->hrouter->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/<param:(.*)>',
            'action'=>'<controller>/<action>',
            'pvar'=>'param',
            'prule'=>['class'=>'pathinfo','names'=>['id','status','type']]
        ]);

        //$this->hrouter->register('<controller:\w+>/<action:\w+>/<param:(.*)>','<controller>/<action>');
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/id/121/status/1/type/2"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->hrouter->buildUrl('user/get',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "user/get/id/122/status/1/type/1");

        $url = $this->hrouter->buildUrl('user/get',['id'=>122,'status'=>1,'type'=>1,'role'=>1]);
        $this->assertTrue($url == "user/get/id/122/status/1/type/1?role=1");

        $url = $this->hrouter->buildUrl('user/get',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "user/get/id/122/status/1");
    }

    public function testDefaultParam()
    {
        // user/get/id/1/status/1/type/1/

        $this->hrouter->addRoute([
            'uri'=>'<language:\w+/?><controller:\w+>/<action:\w+>/<id:\d+>',
            'action'=>'<language><controller>/<action>',
            'defaults'=>['language'=>'en'],
        ]);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('user/get',['id'=>122,'status'=>1,'language'=>'ch']);


        $this->assertTrue($url == "ch/user/get/122?status=1");


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("ch/user/get/1"));
        $params = $routerRequest->getRouteParams();


        $this->assertTrue($routerRequest->getRouteUrl() == "ch/user/get"
            && $params['id'] == '1');


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("en/user/get/1"));
        $params = $routerRequest->getRouteParams();



        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

    }

    public function testParamRule8()
    {
        // user/get/id/1/status/1/type/1/

        $this->hrouter->addRoute([
            'uri'=>'<module:\w+/?><controller:\w+>/<action:\w+>/<id:\d+>',
            'action'=>'<module><controller>/<action>',
            'defaults'=>['language'=>'en'],
        ]);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('user/get',['id'=>122,'status'=>1,'language'=>'ch']);
        $this->assertTrue($url == "user/get/122?status=1&language=ch");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/user/get/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "content/user/get"
            && $params['id'] == '1');

        $url = $this->hrouter->buildUrl('content/user/get',['id'=>122,'status'=>1,'language'=>'ch']);
        $this->assertTrue($url == "content/user/get/122?status=1&language=ch");


        $url = $this->hrouter->buildUrl('user/get',['id'=>122,'status'=>1,'language'=>'ch']);
        $this->assertTrue($url == "user/get/122?status=1&language=ch");
    }

    public function testDuoRule()
    {

        $this->hrouter->addRoute('user/<action:get|list>','user1/<action>');
        $this->hrouter->addRoute('<controller:\w+>/<action:\w+>','<controller>/<action>');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user1/get");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/add"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("new/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "new/get");
    }

    public function testMethodRule()
    {
        $this->hrouter->addRoute('user/<action:get|list>','user1/<action>','post');
        $this->hrouter->addRoute('user/<action:get|list>','user2/<action>','get');
        $this->hrouter->addRoute('user/<action:get|list>','user3/<action>');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get",'post'));
        $this->assertTrue($routerRequest->getRouteUrl() == "user1/get");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get",'get'));
        $this->assertTrue($routerRequest->getRouteUrl() == "user2/get");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user3/get");
    }

    public function testClassRule()
    {
        $this->hrouter->addRoute('adminuser/<action:get|list>',AdminController::class .'@<action>');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("adminuser/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == AdminController::class . "@get");


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("adminuser/list"));
        $this->assertTrue($routerRequest->getRouteUrl() == AdminController::class . "@list");
    }

    public function testSetFlag()
    {
        $this->hrouter->addRoute('user/<id>','user/get');
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get" && $params["id"] == 1);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/list"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get" && $params["id"] == "list");


        $this->hrouter->addRoute('role/<id>','role/get')
            ->asParams(["id"=>'\d+']);
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("role/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "role/get" && $params["id"] == 1);


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("role/list"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "role/list" && !isset($params["id"]));
    }

    public function testWenFlag()
    {
        $this->hrouter->addRoute('user<id:/\d+?>','user/get')
                                //->asParams(["id"=>'\d+'])
        ;
        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get");

        $url = $this->hrouter->buildUrl('user/get',['id'=>122]);

        $this->assertTrue($url == "user/122");

    }

    public function testFlag1()
    {
        $this->getRouter()->addRoute([
            'uri'=>'<module?><controller:\w+>/<action:\w+>/<id:\d+>',
            'action'=>'<module><controller>/<action>',
        ])->asParams(["module"=>'\w+/']);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/user/get"
            && $params['id'] == '1');

    }

    public function testFlag2()
    {
        $this->hrouter->addRoute([
            'uri'=>'<module:?><controller:\w+>/<action:\w+>/<id:\d+?>',
            'action'=>'<module><controller>/<action>',
        ])->asParams(["module"=>'\w+/?']);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/user/get"
            && $params['id'] == '1');

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && !isset($params['id']) );
    }

    public function testFlag3()
    {
        $this->hrouter->addRoute([
            'uri' => 'user/<id:\d+?>',
            'action' => 'user/get',
            'id'=>'new_id',
        ]);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/1"));
        $url = $this->hrouter->buildUrl('new_id',['id'=>122]);
        $this->assertTrue($url == "user/122");

        $url = $this->hrouter->buildUrl('user/get',['id'=>122]);
        $this->assertTrue($url == "user/122");

    }

    public function testOptions()
    {
        $this->hrouter->addRoute([
            'uri' => 'user/<id:\d+?>',
            'action' => 'user/get',
        ])->asSuffix();

        $this->hrouter->addRoute([
            'uri' => 'http://www.baidu.com/news/<id:\d+?>',
            'action' => 'news/get',
        ])->asSuffix();

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("user/get/1"));

        $url = $this->hrouter->buildUrl('user/get',['id'=>122]);
        $this->assertTrue($url == "user/122.html");

        $url = $this->hrouter->buildUrl('user/get.html',['id'=>122]);
        $this->assertTrue($url == "user/122.html");

        $url = $this->hrouter->buildUrl('user/get',['id'=>122],['suffix'=>'htmls']);
        $this->assertTrue($url == "user/122.htmls");

        $url = $this->hrouter->buildUrl('news/get',['id'=>122]);
        $this->assertTrue($url == "http://www.baidu.com/news/122.html");
    }

    public function testRestful()
    {
        $this->hrouter->get("blog","blog/index");
        $this->hrouter->get("blog/create","blog/create");
        $this->hrouter->post("blog","blog/save");
        $this->hrouter->get("blog/<id:\d+>","blog/read");
        $this->hrouter->get("blog/<id:\d+>/edit","blog/edit");
        $this->hrouter->put("blog/<id:\d+>","blog/update");
        $this->hrouter->delete("blog/<id:\d+>","blog/delete");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/create","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/create");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog","post"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/save");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/1","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/read" && $params['id'] == 1);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/1/edit","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/edit" && $params['id'] == 1);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/1","put"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/update" && $params['id'] == 1);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/1","delete"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/delete" && $params['id'] == 1);
    }

    public function testGroup()
    {
        Route::addGroup("blog",function(){
            Route::addRoute("list","blog/list");
            Route::get("get/<id>","blog/get");
            Route::post("add","blog/doadd");
            Route::get("add","blog/doadd");
        })->asParams(["id"=>"\d+"]);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");

        $this->assertTrue($this->hrouter->buildUrL("blog/list") == "blog/list");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2");


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/add","post"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->hrouter->buildUrL("blog/doadd") == "blog/add");
    }

    public function testGroup1()
    {
        Route::addGroup("blog",function(){
            Route::addRoute("list","blog/list");
            Route::get("get/<id>","blog/get");
            Route::get("add","blog/doadd")->asSuffix("shtml");
        })->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->hrouter->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->hrouter->buildUrL("blog/doadd") == "blog/add.shtml");
    }

    public function testGroup2()
    {
        Route::addGroup("blog",function(){
            Route::addRoute("list","list");
            Route::get("get/<id>","get");
            Route::get("add","doadd")->asSuffix("shtml");
        })->asMethod("get")->asPrefix("blog/")->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->hrouter->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");
        $this->assertTrue($this->hrouter->buildUrL("blog/doadd") == "blog/add.shtml");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/bxd","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/bxd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 2);


    }

    public function testGroup3()
    {
        Route::addGroup(function(){
            Route::addRoute("blog/list","list");
            Route::get("blog/get/<id>","get");
            Route::get("blog/add","doadd")->asSuffix("shtml");
        })->asPrefix("blog/")->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->hrouter->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->hrouter->buildUrL("blog/doadd") == "blog/add.shtml");
    }

    public function testRouterGroup()
    {
        $this->getRouter()->addGroup("blog",function(){
            Route::addRoute("list","list");
            Route::get("get/<id>","get");
            Route::get("add","doadd")->asSuffix("shtml");
        })->asPrefix("blog/")->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->hrouter->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");


        $routerRequest = $this->hrouter->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->hrouter->buildUrL("blog/doadd") == "blog/add.shtml");
    }

    public function testFlagGroup()
    {
        Route::addGroup("<module:\w+>/blog",function(){
            Route::addRoute("list","list");
            Route::get("get/<id>","get");
            Route::get("add","doadd")->asSuffix("shtml");
        })->asMethod("get")
            ->asPrefix("<module>/blog/")
            ->asParams(["id"=>"\d+"])
            ->asSuffix("html")
        ;

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/list");

        $this->assertTrue($this->hrouter->buildUrL("content/blog/list") == "content/blog/list.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 1);

        $this->assertTrue($this->hrouter->buildUrL("content/blog/get",["id"=>2]) == "content/blog/get/2.html");



        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/doadd");
        $this->assertTrue($this->hrouter->buildUrL("content/blog/doadd") == "content/blog/add.shtml");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/bxd","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/bxd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 2);
        $this->assertTrue($this->hrouter->buildUrL("content/blog/bxd") == "content/blog/bxd.html");

    }

    public function testGroupMergeRule()
    {
        Route::addGroup("<module:\w+>/blog",function(){
            Route::addRoute("list","list");
            Route::get("get/<id>","get");
            Route::get("getx/<id>","getx");
            Route::get("getb/<id>","getb");
            Route::get("add","doadd")->asSuffix("shtml");
        })->asMethod("get")
            ->asPrefix("<module>/blog/")
            ->asParams(["id"=>"\d+"])
            ->asSuffix("html")
            ->asMergeRule(5);

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/list");

        $this->assertTrue($this->hrouter->buildUrL("content/blog/list") == "content/blog/list.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 1);

        $this->assertTrue($this->hrouter->buildUrL("content/blog/get",["id"=>2]) == "content/blog/get/2.html");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/doadd");
        $this->assertTrue($this->hrouter->buildUrL("content/blog/doadd") == "content/blog/add.shtml");

        $routerRequest = $this->hrouter->parseRequest($this->createRequest("content/blog/bxd","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/bxd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 2);
        $this->assertTrue($this->hrouter->buildUrL("content/blog/bxd") == "content/blog/bxd.html");

    }


    public function testEmptyGroup()
    {
        Route::addGroup("blog",function(){
            Route::get("get/<id>","get");
        })->asAction("blog")->asPrefix("blog/")->asParams(["id"=>"\d+"]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->hrouter->buildUrL("blog/list") == "blog/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get" && ($routerRequest->getRouteParams())["id"] == 2);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2");
    }

    public function testEmptyGroup1()
    {
        Route::addGroup("blog",function(){
            Route::get("get/<id>","get");
        })->asAction("blog/index")->asPrefix("blog/")->asParams(["id"=>"\d+"]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index/list");
        $this->assertTrue($this->hrouter->buildUrL("blog/index/list") == "blog/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get" && ($routerRequest->getRouteParams())["id"] == 2);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2");
    }

    public function testEmptyGroup2()
    {
        Route::addGroup("blog",function(){
            Route::get("get/<id>","get");
        })->asAction("/blog/index")->asPrefix("blog/")->asParams(["id"=>"\d+"]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index");
        $this->assertTrue($this->hrouter->buildUrL("blog/index") == "blog");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index");
        $this->assertTrue($this->hrouter->buildUrL("blog/index") == "blog");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get" && ($routerRequest->getRouteParams())["id"] == 2);
        $this->assertTrue($this->hrouter->buildUrL("blog/get",["id"=>2]) == "blog/get/2");
    }





}
