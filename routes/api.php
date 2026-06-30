<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\SavingCategoryController;
use App\Http\Controllers\PurposeController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::get('categories/expense', [CategoryController::class, 'expenseCategory']);
Route::get('categories/income', [CategoryController::class, 'incomeCategory']);
Route::get('categories/saving', [CategoryController::class, 'savingCategory']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);

Route::get('purposes', [PurposeController::class, 'index']);
Route::get('purposes/{purpose}', [PurposeController::class, 'show']);

Route::middleware('token.auth')->group(function () {

    Route::post('purposes', [PurposeController::class, 'store']);
    Route::get('active-goals', [PurposeController::class, 'activeGoal']);
    Route::put('purposes/{purpose}', [PurposeController::class, 'update']);
    Route::delete('purposes/{purpose}', [PurposeController::class, 'destroy']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);

    Route::apiResource('savings', SavingController::class);

    Route::apiResource('saving-categories', SavingCategoryController::class);

    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    Route::apiResource('incomes', IncomeController::class);

    Route::apiResource('expenses', ExpenseController::class);

    Route::get('reports/daily', [ReportController::class, 'daily']);
    Route::get('reports/weekly', [ReportController::class, 'weekly']);
    Route::get('reports/monthly', [ReportController::class, 'monthly']);

});

Route::post('migrate', function () {
    if (env('APP_ENV') !== 'production') {
        return response()->json(['message' => 'Migrations only available in production'], 403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        return response()->json(['message' => 'Migrations completed', 'output' => \Illuminate\Support\Facades\Artisan::output()], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Migration failed', 'error' => $e->getMessage()], 500);
    }
});
