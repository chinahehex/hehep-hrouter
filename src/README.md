# hehep-hrouter

## 介绍
- hehep-hrouter 是一个PHP 路由工具组件
- 支持注释注解,PHP8原生注解
- 支持分组路由
- 支持key/value结构存储路由,,快速定位路由，提高匹配效率
- 支持合并路由解析,提高匹配效率
- 支持路由缓存
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
        'class'=>'hehe\core\hrouter\fast\FastRouter',
        'suffix'=>true,// 全局前缀
        'domain'=>false,// 生产url 地址时是否显示域名
        'mergeRule'=>false, // 是否合并路由解析
        'mergeLen'=>0,// 一次合并的条数
        'lazy'=>true,// 是否延迟加载规则
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
                'prule'=>['pvar'=>'param','class'=>'value','names'=>['id','status','type']]
            ],
    ],
    
   
];

```

## 路由管理器
- 说明
```
路由管理器:收集路由信息,配置路由请求,管理路由解析器,解析url地址,生成url 地址
```
- 路由管理器操作方式
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

// 创建路由管理器对象
$hrouter = new RouteManager([]);

// 设置路由请求,路由解析器
$hrouter->setRouterConfig([])->setRouteRequest([]);

// 收集路由
Route::get("user/get","user/get");
$hrouter->addRoute("user/<id:\d+>","user/get","get");

// 解析URL地址，并返回结果(假如访问网址"user/123")
$routeRequest = $hrouter->parseRequest();
$action = $routeRequest->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeRequest->getRouteParams();// 获取解析后的额外参数
$rule = $routeRequest->getRouteRule();// 获取匹配到的路由规则对象
// $action 结果:user/get,$params: ["id"=>123]

// 生成URL地址
$url = $hrouter->buildUrL("user/get",["id"=>122]);
// $url 结果:user/122

```

- 路由器操作方式
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

// 创建路由管理器对象
$hrouter = Route::intiRoute();

// 设置路由请求,路由解析器
$hrouter->setRouterConfig([])->setRouteRequest([]);
// Route::intiRoute(AppRouteRequest:class)
// Route::intiRoute('WebRouteRequest')

// 收集路由
Route::get("user/get","user/get");
Route::addRoute("user/<id:\d+>","user/get","get");

// 解析URL地址，并返回结果(假如访问网址"user/123")
$routeRequest = Route::parseRequest();
$action = $routeRequest->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeRequest->getRouteParams();// 获取解析后的额外参数
$rule = $routeRequest->getRouteRule();// 获取匹配到的路由规则对象
// $action 结果:user/get,$params: ["id"=>123]

// 生成URL地址
$url = Route::buildUrL("user/get",["id"=>122]);
// $url 结果:user/122

```


## 路由请求
- 说明
```
路由请求类:存储路由解析器需要的数据,比如路由请求对象可以提供pathinfo地址,host,method 等数据
默认路由请求类:
WebRouteRequest:常规web路由请求,比如php+nginx 环境下运行web请求
ConsoleRouteRequest:控制台路由请求,比如php脚本环境下运行脚本请求

```

- 定义路由请求类
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
- 路由请求使用示例
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\extend\AppRouteRequest;

// 创建路由管理器对象
$hrouter = new RouteManager([]);

// 设置路由请求配置
$hrouter->setRouteRequest([]);

// 创建路由请求对象
$hrouter->createRouteRequest();

// 创建路由请求对象
$routeRequest = new AppRouteRequest();

// 解析URL地址
$hrouter->parseRequest($routeRequest);

// 获取解析结果
$action = $routeRequest->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeRequest->getRouteParams();// 获取解析后的额外参数
$rule = $routeRequest->getRouteRule();// 获取匹配到的路由规则对象

```

## 路由解释器
- 说明
```
路由解释器类:负责调度规则,解析路由规则,生成后缀,域名等等
路由解析由两部分组成:地址(pathinfo)解析+参数解析
    格式:user/list/blog-12-12-12.html,pathinfo:user/list/blog,参数:-12-12-12
路由解释器属性:
suffix:url 是否加入上后缀,默认值:false,格式:html
domain:url 是否加入域名 地址时是否显示域名,格式:http://www.xxx.cn
mergeRule: 是否合并路由解析,默认值:false,此参数会同步至分组
mergeLen:一次合并的条数,0 表示全部
lazy:是否延迟加载规则,默认值:false

```

