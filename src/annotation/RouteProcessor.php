<?php
namespace hehe\core\hrouter\annotation;

use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\Route;
use hehe\core\hrouter\RouteManager;

/**
 * 路由注解处理器
 */
class RouteProcessor extends AnnotationProcessor
{
    protected $annotationHandlers = [
        'Restful'=>'handleRestfulAnnotation'
    ];

    /**
     * 路由规则定义列表
     * @var Rule[]
     */
    protected $routeRules = [];

    /**
     * 分组路由规则
     * @var GroupRule[]
     */
    protected $classGroupRules = [];

    protected function buildUriName(string $class,string $suffix = 'Controller')
    {
        $name = lcfirst(basename(str_replace('\\','/',$class)));
        if (substr_compare($name, $suffix, -strlen($suffix)) === 0) {
            $name = str_replace($suffix,'',$name);
        } else {
            $name = $class;
        }

        return $name;
    }

    protected function buildActionName(string $method,string $suffix = 'Action')
    {
        if (substr_compare($method, $suffix, -strlen($suffix)) === 0) {
            $name = str_replace($suffix,'',$method);
        } else {
            $name = $method;
        }

        return $name;
    }

    public function handleAnnotationClass($annotation,string $class):void
    {
        $routeValues = $this->getProperty($annotation,false);

        $uri = !empty($routeValues['uri']) ? $routeValues['uri'] : '';
        if (empty($uri)) {
            $uri = $this->buildUriName($class);
        }

        $routeValues['uri'] = $uri;
        $groupRule = Route::createGroup($routeValues);
        $groupRule->asPrefix($uri . '/');
        $this->routeRules[] = $groupRule;
        $this->classGroupRules[$class] = $groupRule;
    }

    /**
     * 处理方法注解
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param object $annotation
     * @param string $class
     * @param string $method
     */
    public function handleAnnotationMethod($annotation,string $class,string $method):void
    {
        $routeValues = $this->getProperty($annotation,false);
        if (empty($routeValues['action'])) {
            $routeValues['action'] = $this->buildActionName($method);
        }

        if (!isset($routeValues['method'])) {
            $routeValues['method'] = Route::ANY_METHOD;
        }

        $rule = Route::createRule($routeValues);

        if (isset($this->classGroupRules[$class])) {
            $groupRule = $this->classGroupRules[$class];
            $groupRule->addSubRule($rule);
        } else {
            $this->routeRules[] = $rule;
        }
    }

    public function handleRestfulAnnotation($annotation,string $class,string $target,string $type)
    {
        $routeValues = $this->getProperty($annotation,false);
        $uri = !empty($routeValues['uri']) ? $routeValues['uri'] : '';
        if (empty($uri)) {
            $uri = $this->buildUriName($class);
        }

        $groupRule = Route::createGroup($routeValues);
        $groupRule->asPrefix($uri . '/');
        $groupRule->addSubRule(Route::createRule("","index",'get'));
        $groupRule->addSubRule(Route::createRule("create","create",'get'));
        $groupRule->addSubRule(Route::createRule("","save",'post'));
        $groupRule->addSubRule(Route::createRule("<id:\d+>","read",'get'));
        $groupRule->addSubRule(Route::createRule("<id:\d+>/edit","edit",'get'));
        $groupRule->addSubRule(Route::createRule("<id:\d+>","update",'put'));
        $groupRule->addSubRule(Route::createRule("<id:\d+>","delete",'delete'));
        $this->routeRules[] = $groupRule;
    }

    public function handleProcessorFinish()
    {

        Route::addRules($this->routeRules);
        $this->routeRules = [];
        $this->classGroupRules = [];
    }

}
