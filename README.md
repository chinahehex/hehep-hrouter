# hehep-hrouter

目录

## 介绍
- hehep-hrouter 是一个PHP 路由工具组件,仿Yii2 路由规则
- 支持注释注解,PHP8原生注解

## 安装
- **gitee下载**:
```
git clone git@gitee.com:chinahehex/hehep-hrouter.git
```

- **github下载**:
```
git clone git@github.com:chinahehex/hehep-hrouter.git
```
- 命令安装：
```
composer require hehex/hehep-hrouter
```

## 组件配置
```php
$route_conf = [
    // 路由请求定义
    'routerRequest'=>[
        'class'=>'WebRouterRequest',
        'attr1'=>'参数1',
        'attr2'=>'参数2'
    ],
    
    // 路由解析器定义
    'customRouter'=>[
        'class'=>'hehe\core\hrouter\easy\EasyRouter',
        'suffix'=>true,// 全局前缀
        'domain'=>false,// 生产url 地址时是否显示域名
    ],
    
    // 路由规则列表
    'rules'=>[
            // 常规路由规则定义
            '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',// 设置id类型
            '<controller:\w+>/<action:\w+>/<id:\d+?>'=>'<controller>/<action>',// 设置id可选
            '<module:\w+/?><controller:\w+>/<action:\w+>/<id:\d+?>'=>'<module><controller>/<action>',// 设置module可选,并带有"/"
            
            // 数组格式
            ['uri'=>'<controller:\w+>/<action:\w+>','action'=>"<controller>/<action>","method"=>"get"],
            
            // 参数解析格式:"param" 参数解析地址 
            [
                'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
                'action'=>'<controller>/<action>',
                'pvar'=>'param',
                'prule'=>['class'=>'value','names'=>['id','status','type']]
            ],
    ],
    
   
];

```

## 路由管理器
- 说明
```
路由主要负责解析url(解析的结果又用户处理),生成url 地址
路由解析有两部分组成:地址解析+参数解析
```
- 示例代码
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

// 创建路由管理器对象
$hrouter = new RouteManager([]);

// 注册路由
Route::get("user/get","user/get","get");
$hrouter->addRoute("user/<id:\d+>","user/get","get");

// 解析URL地址，并返回结果(假如访问网址"user/123")
$routeReuqst = $hrouter->parseRequest();
$action = $routeReuqst->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeReuqst->getRouteParams();// 获取解析后的额外参数
// $action 结果:user/get,$params: ["id"=>123]

// 生成URL地址
$url = $hrouter->buildUrL("user/get",["id"=>122]);
// $url 结果:user/122

```

## 路由请求
- 说明
```
路由请求主要功能是为路由解析器提供数据,比如路由请求可以提供pathinfo地址,host,method 等参数
默认路由请求:
WebRouteRequest:常规web路由请求,比如php+nginx 环境下运行web项目
ConsoleRouteRequest:控制台路由请求,比如php脚本环境下运行web项目
```

- 自定义路由请求类
```php
namespace hehe\core\extend;
use hehe\core\hrouter\base\RouteRequest;
use Exception;
use he;

class AppRouteRequest extends RouteRequest
{
    // 定义获取pathinfo 地址的方法
    public function getPathinfo():string
    {
        return he::$ctx->hrequest->getPathInfo();
    }
    
    // 定义获取请求类型的方法
    public function getMethod():string
    {
        return strtolower(he::$ctx->hrequest->getMethod());
    }
    
    // 定义获取host的方法
    public function getHost():string
    {
        return he::$ctx->hrequest->getHostInfo();
    }
}
```
- 自定义路由请求示例
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\extend\AppRouteRequest;

// 创建路由管理器对象
$hrouter = new RouteManager([]);
$routeRequest = new AppRouteRequest();

// 解析URL地址
$hrouter->parseRequest($routeRequest);

// 获取解析结果
$action = $routeReuqst->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeReuqst->getRouteParams();// 获取解析后的额外参数

```