- 定义路由解释器类
```php
namespace hehe\core\hrouter\fast;

use hehe\core\hrouter\base\Router;
use hehe\core\hrouter\base\RouteRequest;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\Route;

class FastRouter extends Router
{
    // 收集路由规则
    public function addRule(Rule $rule):void
    {
    
    }
    
    // 解析路由请求,调用路由规则
    public function parseRequest(RouteRequest $routeRequest)
    {
    
    }
    
    // 匹配action规则
    public function matchAction(string $uri = '',array $params = [],array $options = [])
    {
    
    }
}

```

- 路由解析器使用示例
```php
use hehe\core\hrouter\RouteManager;
// 创建路由管理器对象
$hrouter = new RouteManager([]);

$hrouter->setRouterConfig([
    'class'=>'hehe\core\hrouter\fast\FastRouter',
    'suffix'=>false,// url 地址后缀
    // url 是否加入域名
    'domain'=>false,// 生产url 地址时是否显示域名,
    // 是否合并路由解析
    'mergeRule'=>false,
    // 一次合并的条数
    'mergeLen'=>0,
    // 是否延迟加载规则
    'lazy'=>true,
]);

// 获取路由解释器对象
$router = $hrouter->getRouter();

// 解析URL地址
$routeRequest = $hrouter->parseRequest();

// 获取解析结果
$action = $routeRequest->getRouteUrl();//  获取解析后的"路由地址"
$params = $routeRequest->getRouteParams();// 获取解析后的额外参数
$rule = $routeRequest->getRouteRule();// 获取匹配到的路由规则对象

```

## 路由缓存
- 说明
```
路由缓存类:由于路由规则是动态生成的,所以需要缓存路由规则,以便下次使用
路由缓存属性:
routeFile:路由文件列表
cacheDir:路由缓存目录
cacheFile:路由缓存文件,如果路由缓存文件未设置,则生成路由缓存文件
timeout:路由缓存过期时间,默认值:0,0 表示不过期
```
- 路由缓存使用示例
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

// 设置路由缓存配置
Route::setRouteCache([
    'cacheDir'=>'/www/cache/route',
    'timeout'=>30,
]);

// 获取路由缓存对象
$routeCache = Route::getRouteCache();
$routeCache->addRouteFile('file1','file2');

// 导入路由文件
$routeCache->requireRouteFile();

// 检查缓存是有效
$routeCache->checkCacheStatus();

// 注入缓存路由至路由收集器
$routeCache->injectRoute();

// 开始解析路由请求
$routeRequest = Route::parseRequest();

// 生成URL地址
Route::buildUrL('user/get',["id"=>123]);


```

## 路由定义
### 说明
```
基本格式:["uri"=>"<controller:\w+>/<action:\w+>","action"=>"<controller>/<action>","method"=>"get"]
伪代码: Route::get("路由规则","路由地址");
变量参数:格式<变量名>,<变量名:正则表达式> 或{变量名},{变量名:正则表达式},如<controller:\w+>
uri:路由规则,即匹配http地址的规则表达式
action:路由地址,即匹配"控制器/操作"的表达式，常用于生成url地址
method:请求类型，多个请求类型逗号或|隔开,目前支持以下请求类型
       *(任意类型),GET,POST,PUT,DELETE,PATCH,HEAD
```

### 常规路由
```php
use hehe\core\hrouter\Route;

Route::addRoute("user/add","user/doadd");// 任意请求类型
Route::addRoute("user/add","user/doadd","*");// 任意请求类型
Route::addRoute("user/add","user/doadd","get");
Route::addRoute("user/add","user/doadd","get,post");
Route::addRoute("user/add","user/doadd","get|post");
Route::get("user/add","user/doadd");
Route::get("<controller:\w+>/<action:\w+>","<controller>/<action>");

```
### 变量路由
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

### 可选变量路由
- 说明
```
在变量表达式中末尾带?问号,标识此变量为可选，即可有可无
以下格式可设置为可选变量:
<id:\d+?>
<id?>
->asParams(["id"=>"\d+?"])
```

- 示例代码
```php
use hehe\core\hrouter\Route;

