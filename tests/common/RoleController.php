<?php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;

/**
 * Class UserController
 * @package hrouter\tests\common
 * @Route("role")
 */
class RoleController
{

    /**
     * @Route("doadd")
     */
    public function addAction()
    {

    }

    /**
     * @Route("/role/{id:\d+}")
     */
    public function getAction()
    {

    }

    public function saveAction()
    {

    }

}
