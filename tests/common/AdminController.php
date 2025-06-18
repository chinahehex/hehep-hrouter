<?php
namespace hroute\tests\common;
use hehe\core\hroute\annotation\Route;

/**
 * Class UserController
 * @package hroute\tests\common
 * @Route("admin")
 */
class AdminController
{

    /**
     * @Route("doadd")
     */
    public function addAction()
    {

    }

    /**
     * @Route("/admin/<id:\d+>")
     */
    public function getAction()
    {

    }

    public function saveAction()
    {

    }

}
