<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\CustomFieldDefinitionController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PersonalNoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RetainerController;
use App\Http\Controllers\SavedFilterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ThemeController;
use App\Services\InsightsEngine;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'contactsCount' => \App\Models\Contact::count(),
        'openDealsCount' => \App\Models\Deal::where('status', 'open')->count(),
        'openLeadsCount' => \App\Models\Lead::whereNotIn('status', ['converted', 'unqualified'])->count(),
        'activeProjectsCount' => \App\Models\Project::whereIn('status', ['active', 'on_hold'])->count(),
        'activeRetainersCount' => \App\Models\Retainer::where('status', 'active')->count(),
        'insights' => InsightsEngine::generate(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/export', [ProfileController::class, 'export'])->name('profile.export');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // A /contacts/import útvonalaknak a resource ELŐTT kell szerepelniük, különben a
    // Route::resource "contacts/{contact}" mintája fogná el "import"-ot mint kontakt-ID-t.
    Route::get('/contacts/import', [ContactImportController::class, 'create'])->name('contacts.import.create');
    Route::get('/contacts/import/template', [ContactImportController::class, 'template'])->name('contacts.import.template');
    Route::post('/contacts/import/preview', [ContactImportController::class, 'preview'])->name('contacts.import.preview');
    Route::post('/contacts/import', [ContactImportController::class, 'import'])->name('contacts.import.store');

    Route::resource('contacts', ContactController::class);

    Route::resource('organizations', OrganizationController::class)->except(['create', 'store']);

    Route::resource('campaigns', CampaignController::class);

    Route::resource('custom-field-definitions', CustomFieldDefinitionController::class)->except(['show']);

    Route::resource('leads', LeadController::class)->except(['show']);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

    Route::resource('deals', DealController::class)->except(['show']);
    Route::patch('/deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');

    Route::resource('projects', ProjectController::class)->except(['create', 'store']);

    Route::resource('retainers', RetainerController::class)->except(['create', 'store']);

    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

    Route::post('/saved-filters', [SavedFilterController::class, 'store'])->name('saved-filters.store');
    Route::delete('/saved-filters/{savedFilter}', [SavedFilterController::class, 'destroy'])->name('saved-filters.destroy');

    Route::get('/sajat-jegyzetek', [PersonalNoteController::class, 'index'])->name('personal-notes.index');
    Route::post('/sajat-jegyzetek', [PersonalNoteController::class, 'store'])->name('personal-notes.store');
    Route::patch('/sajat-jegyzetek/{note}', [PersonalNoteController::class, 'update'])->name('personal-notes.update');
    Route::delete('/sajat-jegyzetek/{note}', [PersonalNoteController::class, 'destroy'])->name('personal-notes.destroy');

    Route::patch('/settings/theme', [ThemeController::class, 'update'])->name('theme.update');
});

require __DIR__.'/auth.php';
