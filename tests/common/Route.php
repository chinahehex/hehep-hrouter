<?php
use hehe\core\hrouter\Route;

Route::get("news/list", "news/list");
Route::get("news/get/<id:\d+>", "news/get");
Route::get("news/add", "news/doadd");
Route::get("news/<action:\w+>", "news/<action>");

Route::get("user/list", "user/list");
Route::get("user/get/<id:\d+>", "user/get");
Route::get("user/add", "user/doadd");
Route::get("user/<action:\w+>", "user/<action>");

Route::get("role/list", "role/list");
Route::get("role/get/<id:\d+>", "role/get");
Route::get("role/add", "role/doadd");
Route::get("role/<action:\w+>", "role/<action>");

Route::get("admin/list", "admin/list");
Route::get("admin/get/<id:\d+>", "admin/get");
Route::get("admin/add", "admin/doadd");
Route::get("admin/<action:\w+>", "admin/<action>");

Route::addGroup('blog', function () {
    Route::get("list", "blog/list");
    Route::get("get/<id:\d+>", "blog/get");
    Route::get("<action:\w+>", "blog/<action>");
});

