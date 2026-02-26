<?php

use App\Models\Location;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SuperAdminUserController;
use App\Http\Controllers\Admin\DashboardAdminController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('user.dashboard');
// })->middleware(['auth', 'verified'])->name('user.dashboard');

// Admin routes (superadmin + admin can access)
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    
    // Dashboard
    

    // Route
    

    // Driver assignment
    Route::post('/routes/{routeId}/assign-driver', [RouteController::class, 'assignDriver'])->name('routes.assign-driver');
    Route::delete('/routes/{routeId}/unassign-driver', [RouteController::class, 'unassignDriver'])->name('routes.unassign-driver');

    
    
    // // Export data
    // Route::get('/dashboard/export', [DashboardAdminController::class, 'export'])->name('dashboard.export');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardAdminController::class, 'refresh'])->name('dashboardRefresh');

    // Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locationIndex');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locationCreate');
    Route::get('/locations/show/{location}', [LocationController::class, 'show'])->name('locationShow');
    Route::post('/locations/store', [LocationController::class, 'store'])->name('locationStore');
    Route::put('/locations/update/{location}', [LocationController::class, 'update'])->name('locationUpdate');
    Route::delete('/locations/destroy/{location}', [LocationController::class, 'destroy'])->name('locationDestroy');

    // Routes
    Route::get('/routes', [RouteController::class, 'index'])->name('routeIndex');
    Route::get('/routes/create', [RouteController::class, 'create'])->name('routeCreate');
    Route::post('/routes/store', [RouteController::class, 'store'])->name('routeStore');
    Route::put('/routes/update/{route}', [RouteController::class, 'update'])->name('routeUpdate');
    Route::delete('/routes/{route}', [RouteController::class, 'destroy'])->name('routeDestroy');

    // Driver assignment
    Route::post('/routes/{routeId}/assign-driver', [RouteController::class, 'assignDriver'])->name('routes.assign-driver');
    Route::delete('/routes/{route}/unassign-driver', [RouteController::class, 'unassignDriver'])->name('routes.unassign-driver');

    // Driver Available
    Route::get('/drivers/available', [RouteController::class, 'getAvailableDrivers'])->name('drivers.available');

    // Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveryIndex');
    Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveryCreate');
    Route::get('deliveries/{delivery}', [DeliveryController::class, 'show'])->name('deliveryShow');
    Route::post('/deliveries/store', [DeliveryController::class, 'store'])->name('deliveryStore');
    Route::post('/deliveries/{delivery}/cancel', [DeliveryController::class, 'cancel'])->name('deliveryCancel');

    // Laporan (Report)
    Route::get('/report', [ReportController::class, 'index'])->name('report');
    Route::get('/report/export/excel', [ReportController::class, 'exportExcel'])->name('reportExportExcel');
    Route::get('/report/export/pdf', [ReportController::class, 'exportPdf'])->name('reportExportPdf');
    Route::get('/report/chart-data', [ReportController::class, 'chartData'])->name('reportChartData');
});

Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/deliveries', [DriverController::class, 'index'])->name('deliveryIndex');
    Route::get('/deliveries/{delivery}', [DriverController::class, 'show'])->name('deliveryShow');
    Route::post('/deliveries/{delivery}/start', [DriverController::class, 'startDelivery'])->name('startDelivery');
    Route::post('/checkpoints/{checkpoint}/complete', [DriverController::class, 'completedCheckpoint'])->name('completedCheckpoint');
    Route::post('/checkpoints/{checkpoint}/arrive', [DriverController::class, 'arriveCheckpoint'])->name('arriveCheckpoint');
    Route::post('/deliveries/{delivery}/update-gps', [DriverController::class, 'updateGPS'])->name('updateGPS');

    // Loading
    Route::post('/checkpoints/{checkpoint}/start-loading', [DriverController::class, 'startLoading'])->name('startLoading');
    Route::post('/checkpoints/{checkpoint}/end-loading', [DriverController::class, 'endLoading'])->name('endLoading');

    // History
    Route::get('/history-delivery', [DriverController::class, 'history'])->name('history');
    Route::post('/checkpoints/{checkpoint}/upload-photo', [DriverController::class, 'uploadPhoto'])->name('uploadPhoto');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
