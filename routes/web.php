<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {




    $result = DB::table('film')
        ->select('film_id', 'title')
        ->where('title', 'LIKE', 'K%')
        ->orWhere('title', 'LIKE', 'Q%')
        ->whereIn('language_id', function ($query) {
            $query->select('language_id')->from('language')
                ->where('name', 'English');
        })->orderBy('title')->get();

    return $result;
});