Route::get("user/add/<id:\d+?>","user/add");
Route::get("user/add/<id?>","user/add")->asParams(["id"=>"\d+"]);
Route::get("user/add/<id>","user/add")->asParams(["id"=>"\d+?"]);
// 正确url地址:user/add/122,user/add/

// 带"/" 路由
Route::get("user/add<id:/\d+?>","user/add");
Route::get("user/add<id?>","user/add")->asParams(["id"=>"/\d+"]);
Route::get("user/add<id>","user/add")->asParams(["id"=>"/\d+?"]);
// 正确url地址:user/add/122,user/add

Route::get("<module:\w+/?>news/<action:get|list>","<module>news/<action>");
// 正确url地址:content/news/get,对应的路由地址:content/news/get
// 正确url地址:news/list,对应的路由地址:news/list

```

### 私有变量路由
- 说明
```
私有变量格式:<_变量名:正则表达式>,变量名以下划线(_)开头
私有变量规则:私有变量只负责验证,不会出现在解析URL地后的参数里
```

- 示例代码
```php
use hehe\core\hrouter\Route;
$hrouter = new RouteManager();

Route::addGroup("<_ssl:http|https>://www.xxx.cn",function(){
    Route::get("news/list","news/list");
    Route::get("news/get/<id:\d+>","news/get");
})->asDefaults(['ssl'=>'http']);

// 解析http:http://www.xxx.cn/news/list,返回:url:news/news,$params:[]
// 解析http:https://www.xxx.cn/news/get/1,返回:url:news/get,$params:["id"=>1]

$url = $hrouter->buildUrL("news/list");
// $url:http://www.xxx.cn/news/list

$url = $hrouter->buildUrL("news/list",["ssl"=>'https']);
// $url:https://www.xxx.cn/news/list

```

### 默认变量路由
- 说明
```
提示:带问号(?)可选默认变量值不会现在"路由规则","路由地址"里 
```

- 非可选默认变量
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;
$hrouter = new RouteManager();

Route::get("<lang:\w+>/news/list","news/list")
            ->asDefaults(['lang'=>'ch']);

// 解析pathinfo:ch/news/list,返回:url:news/list,$params:["lang"=>'ch']
// 解析pathinfo:en/news/list,返回:url:news/list,$params:["lang"=>'en']

$url = $hrouter->buildUrL("news/list",["lang"=>"ch"]);
// $url:ch/news/list

$url = $hrouter->buildUrL("news/list",["lang"=>"en"]);
// $url:en/news/list

```

- 带问号(?)可选默认变量
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;
$hrouter = new RouteManager();

// 带?问号
Route::get("<lang:\w+/?>news/list","news/list")
            ->asDefaults(['lang'=>'ch']);
// 解析pathinfo:ch/news/list,返回:url:news/list,$params:["lang"=>'ch']
// 解析pathinfo:en/news/list,返回:url:news/list,$params:["lang"=>'en']
// 解析pathinfo:news/list,返回:url:news/list,$params:["lang"=>'ch']

$url = $hrouter->buildUrL("news/list",["lang"=>"ch"]);
// $url:news/list

$url = $hrouter->buildUrL("news/list",["lang"=>"en"]);
// $url:en/news/list

$url = $hrouter->buildUrL("news/list",["lang"=>"ch"]);
// $url:news/list


// 变量带?问号,且路由地址(action)带此变量
Route::get("<lang:\w+/?>abc/list","<lang>abc/plist")
    ->asDefaults(['lang'=>'ch']);
    
// 解析pathinfo:ch/news/list,返回:url:news/list,$params:[]
// 解析pathinfo:en/news/list,返回:url:en/news/list,$params:[]
// 解析pathinfo:news/list,返回:url:news/list,$params:[]
$url = $hrouter->buildUrL("news/list",["lang"=>"ch"]);
// $url:news/list

$url = $hrouter->buildUrL("news/list",["lang"=>"en"]);
// $url:en/news/list

$url = $hrouter->buildUrL("ch/news/list");
// $url:news/list


$url = $hrouter->buildUrL("en/news/list");
// $url:en/news/list