## 路由定义
### 说明
```
基本格式:["uri"=>"<controller:\w+>/<action:\w+>","action"=>"<controller>/<action>","method"=>"get"]
变量参数:格式<参数名>,<参数名:正则表达式> 或{参数名},{参数名:正则表达式},如<controller:\w+>
uri:路由规则,即解析http地址规则定义
action:路由地址,即控制器/操作，用于生成url地址
method:请求类型，多个请求类型逗号或|隔开
```

### 注册路由
### 常规路由
```php
use hehe\core\hrouter\Route;

Route::addRoute("user/add","user/doadd","get");
Route::addRoute("user/add","user/doadd","get,post");
Route::addRoute("user/add","user/doadd","get|post");
Route::get("user/add","user/doadd");
Route::get("<controller:\w+>/<action:\w+>","<controller>/<action>");

```
### 参数路由
```php
use hehe\core\hrouter\Route;

Route::get("user/<id:\d+>","user/get");
Route::get("user/<id>","user/get")->asParams(["id"=>"\d+"]);
// 正确url地址:user/122,错误url地址:user/add

Route::get("user/<action:\w+>","user/<action>");
// 正确url地址:user/get,user/list,错误url地址:user/123

Route::get("user/<action:get|list>","user/<action>");
// 正确url地址:user/get,user/list,错误url地址:user/edit

```

### 可选参数路由
```php
use hehe\core\hrouter\Route;

Route::get("user/add/<id:\d+?>","user/add");
Route::get("user/add/<id?>","user/add")->asParams(["id"=>"\d+"]);
Route::get("user/add/<id>","user/add")->asParams(["id"=>"\d+?"]);
// 正确url地址:user/add/122,user/add/

// 带"/" 路由
Route::get("user/add<id:/\d+?>","user/add");
Route::get("user/add<id?>","user/add")->asParams(["id"=>"/\d+"]);
Route::get("user/add<id?>","user/add")->asParams(["id"=>"/\d+?"]);
// 正确url地址:user/add/122,user/add

Route::get("<module:\w+/?>news/<action:get|list>","<module>news/<action>");
// 正确url地址:content/news/get,对应的路由地址:content/news/get
// 正确url地址:news/list,对应的路由地址:news/list

```

### 类方法路由
- 说明
```
基本格式:完整类路径@方法名
类方法路由只能用于解析uri地址,无法用于生成uri地址
```
- 示例代码
```php
Route::get("user/add","app/user/AdminController@add");
```

### 分组路由
- 说明
```
分组路由的目的:集中设置参数,提高匹配效率
分组路由规则:常规路由已设置过的参数无法被分组参数覆盖
```

- 示例代码
```php
use hehe\core\hrouter\Route;
Route::addGroup("blog",function(){
    Route::addRoute("list","blog/list");
    Route::get("get/<id>","blog/get");
    Route::post("add","blog/doadd");
    Route::get("add","blog/doadd");
});
```

- 设置参数
```php
use hehe\core\hrouter\Route;
Route::addGroup("blog",function(){
    Route::addRoute("list","list");
    Route::get("get/<id>","get");
    Route::post("add","doadd");
    Route::get("/hblog/add","doadd");
    Route::get("page","page/list");
})->asParams(["id"=>"\d+"])->asSuffix("html")->asPrefix("hblog/")->asMethod("get");

// 分组后相当于
// Route::addRoute("blog/list","hblog/list");
// Route::get("get/<id:\d+>","hblog/get");
// Route::post("blog/add","hblog/doadd");
// Route::post("hblog/add","hblog/doadd");
// Route::post("blog/page","hblog/page/list");


```


### 域名路由
- 说明
```

```

- 常规域名路由
```php
Route::get("http://<language:[a-z]+>.xxx.com/user/get","user/get");
```

- 分组域名路由
```php

```


### 默认参数路由
- 说明
```
提示:默认参数不出现在"路由地址"里
```

