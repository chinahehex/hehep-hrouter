<?php
namespace hrouter\tests\common;
use hehe\core\hrouter\annotation\Restful;

/**
 * @Restful("<module:\w+>/order")
 */
class OrderController
{

    public function indexAction(){}
    public function createAction(){}
    public function saveAction(){}
    public function readAction(){}
    public function editAction(){}
    public function updateAction(){}
    public function deleteAction(){}

}
