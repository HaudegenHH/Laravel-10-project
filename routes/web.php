<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Profile\AvatarController;


use OpenAI\Laravel\Facades\OpenAI;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::patch('/profile/avatar', [AvatarController::class, 'update'])->name('profile.avatar');
    Route::post('/profile/avatar/ai', [AvatarController::class, 'generate'])->name('profile.avatar.ai');
});

require __DIR__.'/auth.php';

// just to test the openai, i create a route and a closure
Route::get('/openai', function() {

    // $result = OpenAI::completions()->create([
    //     'model' => 'text-davinci-003',
    //     'prompt' => 'PHP is'
    // ]);
    // echo $result['choices'][0]['text'];

    $result = OpenAI::images()->create([
        "prompt" =>"create avatar for user with name " . auth()->user()->name,
        "n"      => 1,
        "size"   => "256x256",
    ]);
      
    // dd($result->data[0]->url);
    return response(['url' => $result->data[0]->url]);
});