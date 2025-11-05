<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register-form', function () {
    return view('register');
});

Route::get('/login-form', function () {
    return view('login');
});
