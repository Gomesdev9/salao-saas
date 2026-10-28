<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommissionAdvanceController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PaymentController;
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
    Route::patch('/profile/salao', [ProfileController::class, 'updateSalon'])->name('profile.salon');

    // Agenda
    Route::resource('agenda', AppointmentController::class)
        ->except('show')
        ->parameters(['agenda' => 'appointment'])
        ->names('appointments');
    Route::patch('agenda/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::get('agenda/{appointment}/pagamento', [PaymentController::class, 'create'])->name('appointments.payment.create');
    Route::post('agenda/{appointment}/pagamento', [PaymentController::class, 'store'])->name('appointments.payment.store');
    Route::delete('agenda/{appointment}/pagamento', [PaymentController::class, 'destroy'])->name('appointments.payment.destroy');

    // Financeiro (todas as telas aceitam ?mes=2026-10)
    Route::prefix('financeiro')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('lancamentos', [FinanceController::class, 'entries'])->name('entries');
        Route::post('despesas', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::patch('despesas/{expense}/pagar', [ExpenseController::class, 'pay'])->name('expenses.pay');
        Route::delete('despesas/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::get('comissoes', [CommissionController::class, 'index'])->name('commissions');
        Route::post('comissoes/{professional}/pagar', [CommissionController::class, 'pay'])->name('commissions.pay');
        Route::delete('comissoes/{professional}/pagar', [CommissionController::class, 'unpay'])->name('commissions.unpay');
        Route::post('vales', [CommissionAdvanceController::class, 'store'])->name('advances.store');
        Route::delete('vales/{advance}', [CommissionAdvanceController::class, 'destroy'])->name('advances.destroy');
    });

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
