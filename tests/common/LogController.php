<?php
namespace hroute\tests\common;
use hehe\core\hroute\annotation\Route;

/**
 * Class UserController
 * @package hroute\tests\common
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
