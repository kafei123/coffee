<?php
// +----------------------------------------------------------------------
// | 路由文件
// +----------------------------------------------------------------------
// | CoffeePHP
// +----------------------------------------------------------------------
// | Author: 咖啡屋少年 <a710292863@qq.com>
// +----------------------------------------------------------------------

namespace coffee;

use coffee\Route;

Route::get('get', 'index/get');
Route::get('geta/:id', 'index/geta');
Route::post('post', 'index/post');
Route::any('any', 'index/any');
Route::put('put', 'index/put');
Route::delete('delete', 'index/delete');
Route::patch('patch', 'index/patch');

return [];
