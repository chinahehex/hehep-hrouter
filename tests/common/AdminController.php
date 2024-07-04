<?php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;

/**
 * Class UserController
 * @package hrouter\tests\common
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
