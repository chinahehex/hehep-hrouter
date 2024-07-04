<?php
namespace hehe\core\hrouter\annotation;

use hehe\core\hcontainer\ann\base\AnnotationProcessor;
use hehe\core\hrouter\base\GroupRule;
use hehe\core\hrouter\base\Rule;
use hehe\core\hrouter\easy\EasyRule;
use hehe\core\hrouter\Route;
use hehe\core\hrouter\RouteManager;

/**
 * 路由注解处理器
 */
class RouteProcessor extends AnnotationProcessor
{
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
        $routeValues = $this->getAttribute($annotation,false);

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
        $routeValues = $this->getAttribute($annotation,false);
        if (empty($routeValues['action'])) {
            $routeValues['action'] = $this->buildActionName($method);
        }

        $rule = Route::createRule($routeValues);

        if (isset($this->classGroupRules[$class])) {
            $groupRule = $this->classGroupRules[$class];
            $groupRule->addRule($rule);
        } else {
            $this->routeRules[] = $rule;
        }
    }

    public function handleProcessorFinish()
    {

        Route::addRouteRules($this->routeRules);
        $this->routeRules = [];
        $this->classGroupRules = [];
    }

}
