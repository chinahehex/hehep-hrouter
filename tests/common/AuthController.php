<?php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Route;

/**
 * Class UserController
 * @package hrouter\tests\common
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
