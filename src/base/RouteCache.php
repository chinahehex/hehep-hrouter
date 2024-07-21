<?php
namespace hehe\core\hrouter\base;

/**
 * 路由缓存类
 */
class RouteCache
{

    /**
     * 路由文件
     * @var array
     */
    protected $routeFile = [];

    /**
     * 缓存目录
     * @var string
     */
    protected $cacheDir = '';

    /**
     * 缓存文件
     * @var string
     */
    protected $cacheFile = '';

    /**
     * 缓存失效时间
     * @var int
     */
    protected $timeout = 0;

    /**
     * @var Router
     */
    protected $router;

    /**
     * 缓存有效验证结果
     * @var bool
     */
    protected $_checkResult;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

        if (is_string($this->routeFile)) {
            $this->routeFile = explode(',',$this->routeFile);
        }

        if ($this->cacheFile === '') {
            $this->cacheFile = $this->buildCacheFile();
        }

    }

    public function asOptions(array $options):self
    {
        foreach ($options as $name=>$value) {
            $this->{$name} = $value;
        }

        return $this;
    }

    public function requireRouteFile():void
    {
        foreach ($this->routeFile as $file) {
            require_once $file;
        }
    }

    public function addRouteFile(string ...$files):self
    {
        $this->routeFile = array_merge($this->routeFile,$files);

        return $this;
    }

    /**
     * 检测缓存是否有效
     * @return bool
     */
    public function checkCacheStatus():bool
    {
        if (!is_null($this->_checkResult) && $this->_checkResult == true) {
            return $this->_checkResult;
        }

        $checkResult = true;

        if (!is_file($this->cacheFile)) {
            $checkResult = false;
        }

        if ($checkResult === true) {
            foreach ($this->routeFile as $file) {
                if (!is_file($file)) {
                    $checkResult = false;
                    break;
                }

                if (filemtime($file) > filemtime($this->cacheFile)) {
                    $checkResult = false;
                    break;
                } else if ($this->timeout != 0 && time() > filemtime($this->cacheFile) + $this->timeout) {
                    // 缓存是否在有效期内
                    $checkResult = false;
                    break;
                }
            }
        }

        $this->_checkResult = $checkResult;

        // 缓存有效
        return $checkResult;
    }

    protected function buildCacheFile()
    {
        return $this->cacheDir  . '/route_cache0000.php';
    }

    /**
     * 写入路由缓存
     * @return Rule[]|array
     */
    public function writeRules()
    {
        $routesCache = $this->router->getCollector()->buildCache($this->router);
        file_put_contents($this->cacheFile, "<?php\n\nreturn " . var_export($routesCache, true) . ";\n");
    }

    /**
     * 注入路由规则
     */
    public function injectRules()
    {
        $caches = require($this->cacheFile);
        $this->router->getCollector()->restoreCache($this->router,$caches);
    }



}
