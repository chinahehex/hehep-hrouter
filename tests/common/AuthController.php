<?php
namespace hroute\tests\common;
use hehe\core\hroute\annotation\Route;

/**
 * Class UserController
 * @package hroute\tests\common
 *
 */
#[Route("auth")]
class AuthController
{

    #[Route("doadd")]
    public function addAction()
    {

    }


    #[Route("/auth/<id:\d+>")]
    public function getAction()
    {

    }

    public function saveAction()
    {

    }

}