```

### 带域名路由
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

Route::get('http://www.hehep.cn/news/list','news/list');
Route::get('news/list','news/list')->asDomain("http://www.hehep.cn");

Route::get('http://user<userid:\d+>.hehep.cn/news/<id:\d+>','news/get');
Route::get('news/<id:\d+>','news/get')->asDomain("http://user<userid:\d+>.hehep.cn");

$hrouter = new RouteManager();

$uri = $hrouter->buildUrl('news/list');
// $uri:http://www.hehep.cn/news/list

$uri = $hrouter->buildUrl('news/get',['userid'=>2260,'id'=>1]);
// $uri:http://user2260.hehep.cn/news/1

```

### 绑定类路由
- 说明
```
基本格式:完整类路径@方法名
类方法路由只能用于解析uri地址,无法用于生成uri地址
```
- 示例代码
```php
Route::get("user/add","app/user/AdminController@add");
Route::get("user/<action:\w+>","app/user/AdminController@<action>");
```

### 常用变量表达式

表达式 | 说明 | 示例
:----------------|-------------|------------
`\w+`  | 由数字、26个英文字母,下划线 | 'user/\<action:\w+>'
`\d+`  | 非负整数（正整数 + 0） | 'user/\<id:\d+>'
`[a-z]+`  | 26个小写字母 | 'http://\<lang:[z-z]+>.xxx.cn'
`.+`,`.*`   | 任意字符 | 'user/get\<param:.+>'
`\d{4}`  | 日期格式 | news/list/\<year:\d{4}>/\<month:\d{2}>/\<day:\d{2}>
`[^\/]+`  | 匹配除了'/'以外的字符 | news/search/\<keyword:[^\/]+>



## 路由规则参数

- 路由参数集合

参数 | 说明 | 方法名| 示例
----------|-------------|------------|------------
`domain`  | 是否域名检测 | asDomain | asDomain(true)
`suffix`  | 生成URL是否加入后缀 | asSuffix | asSuffix("html")
`method`  | 请求类型 | asMethod | asMethod("get")
`id`  | 路由唯一标识 | asId | asId("news")
`params`  | "路由规则"变量集合 | asParams | asParams(["id"=>"\d+"])
`defaults`  | 默认变量集合 | asDefaults | asDefaults(['lang'=>'en'])
`completeMatch`  | 是否完全匹配路由规则,默认完全匹配 | asCompleteMatch | asCompleteMatch(false)
`prule`  | Url参数配置规则 | asParamsRule | asParamsRule(['pvar'=>'params','class'=>'xxx'])

- 示例代码
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

// 设置"路由规则"变量
Route::get("user/<id>","user/get")
    ->asParams(["id"=>"\d+"]);
    
// 设置生成URL后缀,生成的URL地址为:user/{id}.html  
Route::get("user/<id:\d+>","user/get")
    ->asSuffix();

// 设置路由唯一标识,生成地址时,直接使用"news_id"定位此条规则,避免了遍历查找
Route::get("news/<id:\d+>","news/get")
    ->asId("news_id");
    
$htouer = new RouteManager();
/** 使用"news"生成URL地址,最后地址为:"news/122" **/
$htouer->buildUrL("news_id",["id"=>122]);

// 设置默认变量
Route::get("news/list/<page:\d+>","news/list")
    ->asDefaults(["page"=>1]);

Route::get("<language:\w+/?>news/list","news/list")
    ->asDefaults(['language'=>'ch']);
```

## 分组路由
- 说明
```
分组路由目的:集中统一设置参数,提高匹配效率
分组路由规则:子路由参数优先于分组路由参数,即分组设置的参数无法覆盖子路由设置的参数
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

### 设置规则参数
```php
use hehe\core\hrouter\Route;
Route::addGroup("blog",function(){
    Route::addRoute("list","list");
    Route::get("get/<id>","get")->asParams(["id"=>"\d+"]);
    Route::post("add","doadd");
    Route::get("/hblog/add","doadd");
    Route::get("page","page/list");
})->asPrefix("hblog/");

// 分组后相当于
// Route::addRoute("blog/list","hblog/list");
// Route::get("blog/get/<id:\d+>","hblog/get")->asParams(["id"=>"\d+"]);
// Route::post("blog/add","hblog/doadd");
// Route::get("hblog/add","hblog/doadd");
// Route::get("blog/page","hblog/page/list");

```

