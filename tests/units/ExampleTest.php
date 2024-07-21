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
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("index/name"));
        $this->assertTrue($routerRequest->getRouteUrl() == "index/name");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/add?id=1"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/add/id/1"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add/id/1");
    }

    public function testAddRule()
    {
        $this->getRouter()->addRoute("<controller:\w+>/<action:\w+>",'<controller>/<action>');
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/add"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add");
    }

    public function testRuleParam()
    {
        $this->getRouter()->addRoute("<controller:\w+>/<id:\d+>",'<controller>/detail');
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/1"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/detail");

        $url = $this->getRouter()->buildUrl('user/detail',['id'=>1]);
        $this->assertTrue($url == "user/1");
    }

    public function testRuleDate()
    {
        $this->getRouter()->addRoute('news/<year:\d{4}>/<category>','news/<category>');
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/2014/list"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && $params['year'] == '2014');

        $url = $this->getRouter()->buildUrl('news/list',['year'=>2015]);
        $this->assertTrue($url == "news/2015/list");
    }

    public function testMoreRule()
    {
        $this->getRouter()->addRoute('<controller:(news|evaluate)>/<id:\d+>/<action:(add|edit|del)>','<controller>/<action>');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/1/add"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/add" && $params['id'] == '1');

        $url = $this->getRouter()->buildUrl('news/add',['id'=>2]);
        $this->assertTrue($url == "news/2/add");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/1/edit"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/edit" && $params['id'] == '1');

        $url = $this->getRouter()->buildUrl('news/edit',['id'=>2]);
        $this->assertTrue($url == "news/2/edit");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("evaluate/1/edit"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "evaluate/edit" && $params['id'] == '1');

        $url = $this->getRouter()->buildUrl('evaluate/edit',['id'=>2]);
        $this->assertTrue($url == "evaluate/2/edit");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/1/edit"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/1/edit");

    }

    public function testHostRule()
    {
        $this->getRouter()->addRoute('http://www.hehep.cn/news/<id:\d+>','news/get');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/1",'','http://www.hehep.cn'));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/get" && $params['id'] == '1');
        $url = $this->getRouter()->buildUrl('news/get',['id'=>2]);
        $this->assertTrue($url == "http://www.hehep.cn/news/2");
    }

    public function testHost1Rule()
    {
        $this->getRouter()->addRoute('http://<module:\w+>.hehep.cn/news/<id:\d+>','<module>/news/get');
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/1",'','http://content.hehep.cn'));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/news/get" && $params['id'] == '1');
        $url = $this->getRouter()->buildUrl('content/news/get',['id'=>2]);
        $this->assertTrue($url == "http://content.hehep.cn/news/2");
    }

    public function testHost2Rule()
    {
        $this->getRouter()->addRoute('news/<id:\d+>','<module>/news/get')->asDomain("http://<module:\w+>.hehep.cn");
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/1",'','http://content.hehep.cn'));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/news/get" && $params['id'] == '1');
        $url = $this->getRouter()->buildUrl('content/news/get',['id'=>2]);
        $this->assertTrue($url == "http://content.hehep.cn/news/2");
    }

    public function testHost3Rule()
    {
        $this->getRouter()->addRoute('news/<id:\d+>','news/get')->asDomain("http://www.hehep.cn");
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/1",'','http://www.hehep.cn'));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/get" && $params['id'] == '1');
        $url = $this->getRouter()->buildUrl('news/get',['id'=>2]);
        $this->assertTrue($url == "http://www.hehep.cn/news/2");
    }

    public function testHParamRule1()
    {
        // thread-119781-1-1.html
        $this->getRouter()->addRoute('<controller:\w+>/<action:\w+>/thread-<id:\d+>-<status:\d+>-<type:\d+>','<controller>/<action>');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/add/thread-121-1-2"));

        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);

        $this->assertTrue($url == "news/add/thread-122-1-1");
    }

    public function testHParamRule2()
    {
        // thread-119781-1-1.html
        //$this->getRouter->register('<controller:\w+>/<action:\w+>/thread<param:(-?.*)>','<controller>/<action>');


        $this->getRouter()->addRoute([
            'uri'=>'user/list/blog<param:.*>',
            'action'=>'user/list',
            'prule'=>['pvar'=>'param','class'=>'split','names'=>[
                'id'=>["regex"=>'\d+',"defval"=>"1"],
                'status'=>["regex"=>'\d+',"defval"=>"1"],
                'type']]
        ]);

        $this->getRouter()->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
            'action'=>'<controller>/<action>',
            'prule'=>['pvar'=>'param','class'=>'split','names'=>['id','status','type']]
        ]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');
        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/list/blog-122-11-2"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/list"
            && $params['id'] == '122' && $params['status'] == '11' && $params['type'] == '2');
        $url = $this->getRouter()->buildUrl('user/list',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "user/list/blog-122-1-1");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/list/blog-122-ch-2"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/list"
            && $params['id'] == '122' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('user/list',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "user/list/blog-122-1-1");

    }

    public function testHParamRule4()
    {
        // thread-119781-1-1.html
        //$this->getRouter->register('<controller:\w+>/<action:\w+>/thread<param:(-?.*)>','<controller>/<action>');
        $this->getRouter()->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:(.*)>',
            'action'=>'<controller>/<action>',
            'prule'=>['pvar'=>'param','class'=>'split','names'=>['id','status','type'=>"0"]]
        ]);
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-0");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122--1");

    }

    public function testHParamRule6()
    {
        // thread-119781-1-1.html
        //$this->getRouter->register('<controller:\w+>/<action:\w+>/thread<param:.*>','<controller>/<action>');
        $this->getRouter()->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
            'action'=>'<controller>/<action>',
            'prule'=>['pvar'=>'param','class'=>'split','names'=>['id','status'=>0,'type'=>"0"]]
        ]);
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-0");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-0-1");
    }

    public function testHParamRule7()
    {
        // 动态参数
        // thread-119781-1-1.html
        //$this->getRouter->register('<controller:\w+>/<action:\w+>/thread<param:(-?.*)>','<controller>/<action>');
        $this->getRouter()->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/thread<param:(.*)>',
            'action'=>'<controller>/<action>',
            'prule'=>['pvar'=>'param','class'=>'split','mode'=>'dynamic','names'=>['id','status'=>"0",'type']]
        ]);
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/add/thread-121-1-2"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "news/add"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1-1");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "news/add/thread-122-1");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122,'type'=>1]);
        $this->assertTrue($url == "news/add/thread-122-0-1");

        $url = $this->getRouter()->buildUrl('news/add',['id'=>122]);
        $this->assertTrue($url == "news/add/thread-122");
    }

    public function testHParamRule3()
    {
        // user/get/id/1/status/1/type/1/

        $this->getRouter()->addRoute([
            'uri'=>'user/list/blog/<param:(.*)>',
            'action'=>'user/list/blog',
            'prule'=>['pvar'=>'param','class'=>'pathinfo','names'=>[
                'id'=>["regex"=>'\d+',"defval"=>"1"],
                'status'=>["regex"=>'\d+',"defval"=>"0"],
                'type']]
        ]);

        $this->getRouter()->addRoute([
            'uri'=>'<controller:\w+>/<action:\w+>/<param:(.*)>',
            'action'=>'<controller>/<action>',
            'prule'=>['pvar'=>'param','class'=>'pathinfo','names'=>['id','status','type']]
        ]);

        //$this->getRouter->register('<controller:\w+>/<action:\w+>/<param:(.*)>','<controller>/<action>');
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get/id/121/status/1/type/2"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "user/get/id/122/status/1/type/1");

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122,'status'=>1,'type'=>1,'role'=>1]);
        $this->assertTrue($url == "user/get/id/122/status/1/type/1?role=1");

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122,'status'=>1]);
        $this->assertTrue($url == "user/get/id/122/status/1");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/list/blog/id/121/status/1/type/2"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/list/blog"
            && $params['id'] == '121' && $params['status'] == '1' && $params['type'] == '2');

        $url = $this->getRouter()->buildUrl('user/list/blog',['id'=>122,'status'=>1,'type'=>1]);
        $this->assertTrue($url == "user/list/blog/id/122/status/1/type/1");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/list/blog/id/121/status/ch/type/2"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/list/blog"
            && $params['id'] == '121' && $params['status'] == '0' && $params['type'] == '2');

    }

    public function testDefaultParam()
    {
        // user/get/id/1/status/1/type/1/

        $this->getRouter()->addRoute([
            'uri'=>'<language:\w+/?><controller:\w+>/<action:\w+>/<id:\d+>',
            'action'=>'<language><controller>/<action>',
            'defaults'=>['language'=>'en'],
        ]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122,'status'=>1,'language'=>'ch']);


        $this->assertTrue($url == "ch/user/get/122?status=1");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/user/get/1"));
        $params = $routerRequest->getRouteParams();


        $this->assertTrue($routerRequest->getRouteUrl() == "ch/user/get"
            && $params['id'] == '1');


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("en/user/get/1"));
        $params = $routerRequest->getRouteParams();



        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

    }

    public function testParamRule8()
    {
        // user/get/id/1/status/1/type/1/

        $this->getRouter()->addRoute([
            'uri'=>'<module:\w+/?><controller:\w+>/<action:\w+>/<id:\d+>',
            'action'=>'<module><controller>/<action>',
            'defaults'=>['language'=>'en']
        ]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122,'status'=>1,'language'=>'ch']);
        $this->assertTrue($url == "user/get/122?status=1&language=ch");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/user/get/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "content/user/get"
            && $params['id'] == '1');

        $url = $this->getRouter()->buildUrl('content/user/get',['id'=>122,'status'=>1,'language'=>'ch']);
        $this->assertTrue($url == "content/user/get/122?status=1&language=ch");


        $url = $this->getRouter()->buildUrl('user/get',['id'=>122,'status'=>1,'language'=>'ch']);
        $this->assertTrue($url == "user/get/122?status=1&language=ch");
    }

    public function testDuoRule()
    {

        $this->getRouter()->addRoute('user/<action:get|list>','user1/<action>');
        $this->getRouter()->addRoute('<controller:\w+>/<action:\w+>','<controller>/<action>');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user1/get");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/add"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user/add");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("new/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "new/get");
    }

    public function testMethodRule()
    {
        $this->getRouter()->addRoute('user/<action:get|list>','user1/<action>','post');
        $this->getRouter()->addRoute('user/<action:get|list>','user2/<action>','get');
        $this->getRouter()->addRoute('user3/<action:get|list>','user3/<action>');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get",'post'));
        $this->assertTrue($routerRequest->getRouteUrl() == "user1/get");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get",'get'));
        $this->assertTrue($routerRequest->getRouteUrl() == "user2/get");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user3/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "user3/get");
    }

    public function testClassRule()
    {
        $this->getRouter()->addRoute('adminuser/<action:get|list>',AdminController::class .'@<action>');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("adminuser/get"));
        $this->assertTrue($routerRequest->getRouteUrl() == AdminController::class . "@get");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("adminuser/list"));
        $this->assertTrue($routerRequest->getRouteUrl() == AdminController::class . "@list");

    }

    public function testSetFlag()
    {
        $this->getRouter()->addRoute('user/<id>','user/get');
        $this->getRouter()->addRoute('role/<id>','role/get')
            ->asParams(["id"=>'\d+']);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get" && $params["id"] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/list"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get" && $params["id"] == "list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("role/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "role/get" && $params["id"] == 1);


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("role/list"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "role/list" && !isset($params["id"]));
    }

    public function testWenFlag()
    {
        $this->getRouter()->addRoute('user<id:/\d+?>','user/get')
                                //->asParams(["id"=>'\d+'])
        ;
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/1"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get");

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122]);

        $this->assertTrue($url == "user/122");

    }

    public function testFlag1()
    {
        $this->getRouter()->addRoute([
            'uri'=>'<module?><controller:\w+>/<action:\w+>/<id:\d+>',
            'action'=>'<module><controller>/<action>',
        ])->asParams(["module"=>'\w+/']);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/user/get"
            && $params['id'] == '1');

    }

    public function testFlag2()
    {
        $this->getRouter()->addRoute([
            'uri'=>'<module:?><controller:\w+>/<action:\w+>/<id:\d+?>',
            'action'=>'<module><controller>/<action>',
        ])->asParams(["module"=>'\w+/?']);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && $params['id'] == '1');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/user/get/1"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "content/user/get"
            && $params['id'] == '1');

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get"));
        $params = $routerRequest->getRouteParams();

        $this->assertTrue($routerRequest->getRouteUrl() == "user/get"
            && !isset($params['id']) );
    }

    public function testFlag3()
    {
        $this->getRouter()->addRoute([
            'uri' => 'user/<id:\d+?>',
            'action' => 'user/get',
            'id'=>'new_id',
        ]);

        $url = $this->getRouter()->buildUrl('new_id',['id'=>122]);
        $this->assertTrue($url == "user/122");

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122]);
        $this->assertTrue($url == "user/122");

    }

    public function testOptions()
    {
        $this->getRouter()->addRoute([
            'uri' => 'user/{id:\d+?}',
            'action' => 'user/get',
        ])->asSuffix();

        $this->getRouter()->addRoute([
            'uri' => 'http://www.baidu.com/news/<id:\d+?>',
            'action' => 'news/get',
        ])->asSuffix();

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("user/get/1"));

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122]);
        $this->assertTrue($url == "user/122.html");

        $url = $this->getRouter()->buildUrl('user/get.html',['id'=>122]);
        $this->assertTrue($url == "user/122.html");

        $url = $this->getRouter()->buildUrl('user/get',['id'=>122],['suffix'=>'htmls']);
        $this->assertTrue($url == "user/122.htmls");

        $url = $this->getRouter()->buildUrl('news/get',['id'=>122]);
        $this->assertTrue($url == "http://www.baidu.com/news/122.html");
    }

    public function testRestful()
    {
        $this->getRouter()->get("blog","blog/index");
        $this->getRouter()->get("blog/create","blog/create");
        $this->getRouter()->post("blog","blog/save");
        $this->getRouter()->get("blog/{id:\d+}","blog/read");
        $this->getRouter()->get("blog/<id:\d+>/edit","blog/edit");
        $this->getRouter()->put("blog/<id:\d+>","blog/update");
        $this->getRouter()->delete("blog/<id:\d+>","blog/delete");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/create","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/create");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog","post"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/save");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/1","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/read" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/1/edit","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/edit" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/1","put"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/update" && $params['id'] == 1);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/1","delete"));
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

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");

        $this->assertTrue($this->getRouter()->buildUrL("blog/list") == "blog/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/add","post"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->getRouter()->buildUrL("blog/doadd") == "blog/add");
    }

    public function testGroup1()
    {
        Route::addGroup("blog",function(){
            Route::addRoute("list","blog/list");
            Route::get("get/<id>","blog/get");
            Route::get("add","blog/doadd")->asSuffix("shtml");
        })->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->getRouter()->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->getRouter()->buildUrL("blog/doadd") == "blog/add.shtml");
    }

    public function testGroup2()
    {
        Route::addGroup("blog",function(){
            Route::addRoute("list","list");
            Route::get("get/{id}","get");
            Route::get("add","doadd")->asSuffix("shtml");
        })->asMethod("get")->asPrefix("blog/")->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->getRouter()->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");
        $this->assertTrue($this->getRouter()->buildUrL("blog/doadd") == "blog/add.shtml");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/bxd","get"));
        var_dump($routerRequest->getRouteUrl());
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

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->getRouter()->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->getRouter()->buildUrL("blog/doadd") == "blog/add.shtml");
    }

    public function testRouterGroup()
    {
        $this->getRouter()->addGroup("blog",function(){
            Route::addRoute("list","list");
            Route::get("get/<id>","get");
            Route::get("add","doadd")->asSuffix("shtml");
        })->asPrefix("blog/")->asParams(["id"=>"\d+"])->asSuffix("html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->getRouter()->buildUrL("blog/list") == "blog/list.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get",($routerRequest->getRouteParams())['id'] == 1);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2.html");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/doadd");

        $this->assertTrue($this->getRouter()->buildUrL("blog/doadd") == "blog/add.shtml");
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

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/list");

        $this->assertTrue($this->getRouter()->buildUrL("content/blog/list") == "content/blog/list.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 1);

        $this->assertTrue($this->getRouter()->buildUrL("content/blog/get",["id"=>2]) == "content/blog/get/2.html");



        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/doadd");
        $this->assertTrue($this->getRouter()->buildUrL("content/blog/doadd") == "content/blog/add.shtml");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/bxd","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/bxd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 2);

        $this->assertTrue($this->getRouter()->buildUrL("content/blog/bxd") == "content/blog/bxd.html");

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

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/list");

        $this->assertTrue($this->getRouter()->buildUrL("content/blog/list") == "content/blog/list.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/get/1","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 1);

        $this->assertTrue($this->getRouter()->buildUrL("content/blog/get",["id"=>2]) == "content/blog/get/2.html");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/add","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/doadd");
        $this->assertTrue($this->getRouter()->buildUrL("content/blog/doadd") == "content/blog/add.shtml");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/bxd","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/bxd");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("content/blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "content/blog/get",($routerRequest->getRouteParams())['id'] == 2);
        $this->assertTrue($this->getRouter()->buildUrL("content/blog/bxd") == "content/blog/bxd.html");

    }


    public function testEmptyGroup()
    {
        Route::addGroup("blog",function(){
            Route::get("get/<id>","get");
        })->asAction("blog")->asPrefix("blog/")->asParams(["id"=>"\d+"]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/list");
        $this->assertTrue($this->getRouter()->buildUrL("blog/list") == "blog/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get" && ($routerRequest->getRouteParams())["id"] == 2);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2");
    }

    public function testEmptyGroup1()
    {
        Route::addGroup("blog",function(){
            Route::get("get/<id>","get");
        })->asAction("blog/index")->asPrefix("blog/")->asParams(["id"=>"\d+"]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index/list");
        $this->assertTrue($this->getRouter()->buildUrL("blog/index/list") == "blog/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get" && ($routerRequest->getRouteParams())["id"] == 2);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2");
    }

    public function testEmptyGroup2()
    {
        Route::addGroup("blog",function(){
            Route::get("get/<id>","get");
        })->asAction("/blog/index")->asPrefix("blog/")->asParams(["id"=>"\d+"]);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index");
        $this->assertTrue($this->getRouter()->buildUrL("blog/index") == "blog");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/index");
        $this->assertTrue($this->getRouter()->buildUrL("blog/index") == "blog");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("blog/get/2","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "blog/get" && ($routerRequest->getRouteParams())["id"] == 2);
        $this->assertTrue($this->getRouter()->buildUrL("blog/get",["id"=>2]) == "blog/get/2");
    }

    public function testDomainGroup()
    {
        Route::addGroup("http://www.hehex.cn",function(){
            Route::get("news/list","news/list");
            Route::get("news/get/<id:\d+>","news/get");
        });

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/list","get","http://www.hehex.cn"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list");
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "http://www.hehex.cn/news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/get/2","get","http://www.hehex.cn"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/get");
        $this->assertTrue($this->getRouter()->buildUrL("news/get",['id'=>2]) == "http://www.hehex.cn/news/get/2");

    }

    public function testDomain1Group()
    {
        Route::addGroup("<_ssl:http|https>://www.hehex.cn",function(){
            Route::get("news/list","news/list");
            Route::get("news/get/<id:\d+>","news/get");
        })->asDefaults(['ssl'=>'http']);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/list","get","http://www.hehex.cn"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list");
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "http://www.hehex.cn/news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/get/2","get","http://www.hehex.cn"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/get");
        $this->assertTrue($this->getRouter()->buildUrL("news/get",['id'=>2]) == "http://www.hehex.cn/news/get/2");
        $this->assertTrue($this->getRouter()->buildUrL("news/get",['id'=>2,"ssl"=>"https"]) == "https://www.hehex.cn/news/get/2");
    }

    public function testDefaultVar()
    {
        Route::get("<language:\w+>/news/list","news/list")
            ->asDefaults(['language'=>'ch']);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("en/news/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'en');
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "ch/news/list");


        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/news/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'ch');

    }

    public function testDefault1Var()
    {


        Route::get("<lang:\w+/?>abc/list","<lang>abc/plist")
            ->asDefaults(['lang'=>'ch']);

        Route::get("<language:\w+/?>news/list","news/list")
            ->asDefaults(['language'=>'ch']);

        Route::get("<language:\w+/?>role/get<id:/\d+?>","role/get")
            ->asDefaults(['language'=>'ch']);

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("en/news/list","get"));

        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'en');
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/news/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'ch');

