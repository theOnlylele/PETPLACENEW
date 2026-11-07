<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\VetDashboardController;
use App\Http\Controllers\Admin\StaffApprovalController;

// HOME
Route::get('/', function () {
    return view('welcome');
});

// CHOOSE ACCOUNT
Route::get('/choose', function () {
    return view('choose');
})->name('choose');

// VET AUTH
Route::get('/login/vet', function () {
    return view('auth.login-vet');
})->name('login.vet');

Route::get('/register/vet', function () {
    return view('auth.register-vet');
})->name('register.vet');

// OWNER AUTH
Route::get('/login/owner', function () {
    return view('auth.login-owner');
})->name('login.owner');

Route::get('/register/owner', function () {
    return view('auth.register-owner');
})->name('register.owner');

// PROTECTED DASHBOARDS
Route::middleware(['auth'])->group(function () {

    // GENERAL DASHBOARD REDIRECT
    Route::get('/dashboard', function () {
        return redirect()->route(
            auth()->user()->dashboardRouteName()
        );
    })->name('dashboard');

    // PROFILE
    Route::get('/profile', [
        App\Http\Controllers\ProfileController::class,
        'edit'
    ])->name('profile.edit');

    Route::patch('/profile', [
        App\Http\Controllers\ProfileController::class,
        'update'
    ])->name('profile.update');

    Route::delete('/profile', [
        App\Http\Controllers\ProfileController::class,
        'destroy'
    ])->name('profile.destroy');

    // ADMIN DASHBOARD
    Route::get('/admin/dashboard', function () {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.dashboard');
    })->name('admin.dashboard');

    // ADMIN STAFF APPROVAL
    Route::get('/admin/staff-applications', [
        StaffApprovalController::class,
        'index'
    ])->name('admin.staff-applications');

    Route::patch('/admin/staff-applications/{vet}/approve', [
        StaffApprovalController::class,
        'approve'
    ])->name('admin.staff-approve');

    Route::patch('/admin/staff-applications/{vet}/reject', [
        StaffApprovalController::class,
        'reject'
    ])->name('admin.staff-reject');

    // VET DASHBOARD
    Route::get('/vet/dashboard', [
        VetDashboardController::class,
        'show'
    ])->name('vet.dashboard');

    Route::patch('/vet/appointments/{appointment}', [
        VetDashboardController::class,
        'updateAppointment'
    ])->name('vet.appointments.update');

    Route::put('/vet/availability', [
        VetDashboardController::class,
        'saveAvailability'
    ])->name('vet.availability.update');

    Route::post('/vet/visits', [
        VetDashboardController::class,
        'storeVisit'
    ])->name('vet.visits.store');

    Route::put('/vet/patients/{pet}', [
        VetDashboardController::class,
        'updatePatient'
    ])->name('vet.patients.update');

    Route::post('/vet/vaccinations', [
        VetDashboardController::class,
        'storeVaccination'
    ])->name('vet.vaccinations.store');

    Route::get('/vet/vaccinations/{vaccinationRecord}/certificate', [
        VetDashboardController::class,
        'certificate'
    ])->name('vet.vaccinations.certificate');

    // GROOMER DASHBOARD
    Route::get('/groomer/dashboard', [
        App\Http\Controllers\GroomerDashboardController::class,
        'show'
    ])->name('groomer.dashboard');

    // OWNER PAGES
    Route::get('/owner/dashboard', [
        OwnerDashboardController::class,
        'show'
    ])->name('owner.dashboard');

    // MY PETS
    Route::get('/owner/pets', [
        OwnerDashboardController::class,
        'pets'
    ])->name('owner.pets');

    // ADD PET
    Route::get('/owner/pets/add', [
        OwnerDashboardController::class,
        'createPet'
    ])->name('owner.pets.create');

    // APPOINTMENTS
    Route::get('/owner/appointments', [
        OwnerDashboardController::class,
        'appointments'
    ])->name('owner.appointments');

    // VACCINATION RECORDS
    Route::get('/owner/vaccinations', [
        OwnerDashboardController::class,
        'vaccinations'
    ])->name('owner.vaccinations');

    // OWNER PET ACTIONS
    Route::post('/owner/pets', [
        OwnerDashboardController::class,
        'storePet'
    ])->name('owner.pets.store');

    Route::patch('/owner/pet-profile/{pet?}', [
        OwnerDashboardController::class,
        'updatePet'
    ])->name('owner.pet-profile.update');

    // OWNER APPOINTMENT ACTION
    Route::post('/owner/appointments', [
        OwnerDashboardController::class,
        'requestAppointment'
    ])->name('owner.appointments.store');
});

// BREEZE AUTH ROUTES
require __DIR__.'/auth.php';