### 带变量分组
```php
use hehe\core\hrouter\Route;
Route::addGroup("<module:\w+>/blog",function(){
    Route::addRoute("list","list");
    Route::get("get/<id>","get")->asParams(["id"=>"\d+"]);
    Route::post("add","doadd");
    Route::get("/hblog/add","/hblog/doadd");
    Route::get("page","page/list")->asSuffix("shtml");
})->asMethod("get")
    ->asPrefix("<module>/hblog/")
    ->asSuffix("html");

// 分组后相当于
// Route::addRoute("<module:\w+>/blog/list","<module>/hblog/list")->asSuffix("html");
// Route::get("<module:\w+>/blog/get/<id:\d+>","<module>/hblog/get")->asParams(["id"=>"\d+"])->asSuffix("html");
// Route::post("<module:\w+>/blog/add","<module>/hblog/doadd")->asSuffix("html");
// Route::get("hblog/add","hblog/doadd")->asSuffix("html");
// Route::get("<module:\w+>/blog/page","<module>/hblog/page/list")->asSuffix("shtml");

```

### 合并路由解析

- 说明
```
合并解析目的:提高匹配效率
合并原则:只合并相同请求类型的路由
可选参数:支持指定合并的条数
```

- 示例代码
```php
use hehe\core\hrouter\Route;

// 只能相同请求类型的路由规则合并

// get/<id>,geta/<id>,getb/<id> 合并成一条正则表达式进行验证,
Route::addGroup("blog",function(){
    Route::get("get/<id>","get");
    Route::get("geta/<id>","geta");
    Route::get("getb/<id>","getb");
})->asMethod("get")->asParams(["id"=>"\d+"])->asMergeRule();


// 指定路由规则每次合并数量,如合并数量为2时, (get/<id>,geta/<id>)一组合并,(getb/<id>)单独一组,
Route::addGroup("blog",function(){
    Route::get("get/<id>","get");
    Route::get("geta/<id>","geta");
    Route::get("getb/<id>","getb");
})->asMethod("get")->asParams(["id"=>"\d+"])->asMergeRule(2);

```

### 分组参数同步子路由

参数 | 方法 | 分组路由|子路由| 同步至子路由 | 说明
----------|------------|:-----:|:-------:|:-----:|------------
`suffix`  | asSuffix() |&check;| &check;|&check;|统一设置子路由后缀
`id`  | asId() |&check;| &check;|&check;|统一设置子路由的id前缀,如分组id:admin::,如子路由id:user,最终子路由id:admin::user
`params`  | asParams() |&check;| &check;|&check;|统一设置子路由变量,子路由变量与分组变量合并,并且子路由变量优先
`prefix`  | asPrefix("blog/") |&check;| &cross;|&check;|统一设置子路由action前缀(首字符为"/"的除外),分组prefix:blog/,子路由action:list,最终子路由action：blog/list
`mergeRule`  | asMergeRule(5) |&check;| &cross;|&cross;;|路由规则合并成一条正则表达式进行验证，可以指定一次合并N条


## 域名路由

- 常规域名路由
```php
use hehe\core\hrouter\Route;
Route::get("http://<language:[a-z]+>.xxx.com/user/get","user/get")
    ->asDefaults(["language"=>'ch']);

Route::get("user/get","user/get")
    ->asDefaults(["language"=>'ch'])->asDomain("http://<language:[a-z]+>.xxx.com");

```

- 分组域名路由
```php
 Route::addGroup("<_ssl:http|https>://www.xxx.cn",function(){
    Route::get("news/list","news/list");
    Route::get("news/get/<id:\d+>","news/get");
})->asDefaults(['ssl'=>'http']);

```

## URL参数解析