//        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/role/get/1","get"));
//        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/news/list","get"));

        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'ch');
        $this->assertTrue($this->getRouter()->buildUrL("news/list",["language"=>'ch']) == "news/list");
        $this->assertTrue($this->getRouter()->buildUrL("news/list",["language"=>'en']) == "en/news/list");
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("en/abc/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "en/abc/plist");

        $this->assertTrue($this->getRouter()->buildUrL("abc/plist",["lang"=>'en']) == "en/abc/list");
        $this->assertTrue($this->getRouter()->buildUrL("en/abc/plist") == "en/abc/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/abc/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "abc/plist");

        $this->assertTrue($this->getRouter()->buildUrL("abc/plist",["lang"=>'ch']) == "abc/list");
        $this->assertTrue($this->getRouter()->buildUrL("ch/abc/plist") == "abc/list");
    }

    public function testDateVar()
    {
        Route::get("news/list/<year:\d{4}>/<month:\d{2}>/<day:\d{2}>", "news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("news/list/2024/07/05","get"));
        $params = $routerRequest->getRouteParams();
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list"
            && $params['year'] == '2024' && $params['month'] == '07') && $params['day'] == '05';

    }

    public function testOtherFlag()
    {
        Route::get("<lang:\w+/?>abc/list","<lang>abc/plist")
            ->asDefaults(['lang'=>'ch']);

        Route::get("<language:\w+/?>news/list","news/list")
            ->asDefaults(['language'=>'ch']);

        Route::get("<language:\w+/?>role/get<id:/\d+?>","role/get")
            ->asDefaults(['language'=>'ch']);
        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("en/news/list","get"));

        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'en');
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/news/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'ch');

//        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/role/get/1","get"));
//        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/news/list","get"));

        $this->assertTrue($routerRequest->getRouteUrl() == "news/list" && ($routerRequest->getRouteParams())['language'] == 'ch');
        $this->assertTrue($this->getRouter()->buildUrL("news/list",["language"=>'ch']) == "news/list");
        $this->assertTrue($this->getRouter()->buildUrL("news/list",["language"=>'en']) == "en/news/list");
        $this->assertTrue($this->getRouter()->buildUrL("news/list") == "news/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("en/abc/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "en/abc/plist");

        $this->assertTrue($this->getRouter()->buildUrL("abc/plist",["lang"=>'en']) == "en/abc/list");
        $this->assertTrue($this->getRouter()->buildUrL("en/abc/plist") == "en/abc/list");

        $routerRequest = $this->getRouter()->parseRequest($this->createRequest("ch/abc/list","get"));
        $this->assertTrue($routerRequest->getRouteUrl() == "abc/plist");

        $this->assertTrue($this->getRouter()->buildUrL("abc/plist",["lang"=>'ch']) == "abc/list");
        $this->assertTrue($this->getRouter()->buildUrL("ch/abc/plist") == "abc/list");
    }


}
