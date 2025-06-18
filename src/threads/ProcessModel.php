<?php
namespace hehe\core\hroute\threads;


/**
 * 进程模型
 * 以当前进程id为标识
 * @property \hehe\core\hroute\base\RouteRequest $routeRequest
 */
class ProcessModel
{
    protected $_attributes = [];

    protected function getIdent()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 0;
        } else {
            return posix_getpid();
        }
    }

    public function __get($name)
    {
        if (!isset($this->_attributes[$name])) {
            return null;
        }

        $ident = $this->getIdent();

        if (!isset($this->_attributes[$name][$ident])) {
            return null;
        }

        return $this->_attributes[$name][$ident];
    }

    public function __set($name, $value)
    {
        $ident = $this->getIdent();
        if (is_null($value)) {
            unset($this->_attributes[$name][$ident]);
        } else {
            $this->_attributes[$name][$ident] = $value;
        }
    }
}
