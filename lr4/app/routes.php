<?php

/*
|--------------------------------------------------------------------------
| アプリケーションルート
|--------------------------------------------------------------------------
|
| このファイルでアプリケーションの全ルートを定義します。
| 方法は簡単です。対応するURIをLaravelに指定してください。
| そしてそのURIに対応する実行コードをクロージャーで指定します。
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::controller('posts', 'MessagesController');

Route::model('site', 'Site');
Route::any('site/{site}', function($site) {
	return Redirect::to('site/'.$site->id.'/home');
});
Route::controller('site/{site}/{page}', 'SitePageController');
Route::controller('siteg/{site}', 'SiteController');