### 分隔符(split)格式
- 说明
```
基本格式:thread-119781-1.html
类属性如下:
pvar:URL地址参数解析名称,与"路由规则"中URL参数解析名称对应,比如uri:xxx/thread<hvar:(.*)>,pvar值为:hvar
names:参数项名称，默认值，正则表达式，以及顺序定义.
     格式:['status'=>["regex"=>"正则表达式","defval"=>"默认值"]]
     格式1:['id','status'=>"默认值",'type']
     格式2:['id'=>["regex"=>'\d+',"defval"=>"0"],'status'=>["regex"=>'\d+',"defval"=>"0"],'type']
flag:参数项之间的分隔符,默认是中划线-,比如"thread-122-1-1.html"地址中的"122-1-1"
prefix:参数前缀,默认是中划线-,比如"thread-122-1-1.html"地址中的122前面的中划线-
defval:全局默认值
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
```

- 动态参数模式
```php
use hehe\core\hrouter\Route;

// 动态参数类型,解析的参数格式如下:thread-119781-1-1.html
Route::get([
    'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
    'action'=>'<controller>/<action>',
    'prule'=>[
        'pvar'=>'param',
        'class'=>'split',// 参数解析器类路径
        'mode'=>'dynamic',// fixed:固定参数,dynamic:动态参数
        // 所有参数的默认值以及顺序,如thread-{id}-{status}-{type}.html
        'names'=>['id','status'=>"0",'type'],
    ]
]);

Route::get('<controller:\w+>/<action:\w+>/thread<param:.*>','<controller>/<action>')
         ->asParamsRule([
            'pvar'=>'param',
            'class'=>'split',
            'mode'=>'fixed',
            'names'=>['id','status'=>"0",'type'] 
        ]);

// URL地址:news/get/thread-119781-1-1.html,$action::news/get,$params:["id"=>119781,"status"=>1,"type"=>1]
// URL地址:news/get/thread-119781.html,$action::news/get,$params:["id"=>119781,"status"=>0]
// 生成URL:["id"=>122,"type"=>1],得到的URL:xxx/thread-122-0-1.html
// 生成URL:["id"=>122],得到的URL:xxx/thread-122-0.html

```

- 固定参数模式
```php
use hehe\core\hrouter\Route;

Route::get([
    'uri'=>'<controller:\w+>/<action:\w+>/thread<param:.*>',
    'action'=>'<controller>/<action>',
    'prule'=>[
        'pvar'=>'param',
        'class'=>'split',// 参数解析器类路径
        'mode'=>'fixed',// fixed:固定参数,dynamic:动态参数
        // 所有参数的默认值以及顺序,如thread-{id}-{status}-{type}.html
        'names'=>['id','status'=>"0",'type'],
    ]
]);

Route::get('<controller:\w+>/<action:\w+>/thread<param:.*>','<controller>/<action>')
        ->asParamsRule([
            'pvar'=>'param',
            'class'=>'split',
            'mode'=>'fixed',
            'names'=>['id','status'=>"0",'type'] 
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
属性如下:
valueSplit:参数名与值的分隔符,默认"/",如id/1
paramSplit:参数与参数的分隔符,默认"/",如id/1/status/1,id-1/status-1
prefix:参数前缀,默认""
names:参数项名称，默认值，正则表达式，以及顺序定义.
     格式:['status'=>["regex"=>"正则表达式","defval"=>"默认值"]]
     格式1:['id','status'=>"默认值",'type']
     格式2:['id'=>["regex"=>'\d+',"defval"=>"0"],'status'=>["regex"=>'\d+',"defval"=>"0"],'type']
defval:全局默认值
```
- 示例代码
```php
use hehe\core\hrouter\Route;

// 解析的参数格式如下:news/get/id/1/status/1
Route::get([
    'uri'=>'<controller:\w+>/<action:\w+>/<param:\w+>',
    'action'=>'<controller>/<action>',
    
    'prule'=>[
        'pvar'=>'param',
        'class'=>'pathinfo',// 参数解析器类路径
//        'valueSplit'=>'/',
//        'paramSplit'=>'/',
        // 所有参数的默认值以及顺序
        'names'=>['id','status','type'],
    ]
]);

// 或
Route::get('<controller:\w+>/<action:\w+>/thread<param:.*>','<controller>/<action>')
        ->asParamsRule([
          'pvar'=>'param',
//        'valueSplit'=>'/',
//        'paramSplit'=>'/',
           'names'=>['id','status','type']
         ]);

// URL地址:news/get/id/122/status/1/type/1.html,$action::news/get,$params:["id"=>122,"status"=>1,"type"=>1]
// URL地址:news/get/id/122/type/1.html,$action::news/get,$params:["id"=>122,"type"=>1]
// URL地址:news/get/id/122/status/1.html,$action::news/get,$params:["id"=>122,"status"=>1]

// 生成URL:["id"=>122,"status"=>1],得到的URL:xxx/id/122/status/1
// 生成URL:["id"=>122,"type"=>1,"status"=>1],得到的URL:xxx/id/122/status/1/type/1


```

