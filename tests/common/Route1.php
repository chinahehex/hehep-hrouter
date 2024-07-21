<?php
use hehe\core\hrouter\Route;

Route::get("newsx/list", "newsx/list");
Route::get("newsx/get/<id:\d+>", "newsx/get");
Route::get("newsx/add", "newsx/doadd");
Route::get("newsx/<action:\w+>", "newsx/<action>");

Route::get("userx/list", "userx/list");
Route::get("userx/get/<id:\d+>", "userx/get");
Route::get("userx/add", "userx/doadd");
Route::get("userx/<action:\w+>", "userx/<action>");

Route::get("rolex/list", "rolex/list");
Route::get("rolex/get/<id:\d+>", "rolex/get");
Route::get("rolex/add", "rolex/doadd");
Route::get("rolex/<action:\w+>", "rolex/<action>");

Route::get("adminx/list", "adminx/list");
Route::get("adminx/get/<id:\d+>", "adminx/get");
Route::get("adminx/add", "adminx/doadd");
Route::get("adminx/<action:\w+>", "adminx/<action>");

Route::addGroup('blogx', function () {
    Route::get("list", "blogx/list");
    Route::get("get/<id:\d+>", "blogx/get");
    Route::get("<action:\w+>", "blogx/<action>");
});

