<?php
use hehe\core\hrouter\Route;

Route::get("newsa/list", "newsa/list");
Route::get("newsa/get/<id:\d+>", "newsa/get");
Route::get("newsa/add", "newsa/doadd");
Route::get("newsa/<action:\w+>", "newsa/<action>");

Route::get("usera/list", "usera/list");
Route::get("usera/get/<id:\d+>", "usera/get");
Route::get("usera/add", "usera/doadd");
Route::get("usera/<action:\w+>", "usera/<action>");

Route::get("rolea/list", "rolea/list");
Route::get("rolea/get/<id:\d+>", "rolea/get");
Route::get("rolea/add", "rolea/doadd");
Route::get("rolea/<action:\w+>", "rolea/<action>");

Route::get("admina/list", "admina/list");
Route::get("admina/get/<id:\d+>", "admina/get");
Route::get("admina/add", "admina/doadd");
Route::get("admina/<action:\w+>", "admina/<action>");

Route::addGroup('bloga', function () {
    Route::get("list", "bloga/list");
    Route::get("get/<id:\d+>", "bloga/get");
    Route::get("<action:\w+>", "bloga/<action>");
});


