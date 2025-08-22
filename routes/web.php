<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AccountCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectTypeController;
use App\Http\Controllers\AccountCodeTypeController;
use App\Http\Controllers\ProjectStepController;
use App\Http\Controllers\IncomeController;

Route::get('/', fn() => redirect()->route('dashboard'));
Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware(['auth'])->name('dashboard');
Route::middleware(['auth'])->group(function () {
    Route::resource('project-types', ProjectTypeController::class)->except(['show']);
    Route::resource('projects', ProjectController::class);

    Route::post('projects/{project}/steps', [ProjectStepController::class,'store'])->name('projects.steps.store');
    Route::put('steps/{step}', [ProjectStepController::class,'update'])->name('steps.update');
    Route::delete('steps/{step}', [ProjectStepController::class,'destroy'])->name('steps.destroy');

    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('projects/{project}/expenses', [ExpenseController::class, 'store'])->name('projects.expenses.store');
    Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::resource('incomes', IncomeController::class)->only(['update','destroy']);

    Route::resource('account-code-types', AccountCodeTypeController::class)->except(['show']);
    Route::resource('account-codes', AccountCodeController::class);
    
    Route::get('reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    Route::get('reports/cashbook', [ReportController::class, 'cashbook'])->name('reports.cashbook');
    Route::get('reports/notes', [ReportController::class, 'notes'])->name('reports.notes');
    Route::get('reports/pnl', [ReportController::class, 'pnl'])->name('reports.pnl');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';