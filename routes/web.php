<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::dashboard')->name('dashboard');
Route::livewire('/live', 'pages::demo')->name('live');
Route::livewire('/editor', 'pages::editor')->name('editor');
