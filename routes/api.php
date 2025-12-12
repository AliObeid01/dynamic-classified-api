<?php

use App\Services\CategoryService;
use Database\Seeders\CategoriesSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
   Route::get('catgeories', [CategoriesSeeder::class, 'run']);
});
