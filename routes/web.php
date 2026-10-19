<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'todayCount' => Appointment::whereDate('starts_at', today())->whereIn('status', AppointmentStatus::occupying())->count(),
        'professionalsCount' => Professional::where('active', true)->count(),
        'servicesCount' => Service::where('active', true)->count(),
        'clientsCount' => Client::count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

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