- 代码示例
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;
Route::get("<language:\w+/?>news/list","news/list")
    ->asDefaults(['language'=>'ch']);
$hrouter = new RouteManager([]);

// 解析的有效URL地址,ch/news/list,en/news/list,news/list
$routeRequest = $hrouter->parseRequest(new AppRouteRequest());
// 获取解析结果
$action = $routeReuqst->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeReuqst->getRouteParams();// 获取解析后的额外参数  
// URL地址: ch/news/list,$action:news/list, $params:["language"=>"ch"]
// URL地址: en/news/list,$action:news/list, $params:["language"=>"en"]
// URL地址: news/list,$action:news/list, $params:["language"=>"ch"]

// 生成URL地址
$url = $hrouter->buildUrL("news/list",["language"=>"ch"]);
// $url:"news/list"

$url = $hrouter->buildUrL("news/list",["language"=>"en"]);
// $url:"en/news/list"
```



## 路由规则参数
- 路由参数集合

参数 | 说明 | 方法名| 示例
----------|-------------|------------|------------
`domain`  | 是否域名检测 | asDomain | asDomain(),asDomain(true)
`suffix`  | 生成URL地址时是否加入后缀 | asSuffix | asSuffix(),asSuffix("html")
`method`  | 请求类型 | asMethod | asMethod("get"),asMethod("get|post")
`id`  | 路由唯一标识(全局唯一),用于快速生成URL地址 | asId | asId("news")
`uriParams`  | "路由规则"参数集合 | asParams | asParams(["id"=>"\d+"])
`defaults`  | 默认参数集合 | asDefaults | asDefaults(['language'=>'en']),asDefaults(['page'=>1])
  
- 示例代码
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

// 设置"路由规则"参数
Route::get("user/<id>","user/get")
    ->asParams(["id"=>"\d+"]);
    
// 设置生成URL后缀,生成的URL地址为:user/{id}.html  
Route::get("user/<id:\d+>","user/get")
    ->asSuffix();

// 设置路由唯一标识,生成地址时,直接使用"news"定位此条规则,避免了遍历查找
Route::get("news/<id:\d+>","news/get")
    ->asId("news");
$htouer = new RouteManager();
/** 使用"news"生成URL地址,最后地址为:"news/122" **/
$htouer->buildUrL("news",["id"=>122]);

// 设置默认参数
Route::get("news/list/<page:\d+>","news/list")
    ->asDefaults(["page"=>1]);

Route::get("<language:\w+/?>news/list","news/list")
    ->asDefaults(['language'=>'ch']);
```

## URL参数解析

### 参数split分隔格式
- 说明
```
参数解析分隔类属性如下:
pvar:URL地址参数解析名称,与"路由规则"中URL参数解析名称对应,比如uri:xxx/thread<hvar:(.*)>,pvar:hvar
mode:参数数量类型,fixed:固定参数,dynamic:动态参数
固定参数:fixed,如设置names=['id','status'=>"0",'type'],
    URL参数格式1,如thread-119781-1-1.html,错误格式thread-119781-1.html
    生成URL格式1:["id"=>122,"type"=>1],得到的URL:xxxx/thread-122-0-1.html
    
动态参数:dynamic,如设置names=["id",'status'=>"0",'type']
    URL参数格式1:如thread-119781.html,解析后得到的参数$params = ["id"=>119781,"status"=>0];
    URL参数格式2:如thread-119781-1.html,解析后得到的参数$params = ["id"=>119781,"status"=>1];
    URL参数格式3:如thread-119781-1-1.html,解析后得到的参数$params = ["id"=>119781,"status"=>1,"type"=>1];
    生成URL格式1:["id"=>122,"type"=>1],得到的URL:xxxx/thread-122-0-1.html
    生成URL格式2:["id"=>122],得到的URL:xxxx/thread-122-0.html
    生成URL格式3:["id"=>122,"status"=>1,"type"=>1],得到的URL:xxxx/thread-122-1-1.html
        
names:参数项名称，默认值，以及顺序定义,如['id','status'=>"0",'type']
flag:参数项之间的分隔符,默认是中划线-,比如"thread-122-1-1.html"地址中的"122-1-1"
prefix:参数前缀,默认是中划线-,比如"thread-122-1-1.html"地址中的122前面的中划线-

```

