<?php

use Illuminate\Support\Facades\Route;
/**/
Route::view('/driftsstatus', 'status.index')->name('status.index');

Route::get('/', function () {
    return view('welcome');
});

