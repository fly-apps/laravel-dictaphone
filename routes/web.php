<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Recorder;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/info', function () {
    dd(phpinfo());
});
