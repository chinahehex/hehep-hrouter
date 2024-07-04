<?php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;

/**
 * Class UserController
 * @package hrouter\tests\common
 * @Route()
 */
class LogController
{

    /**
     * @Route("doadd")
     */
    public function addAction()
    {

    }

    /**
     * @Route("/log/<id:\d+>")
     */
    public function getAction()
    {

    }

    public function saveAction()
    {

    }

}
