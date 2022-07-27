<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarController;

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
    return view('welcome');
});

Route::get('/calendar', [CalendarController::class, 'calendar']);
Route::get('/calendar-update', [CalendarController::class, 'calendar_update'])->name('calendar.update');

Route::post('/events-add', [CalendarController::class, 'add_event'])->name('events.add');

Route::get('/google-auth', [CalendarController::class, 'google_auth'])->name('google.auth');
Route::get('/google-callback', [CalendarController::class, 'google_callback'])->name('google.callback');
Route::post('/google-logout', [CalendarController::class, 'google_logout'])->name('google.logout');
