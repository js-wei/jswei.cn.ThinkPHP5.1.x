<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    p(PHP_VERSION);
});

Route::get('hello/:name', 'index/hello');

// 给publish控制器设置快捷路由
Route::rule('user/register', 'index/index/register');
Route::rule('user/get_location', 'index/index/get_location');
Route::rule('user/signup', 'index/index/signup');
Route::rule('user/message', 'index/publish/send_message');
Route::rule('user/login', 'index/publish/login');
Route::rule('user/logout', 'index/publish/logout');
Route::rule('user/forget', 'index/publish/forget');

//给Api控制器设置快捷路由
Route::rule('api/config', 'index/api/get_site');
Route::rule('api/login', 'index/api/login');
Route::rule('api/register', 'index/api/register');
Route::rule('api/set_password', 'index/api/set_password');
Route::rule('api/send_email_code', 'index/api/send_email_code');
Route::rule('api/send_message', 'index/api/send_message');
Route::rule('api/check_code', 'index/api/check_code');
Route::rule('api/personal', 'index/api/personal_info');
Route::rule('api/check_nickname', 'index/api/check_nickname');
Route::rule('api/get_ip_location', 'index/api/get_ip_location');
Route::rule('api/get_province', 'index/api/get_province');
Route::rule('api/get_city', 'index/api/get_city');
Route::rule('api/get_areas', 'index/api/get_areas');
Route::rule('api/upgrade_nickname', 'index/api/upgrade_nickname');
Route::rule('api/upgrade_sex', 'index/api/upgrade_sex');
Route::rule('api/upgrade_head', 'index/api/upgrade_head');
Route::rule('api/upgrade_phone', 'index/api/upgrade_phone');
Route::rule('api/upgrade_email', 'index/api/upgrade_email');
Route::rule('api/set_hobbise', 'index/api/set_hobbise');
Route::rule('api/push_app', 'index/api/push_app');
Route::rule('api/push_recive', 'index/api/push_recive');
Route::rule('api/set_information', 'index/api/set_information');
