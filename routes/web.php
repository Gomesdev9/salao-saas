<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// Página de divulgação do sistema, com o convite para a empresa se cadastrar
Route::view('/', 'site.home')->name('home');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Agenda
    Route::resource('agenda', AppointmentController::class)
        ->except('show')
        ->parameters(['agenda' => 'appointment'])
        ->names('appointments');
    Route::patch('agenda/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');

    // Cadastros do salão (URLs em português: /profissionais, /servicos, /clientes)
    Route::resource('profissionais', ProfessionalController::class)
        ->except('show')
        ->parameters(['profissionais' => 'professional'])
        ->names('professionals');
    Route::resource('servicos', ServiceController::class)
        ->except('show')
        ->parameters(['servicos' => 'service'])
        ->names('services');
    Route::resource('clientes', ClientController::class)
        ->except('show')
        ->parameters(['clientes' => 'client'])
        ->names('clients');
});

require __DIR__.'/auth.php';
