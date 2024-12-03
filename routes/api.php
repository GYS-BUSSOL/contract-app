<?php

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\{Route, RateLimiter};
use App\Http\Controllers\Api\{
  ManDaysRateController,
  Approval2Controller,
  Approval1Controller,
  BUController,
  ReviewerController,
  RangeController,
  BudgetBUController,
  MerVendorController,
  VendorAssigmentController,
  PPSController,
  RenewalController,
  PBLController,
  CompanyController,
  HumanResourcesController,
  MerCCBUWCController,
  MerContractStatusController,
  MerLaborTypeController,
  SPKController,
  BusinessUnitController,
  CostCenterController,
  WorkCenterController,
  JobTypeController,
  MeasurementUnitController,
  SignatureTypeController,
  PaymentTypeController
};

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');


RateLimiter::for('api', function (Request $request) {
  return Limit::perMinute(60)->by($request->ip());
});

Route::group(['prefix' => 'apps'], function () {
  // Range Years
  Route::controller(RangeController::class)->group(function () {
    Route::post('/years-range/list', 'listYear');
  });
  // Mer CC,BU,WC
  Route::controller(MerCCBUWCController::class)->group(function () {
    Route::get('/mer-bu/list', 'listBU');
    Route::get('/mer-cc/list', 'listCC');
    Route::get('/mer-wc/list', 'listWC');
  });
  // Payment Type
  Route::controller(PaymentTypeController::class)->group(function () {
    Route::get('/payment-type/list', 'list');
  });
  // Measurement Unit
  Route::controller(MeasurementUnitController::class)->group(function () {
    Route::get('/measurement-unit/list', 'list');
  });
  // BU
  Route::controller(BUController::class)->group(function () {
    Route::get('/bu/list', 'list');
  });
  // Mer contract status
  Route::controller(MerContractStatusController::class)->group(function () {
    Route::get('/mer-contract-status/list', 'list');
  });
  // Vendor Assigment
  Route::controller(VendorAssigmentController::class)->group(function () {
    Route::post('/vendor-assigment/search', 'search');
  });
  // PPS
  Route::controller(PPSController::class)->group(function () {
    Route::post('/pps-ongoing/search', 'searchOngoing');
    Route::post('/pps-completed/search', 'searchCompleted');
    Route::post('/pps-ongoing/add', 'add');
    Route::get('/pps-ongoing/edit/{id}', 'edit');
    Route::put('/pps-ongoing/update/{id}', 'update');
    Route::delete('/pps-ongoing/delete/{id}', 'destroy');
  });
  // Renewal
  Route::controller(RenewalController::class)->group(function () {
    Route::post('/renewal/search', 'search');
  });
  // PBL
  Route::controller(PBLController::class)->group(function () {
    Route::post('/pbl/search', 'search');
  });
  // Company
  Route::controller(CompanyController::class)->group(function () {
    Route::get('/company/list', 'list');
  });
  // Vendor
  Route::controller(MerVendorController::class)->group(function () {
    Route::get('/mer-vendor/list', 'list');
  });
  // Reviewer
  Route::controller(ReviewerController::class)->group(function () {
    Route::post('/reviewer/search', 'search');
  });
  // Budget BU
  Route::controller(BudgetBUController::class)->group(function () {
    Route::post('/budget-bu/search', 'search');
  });
  // SPK
  Route::controller(SPKController::class)->group(function () {
    Route::post('/spk-list/search', 'search');
    Route::post('/spk-report/search', 'searchReport');
    Route::post('/spk-active/search', 'searchActive');
  });
  // Approval
  Route::controller(Approval1Controller::class)->group(function () {
    Route::post('/approval-lvl1-ongoing/search', 'searchOngoing');
    Route::post('/approval-lvl1-completed/search', 'searchCompleted');
  });
  Route::controller(Approval2Controller::class)->group(function () {
    Route::post('/approval-lvl2-ongoing/search', 'searchOngoing');
    Route::post('/approval-lvl2-completed/search', 'searchCompleted');
  });
});

Route::group(['prefix' => 'configurations'], function () {
  // Man Days Rate
  Route::controller(ManDaysRateController::class)->group(function () {
    Route::post('/man-days-rate/search', 'search');
    Route::post('/man-days-rate/add', 'add');
    Route::get('/man-days-rate/edit/{id}', 'edit');
    Route::put('/man-days-rate/update/{id}', 'update');
    Route::delete('/man-days-rate/delete/{id}', 'destroy');
  });
  // Human Resources (User Management)
  Route::controller(HumanResourcesController::class)->group(function () {
    Route::post('/human-resources/search', 'search');
    Route::get('/human-resources/list', 'list');
    Route::get('/human-resources/list/{access}', 'listWithRole');
    Route::post('/human-resources/add', 'add');
    Route::get('/human-resources/edit/{id}', 'edit');
    Route::put('/human-resources/update/{id}', 'update');
    Route::delete('/human-resources/delete/{id}', 'destroy');
  });
  // Area Management (Business Unit)
  Route::controller(BusinessUnitController::class)->group(function () {
    Route::post('/area-management-bu/search', 'search');
    Route::post('/area-management-bu/add', 'add');
    Route::get('/area-management-bu/edit/{id}', 'edit');
    Route::put('/area-management-bu/update/{id}', 'update');
    Route::delete('/area-management-bu/delete/{id}', 'destroy');
  });
  // Area Management (Cost Center)
  Route::controller(CostCenterController::class)->group(function () {
    Route::post('/area-management-cc/search', 'search');
    Route::post('/area-management-cc/add', 'add');
    Route::get('/area-management-cc/edit/{id}', 'edit');
    Route::put('/area-management-cc/update/{id}', 'update');
    Route::delete('/area-management-cc/delete/{id}', 'destroy');
  });
  // Area Management (Work Center)
  Route::controller(WorkCenterController::class)->group(function () {
    Route::post('/area-management-wc/search', 'search');
    Route::post('/area-management-wc/add', 'add');
    Route::get('/area-management-wc/edit/{id}', 'edit');
    Route::put('/area-management-wc/update/{id}', 'update');
    Route::delete('/area-management-wc/delete/{id}', 'destroy');
  });
  // Job Type
  Route::controller(JobTypeController::class)->group(function () {
    Route::post('/job-type/search', 'search');
    Route::get('/job-type/list', 'list');
    Route::post('/job-type/add', 'add');
    Route::get('/job-type/edit/{id}', 'edit');
    Route::put('/job-type/update/{id}', 'update');
    Route::delete('/job-type/delete/{id}', 'destroy');
  });
  // Signature Type
  Route::controller(SignatureTypeController::class)->group(function () {
    Route::post('/signature-type/search', 'search');
    Route::post('/signature-type/add', 'add');
    Route::get('/signature-type/edit/{id}', 'edit');
    Route::put('/signature-type/update/{id}', 'update');
    Route::delete('/signature-type/delete/{id}', 'destroy');
  });
  // Mer Labor Type
  Route::controller(MerLaborTypeController::class)->group(function () {
    Route::get('/labor-type/list', 'list');
  });
});
