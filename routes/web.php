<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'contactsCount' => \App\Models\Contact::count(),
        'openDealsCount' => \App\Models\Deal::where('status', 'open')->count(),
        'openLeadsCount' => \App\Models\Lead::whereNotIn('status', ['converted', 'unqualified'])->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/export', [ProfileController::class, 'export'])->name('profile.export');

    Route::resource('contacts', ContactController::class);

    Route::resource('leads', LeadController::class)->except(['show']);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

    Route::resource('deals', DealController::class)->except(['show']);
    Route::patch('/deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');

    Route::patch('/settings/theme', [ThemeController::class, 'update'])->name('theme.update');
});

require __DIR__.'/auth.php';
