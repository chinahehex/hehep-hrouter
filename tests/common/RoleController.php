<?php
namespace hroute\tests\common;
use hehe\core\hroute\annotation\Route;

/**
 * Class UserController
 * @package hroute\tests\common
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
