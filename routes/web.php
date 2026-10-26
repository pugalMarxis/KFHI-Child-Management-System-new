<?php

use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ChildController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventAttendanceController;
use App\Http\Controllers\QuestionnaireResponseController;


use App\Http\Controllers\ChildOfficer\DashboardController;
use App\Http\Controllers\ChildOfficer\ChildRegistrationReportController;
use App\Http\Controllers\ChildOfficer\ChildReportController as ChildOfficerChildReportController;
use App\Http\Controllers\ChildOfficer\ChildController as ChildOfficerChildController;
use App\Http\Controllers\ChildOfficer\GuardianController as ChildOfficerGuardianController;

use App\Http\Controllers\ChildOfficer\QRCodeController as ChildOfficerQRCodeController;
use App\Http\Controllers\ChildOfficer\ChildInformationController as ChildOfficerChildInformationController;
use App\Http\Controllers\ChildOfficer\EventFollowUpController as ChildOfficerEventFollowUpController;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page
Route::get('/', function () {
    return view('home.index');
})->name('home');


// Login page
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');


// Process login
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');


// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Protected Admin Area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Admin User Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/users', UserController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin Child Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/children', ChildController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin Guardian Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/guardians', GuardianController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin Event Management
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/events', EventController::class);


    /*
    |--------------------------------------------------------------------------
    | Admin QR Code
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/admin/children/{child}/qr/generate',
        [QRCodeController::class, 'generate']
    )->name('children.qr.generate');

    Route::get(
        '/admin/children/{child}/qr',
        [QRCodeController::class, 'show']
    )->name('children.qr.show');

    Route::get(
        '/admin/offline-qr',
        [QRCodeController::class, 'offlineQr']
    )->name('admin.offline_qr');

    Route::get(
        '/admin/qr/scan',
        [ChildOfficerQRCodeController::class, 'scan']
    )->name('admin.qr.scan');

    Route::get(
        '/admin/children/{child}/offline-qr/download',
        [QRCodeController::class, 'downloadOfflineSvg']
    )->name('admin.children.offline_qr.download');

    /*
    |--------------------------------------------------------------------------
    | Admin Event Attendance
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/events/{event}/attendance',
        [EventAttendanceController::class, 'index']
    )->name('events.attendance.index');

    Route::post(
        '/admin/events/{event}/attendance/scan',
        [EventAttendanceController::class, 'scan']
    )->name('events.attendance.scan');


    /*
    |--------------------------------------------------------------------------
    | Admin Questionnaires
    |--------------------------------------------------------------------------
    */

    Route::resource(
        '/admin/events/{event}/questionnaires',
        \App\Http\Controllers\Admin\QuestionnaireController::class
    )->names('events.questionnaires');


    /*
    |--------------------------------------------------------------------------
    | Admin Questionnaire Questions
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/create',
        [\App\Http\Controllers\Admin\QuestionController::class, 'create']
    )->name('events.questionnaires.questions.create');

    Route::post(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions',
        [\App\Http\Controllers\Admin\QuestionController::class, 'store']
    )->name('events.questionnaires.questions.store');

    Route::get(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/{question}/edit',
        [\App\Http\Controllers\Admin\QuestionController::class, 'edit']
    )->name('events.questionnaires.questions.edit');

    Route::put(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/{question}',
        [\App\Http\Controllers\Admin\QuestionController::class, 'update']
    )->name('events.questionnaires.questions.update');

    Route::delete(
        '/admin/events/{event}/questionnaires/{questionnaire}/questions/{question}',
        [\App\Http\Controllers\Admin\QuestionController::class, 'destroy']
    )->name('events.questionnaires.questions.destroy');
});


/*
|--------------------------------------------------------------------------
| Public QR Scan
|--------------------------------------------------------------------------
*/

Route::get(
    '/qr/scan/{token}',
    [QRCodeController::class, 'scan']
)->name('qr.scan');


/*
|--------------------------------------------------------------------------
| Questionnaire Response
|--------------------------------------------------------------------------
*/

Route::get(
    '/questionnaires/{questionnaire}/children/{child}/respond',
    [QuestionnaireResponseController::class, 'create']
)->name('questionnaires.responses.create');

Route::post(
    '/questionnaires/{questionnaire}/children/{child}/respond',
    [QuestionnaireResponseController::class, 'store']
)->name('questionnaires.responses.store');

Route::get(
    '/questionnaires/{questionnaire}/children/{child}/success',
    [QuestionnaireResponseController::class, 'success']
)->name('questionnaires.responses.success');