## Url地址生成
### 常规生成URL
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

Route::get("news/add","news/doadd");
Route::get("news/<id:\d+>","news/get");
Route::get("news/search/conf<params:.*>","news/search")
->asOptions(["prule"=>["pvar"=>"param","class"=>'split','names'=>["catid"=>0,"status"=>0,] ]]);
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

### 由路由标识生成URL
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;
Route::get("news/<id:\d+>","news/get")->asId("news_id");

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news_id",["id"=>2]);
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
// $url:news/2.shtml

$url = $hrouter->buildUrL("news/get",["id"=>2],["suffix"=>"shtml"]);
// $url:news/2.shtml

$url = $hrouter->buildUrL("news/list");
// $url:news/list.html

$url = $hrouter->buildUrL("news/list",[],['suffix'=>"shtml"]);
// $url:news/list.shtml

```

### 生成带域名URL
```php
use hehe\core\hrouter\RouteManager;
use hehe\core\hrouter\Route;

Route::get("http://<language:[a-z]+>.xxx.com/user/get","news/get")
    ->asDefaults(["language"=>'ch']);

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news/get");
// $url:http://ch.xxx.com/user/get

```

### 生成带锚点URL
```php
use hehe\core\hrouter\RouteManager;

$hrouter = new RouteManager();
$url = $hrouter->buildUrL("news/get",["#"=>"add"],['suffix'=>"html"]);
// $url:news/get.html#add

```

## restful路由

- 常规格式
```php
use hehe\core\hrouter\Route;
// 指定地址格式
Route::get("blog","blog/index");
Route::get("blog/create","blog/create");
Route::post("blog","blog/save");
Route::get("blog/<id:\d+>","blog/read");
Route::get("blog/<id:\d+>/edit","blog/edit");
Route::put("blog/<id:\d+>","blog/update");
Route::delete("blog/<id:\d+>","blog/delete");

```

- 变量格式
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

```
- 注解常规格式
```php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Restful;
/**
 * @Restful("good")
 */
class GoodController
{
    public function indexAction(){}
    public function createAction(){}
    public function saveAction(){}
    public function readAction(){}
    public function editAction(){}
    public function updateAction(){}
    public function deleteAction(){}
}

```

- 注解变量格式
```php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Restful;
/**
 * @Restful("<module:\w+>/order")
 */
class OrderController
{
    public function indexAction(){}
    public function createAction(){}
    public function saveAction(){}
    public function readAction(){}
    public function editAction(){}
    public function updateAction(){}
    public function deleteAction(){}
}

```

## 注解路由
- 说明
```
注解器:hehe\core\hrouter\annotation\Route
注解类:相当于创建一个分组路由，注解类方法相当于在分组路由注册子路由
```
- 注解类
```php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;
/**
 * @Route("admin")
 * 相当于:Route::addRoute("admin<route:.*?>","admin<route>");
 */
class AdminController
{
    // 访问此方法:"admin/save"
    public function saveAction(){}
}
```

- 注解类方法
```php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;

#[Route("admin")]
class AdminController
{

    /**
     * @Route("doadd")
     * 相当于:Route::addRoute("admin/doadd","admin/add");
     */
    public function addAction(){}

    /**
     * 相当于:Route::addRoute("admin/<id:\d+>","admin/get")->asSuffix("html"); 
     */
     #[Route("/admin/<id:\d+>",suffix:"html")]
    public function getAction(){}
    
    /**
     * 
     * 相当于:Route::addRoute("/admin/dosave","admin/save");  
     */
     #[Route("/admin/save")]
    public function saveAction(){}

}
```


## 扩展路由






    