- 示例代码
```php
use hehe\core\hrouter\Route;

// 动态参数类型,解析的参数格式如下:thread-119781-1-1.html
Route::get([
    'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
    'action'=>'<controller>/<action>',
    'pvar'=>'param',
    'prule'=>[
        'class'=>'split',// 参数解析器类路径
        'mode'=>'dynamic',// fixed:固定参数,dynamic:动态参数
        // 所有参数的默认值以及顺序,如thread-{id}-{status}-{type}.html
        'names'=>['id','status'=>"0",'type'],
    ]
]);

// URL地址:news/get/thread-119781-1-1.html,$action::news/get,$params:["id"=>119781,"status"=>1,"type"=>1]
// URL地址:news/get/thread-119781.html,$action::news/get,$params:["id"=>119781,"status"=>0]
// 生成URL:["id"=>122,"type"=>1],得到的URL:xxx/thread-122-0-1.html
// 生成URL:["id"=>122],得到的URL:xxx/thread-122-0.html

// 固定参数类型
Route::get([
    'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
    'action'=>'<controller>/<action>',
    'pvar'=>'param',
    'prule'=>[
        'class'=>'split',// 参数解析器类路径
        'mode'=>'fixed',// fixed:固定参数,dynamic:动态参数
        // 所有参数的默认值以及顺序,如thread-{id}-{status}-{type}.html
        'names'=>['id','status'=>"0",'type'],
    ]
]);

// URL地址:news/get/thread-119781-1-2.html,$action::news/get,$params:["id"=>119781,"status"=>1,"type"=>2]
// URL地址:news/get/thread-119781-0-1.html,$action::news/get,$params:["id"=>119781,"status"=>0,"type"=>2]
// 生成URL:["id"=>122],得到的URL:xxx/thread-122-0.html
// 生成URL:["id"=>122,"type"=>1],得到的URL:xxxx/thread-122-0-1.html

```

### PATHINFO分隔格式
- 说明
```
基本格式:名称/值1/名称/值2/名称/值3，如:news/get/id/1/status/1
参数解析类属性如下:
valueSplit:参数名与值的分隔符,默认"/",如id/1
paramSplit:参数与参数的分隔符,默认"/",如id/1/status/1,id-1/status-1
prefix:参数前缀,默认""
names:参数项名称，默认值，以及顺序定义,如['id','status'=>"0",'type']
```
- 示例代码
```php
use hehe\core\hrouter\Route;

// 解析的参数格式如下:news/get/id/1/status/1
Route::get([
    'uri'=>'<controller:\w+>/<action:\w+>/<param:\w+>',
    'action'=>'<controller>/<action>',
    'pvar'=>'param',
    'prule'=>[
        'class'=>'pathinfo',// 参数解析器类路径
        // 所有参数的默认值以及顺序
        'names'=>['id','status','type'],
    ]
]);

// URL地址:news/get/id/122/status/1/type/1.html,$action::news/get,$params:["id"=>122,"status"=>1,"type"=>1]
// URL地址:news/get/id/122/type/1.html,$action::news/get,$params:["id"=>122,"type"=>1]
// URL地址:news/get/id/122/status/1.html,$action::news/get,$params:["id"=>122,"status"=>1]

// 生成URL:["id"=>122,"status"=>1],得到的URL:xxx/id/122/status/1
// 生成URL:["id"=>122,"type"=>1,"status"=>1],得到的URL:xxx/id/122/status/1/type/1


```

## Url生成
### 常规生成URL
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