/*
|--------------------------------------------------------------------------
| Child Officer Area
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Child Officer Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/child-officer/dashboard',
        [DashboardController::class, 'index']
    )->name('child_officer.dashboard');


Route::get(
    '/child-officer/reports',
    [ChildOfficerChildReportController::class, 'index']
)->name('child_officer.reports.index');

Route::get('/child-officer/registration-reports', [ChildRegistrationReportController::class, 'index'])
    ->name('child_officer.registration_reports.index');

Route::get('/child-officer/registration-reports/export/excel', [ChildRegistrationReportController::class, 'exportExcel'])
    ->name('child_officer.registration_reports.export.excel');

Route::get('/child-officer/registration-reports/export/pdf', [ChildRegistrationReportController::class, 'exportPdf'])
    ->name('child_officer.registration_reports.export.pdf');


Route::get('/child-officer/reports/child-follow-ups',
    [\App\Http\Controllers\ChildOfficer\ChildFollowUpReportController::class, 'index']
)->name('child_officer.follow_up_reports.children.index');

Route::get('/child-officer/reports/child-follow-ups/export/excel',
    [\App\Http\Controllers\ChildOfficer\ChildFollowUpReportController::class, 'exportExcel']
)->name('child_officer.follow_up_reports.children.export.excel');

Route::get('/child-officer/reports/child-follow-ups/export/pdf',
    [\App\Http\Controllers\ChildOfficer\ChildFollowUpReportController::class, 'exportPdf']
)->name('child_officer.follow_up_reports.children.export.pdf');

Route::get('/child-officer/reports/event-follow-ups',
    [\App\Http\Controllers\ChildOfficer\EventFollowUpReportController::class, 'index']
)->name('child_officer.follow_up_reports.events.index');

Route::get('/child-officer/reports/event-follow-ups/export/excel',
    [\App\Http\Controllers\ChildOfficer\EventFollowUpReportController::class, 'exportExcel']
)->name('child_officer.follow_up_reports.events.export.excel');

Route::get('/child-officer/reports/event-follow-ups/export/pdf',
    [\App\Http\Controllers\ChildOfficer\EventFollowUpReportController::class, 'exportPdf']
)->name('child_officer.follow_up_reports.events.export.pdf');


Route::get(
    '/child-officer/reports/export/excel',
    [ChildOfficerChildReportController::class, 'exportExcel']
)->name('child_officer.reports.export.excel');

Route::get(
    '/child-officer/reports/export/pdf',
    [ChildOfficerChildReportController::class, 'exportPdf']
)->name('child_officer.reports.export.pdf');

    Route::prefix('/child-officer/follow-ups')->name('child_officer.follow-ups.')->group(function () {
        Route::get('/', [ChildOfficerEventFollowUpController::class, 'index'])->name('index');
        Route::get('/create', [ChildOfficerEventFollowUpController::class, 'create'])->name('create');
        Route::post('/', [ChildOfficerEventFollowUpController::class, 'store'])->name('store');
        Route::get('/{followUp}', [ChildOfficerEventFollowUpController::class, 'show'])->name('show');
        Route::post('/{followUp}/updates', [ChildOfficerEventFollowUpController::class, 'storeUpdate'])->name('updates.store');
        Route::post('/{followUp}/actions', [ChildOfficerEventFollowUpController::class, 'storeAction'])->name('actions.store');
        Route::patch('/{followUp}/actions/{action}', [ChildOfficerEventFollowUpController::class, 'updateAction'])->name('actions.update');
        Route::patch('/{followUp}/reschedule', [ChildOfficerEventFollowUpController::class, 'reschedule'])->name('reschedule');
        Route::patch('/{followUp}/complete', [ChildOfficerEventFollowUpController::class, 'complete'])->name('complete');
    });


    /*
    |--------------------------------------------------------------------------
    | Child Officer Children
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/child-officer/children',
        [ChildOfficerChildController::class, 'index']
    )->name('child_officer.children.index');

    Route::get(
        '/child-officer/children/create',
        [ChildOfficerChildController::class, 'create']
    )->name('child_officer.children.create');

    Route::post(
        '/child-officer/children',
        [ChildOfficerChildController::class, 'store']
    )->name('child_officer.children.store');

    Route::get(
        '/child-officer/children/{child}/edit',
        [ChildOfficerChildController::class, 'edit']
    )->name('child_officer.children.edit');

    Route::put(
        '/child-officer/children/{child}',
        [ChildOfficerChildController::class, 'update']
    )->name('child_officer.children.update');

    Route::get(
        '/child-officer/children/{child}',
        [ChildOfficerChildController::class, 'show']
    )->name('child_officer.children.show');


    /*
    |--------------------------------------------------------------------------
    | Child Officer Guardians
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/child-officer/guardians/create',
        [ChildOfficerGuardianController::class, 'create']
    )->name('child_officer.guardians.create');
    
    Route::get(
        '/child-officer/guardians/search-children',
        [ChildOfficerGuardianController::class, 'searchChildren']
    )->name('child_officer.guardians.search_children');
    
    Route::post(
        '/child-officer/guardians',
        [ChildOfficerGuardianController::class, 'store']
    )->name('child_officer.guardians.store');

    Route::get(
        '/child-officer/qr/scan',
        [ChildOfficerQRCodeController::class, 'scan']
    )->name('child_officer.qr.scan');

    Route::post(
        '/child-officer/qr/lookup',
        [ChildOfficerQRCodeController::class, 'lookup']
    )->name('child_officer.qr.lookup');

    Route::get(
        '/child-officer/offline-qr',
        [ChildOfficerQRCodeController::class, 'offlineQr']
    )->name('child_officer.offline_qr');

    Route::delete(
        '/child-officer/children/{child}',
        [ChildOfficerChildController::class, 'destroy']
    )->name('child_officer.children.destroy');

    Route::get(
        '/child-officer/guardians/{guardian}/edit',
        [ChildOfficerGuardianController::class, 'edit']
    )->name('child_officer.guardians.edit');
    
    Route::put(
        '/child-officer/guardians/{guardian}',
        [ChildOfficerGuardianController::class, 'update']
    )->name('child_officer.guardians.update');
    
    Route::delete(
        '/child-officer/guardians/{guardian}',
        [ChildOfficerGuardianController::class, 'destroy']
    )->name('child_officer.guardians.destroy');

    Route::get(
        '/child-officer/children/{child}/information/create',
        [ChildOfficerChildInformationController::class, 'create']
    )->name('child_officer.child_information.create');
    
    Route::post(
        '/child-officer/children/{child}/information',
        [ChildOfficerChildInformationController::class, 'store']
    )->name('child_officer.child_information.store');




});
