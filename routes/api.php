<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('{id}/company', [CompanyController::class, 'getManagerTree']);
Route::get('/allcompanies',[CompanyController::class,'getAllCompanies']);


Route::get('/company/{companyId}/employee/{employeeId}', [CompanyController::class, 'getEmployeeTree']);