Route::get("news/add","news/doadd");
Route::get("news/<id:\d+>","news/get");
Route::get("news/search/conf<params:.*>","news/search")
->asOptions(["pvar"=>"param","prule"=>["class"=>'split','names'=>["catid"=>0,"status"=>0,] ]]);
Route::get("<controller:\w+>/<action:\w+>","<controller>/<action>");

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news/doadd");
// $url:news/add

$url = $hrouter->buildUrL("news/get",["id"=>1]);
// $url:news/1

$url = $hrouter->buildUrL("news/list",["id"=>1]);
// $url:news/list?id=1

$url = $hrouter->buildUrL("news/search",["catid"=>122,"status"=>1]);
// $url:news/search/conf-122-1

```

### 路由标识生成URL
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;
Route::get("news/<id:\d+>","news/get")->asId("news_get");

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news_get",["id"=>2]);
// $url:news/2

```
### 生成带后缀URL
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;
Route::get("news/<id:\d+>","news/get")->asSuffix("html");
Route::get("news/list","news/get")->asSuffix();

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news/get",["id"=>2]);
// $url:news/2.html

$url = $hrouter->buildUrL("news/get.shtml",["id"=>2]);
// $url:news/2.htmls

$url = $hrouter->buildUrL("news/get",["id"=>2],["suffix"=>"shtml"]);
// $url:news/2.htmls

$url = $hrouter->buildUrL("news/list");
// $url:news/list.html

$url = $hrouter->buildUrL("news/list",[],['suffix'=>"shtml"]);
// $url:news/list.htmls

```

### 生成带域名URL
```php
use hehe\core\hrouter\RouteManager;

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news/get",["#"=>"add"],['suffix'=>"html"]);
// $url:news/get.html#add

```

### 生成锚点
```php
use hehe\core\hrouter\RouteManager;

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news/get",["#"=>"add"],['suffix'=>"html"]);
// $url:news/get.html#add

```

## restful规则
```php
use hehe\core\hrouter\Route;

// 通用格式
Route::get("<controller:\w+>","<controller>/index");
Route::get("<controller:\w+>/create","<controller>/create");
Route::post("<controller:\w+>","<controller>/save");
Route::get("<controller:\w+>/<id:\d+>","<controller>/read");
Route::get("<controller:\w+>/<id:\d+>/edit","<controller>/edit");
Route::put("<controller:\w+>/<id:\d+>","<controller>/update");
Route::delete("<controller:\w+>/<id:\d+>","<controller>/delete");

// 特定地址格式
Route::get("blog","blog/index");
Route::get("blog/create","blog/create");
Route::post("blog","blog/save");
Route::get("blog/<id:\d+>","blog/read");
Route::get("blog/<id:\d+>/edit","blog/edit");
Route::put("blog/<id:\d+>","blog/update");
Route::delete("blog/<id:\d+>","blog/delete");

```
## 注解路由
- 说明
```
注解器:hehe\core\hrouter\annotation\Route
注解类方法:如果"路由规则"以"/"开头，则表示完整的路由规则,如非"/"开头,其路由规则最终会带上类注解“路由规则”
  如:类路由规则:admin,方法路由规则doadd,则最终的路由规则为:"admin/doadd"
  如:类路由规则:admin,方法路由规则/config/set,则最终的路由规则为:"/config/set"
```
- 用于类
```php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;
/**
 * @Route("admin")
 * 相当于:RouteManager::addRoute("admin<route:.*?>","admin<route>");
 */
class AdminController
{
    // 访问此方法:"admin/save"
    public function saveAction(){}
}
```

- 用于类方法
```php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;

#[Route("admin")]
class AdminController
{

    /**
     * @Route("doadd")
     * 相当于:RouteManager::addRoute("admin/doadd","admin/add");
     */
    public function addAction(){}

    /**
     * 相当于:RouteManager::addRoute("admin/<id:\d+>","admin/get"); 
     */
     #[Route("/admin/<id:\d+>")]
    public function getAction(){}
    
    /**
     * 
     */
    public function saveAction(){}

}
```


## 扩展路由






    










