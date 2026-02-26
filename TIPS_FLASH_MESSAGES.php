<?php

/**
 * TIPS & TRICKS - Spatie Laravel Flash
 * 
 * File ini berisi tips praktis untuk menggunakan flash messages
 * dalam pengembangan aplikasi ini.
 */

// ============================================
// 1. FLASH MESSAGES BASICS
// ============================================

// Standard usage
// return redirect()->route('index')->with('success', 'Message');

// Chaining multiple messages (hanya satu yang akan tampil)
// return redirect()->route('index')
//     ->with('success', 'Primary message')
//     ->with('info', 'Secondary message'); // Ini akan di-override


// ============================================
// 2. DENGAN ERROR VALIDATION
// ============================================

// Default Laravel validation errors ditampilkan otomatis
// Namun bisa tambah flash message:
// return redirect()->back()
//     ->withErrors($validator)
//     ->withInput()
//     ->with('info', 'Mohon periksa kembali form Anda');


// ============================================
// 3. DENGAN INPUT PRESERVATION
// ============================================

// Simpan input jika ada error
// return redirect()->back()
//     ->withInput()
//     ->with('error', 'Email sudah terdaftar');

// Di blade template, gunakan old():
// <input type="email" value="{{ old('email') }}">


// ============================================
// 4. CONDITIONAL FLASH
// ============================================

// Flash message berdasarkan kondisi
// if ($model->wasRecentlyCreated) {
//     $message = 'Data baru berhasil ditambahkan!';
// } else {
//     $message = 'Data berhasil diperbarui!';
// }
// 
// return redirect()->route('index')->with('success', $message);


// ============================================
// 5. FLASH DENGAN DATA TAMBAHAN
// ============================================

// Return juga data lain
// return redirect()->route('index')
//     ->with('success', 'Data berhasil ditambahkan!')
//     ->with('created_id', $model->id)
//     ->with('created_at', $model->created_at);

// Di template bisa akses:
// {{ session('created_id') }}
// {{ session('created_at') }}


// ============================================
// 6. DELAYED REDIRECT DENGAN FLASH
// ============================================

// Tidak bisa delay langsung di Laravel, tapi bisa di blade:
// <script>
//     setTimeout(() => {
//         window.location.href = '{{ route("index") }}';
//     }, 2000);
// </script>


// ============================================
// 7. FLASH PADA AJAX (Bonus Tips)
// ============================================

// Jika menggunakan AJAX, return JSON:
// return response()->json([
//     'success' => true,
//     'message' => 'Data berhasil disimpan!',
//     'data' => $model->toArray()
// ]);

// Handle di JavaScript:
// if (response.success) {
//     showAlert('success', response.message);
// }


// ============================================
// 8. FLASH DENGAN FORMATTING HTML
// ============================================

// Bisa gunakan HTML dalam flash message:
// return redirect()->route('index')
//     ->with('success', 'Data <strong>berhasil</strong> ditambahkan!');

// PERHATIAN: Sanitasi input dari user untuk keamanan!


// ============================================
// 9. MULTI-LANGUAGE FLASH MESSAGES
// ============================================

// Gunakan Laravel Localization:
// return redirect()->route('index')
//     ->with('success', __('messages.success.created'));

// Di resources/lang/id/messages.php:
// return [
//     'success' => [
//         'created' => 'Data berhasil ditambahkan!',
//         'updated' => 'Data berhasil diperbarui!',
//         'deleted' => 'Data berhasil dihapus!',
//     ],
//     'error' => [
//         'not_found' => 'Data tidak ditemukan!',
//     ],
// ];


// ============================================
// 10. CUSTOM FLASH HELPER (OPTIONAL)
// ============================================

// Buat file app/Helpers/FlashHelper.php:
/*

namespace App\Helpers;

class FlashHelper
{
    public static function success($message, $redirect = null)
    {
        return redirect($redirect)->with('success', $message);
    }

    public static function error($message, $redirect = null)
    {
        return redirect($redirect)->with('error', $message);
    }

    public static function created($model, $redirect = null)
    {
        return self::success(
            "Data '{$model->name}' berhasil ditambahkan!",
            $redirect
        );
    }

    public static function updated($model, $redirect = null)
    {
        return self::success(
            "Data '{$model->name}' berhasil diperbarui!",
            $redirect
        );
    }

    public static function deleted($name = 'Data', $redirect = null)
    {
        return self::success(
            "{$name} berhasil dihapus!",
            $redirect
        );
    }
}

*/

// Penggunaan:
// use App\Helpers\FlashHelper;
// return FlashHelper::created($model, 'index');


// ============================================
// 11. DEBUGGING FLASH MESSAGES
// ============================================

// Jika flash message tidak muncul:

// 1. Cek session tersimpan
echo session('success');  // Harus output message

// 2. Cek component terpanggil
// Buka inspector > Network > check response headers

// 3. Cek Alpine.js loaded
console.log(Alpine);  // Di browser console

// 4. Cek middleware
// File: app/Http/Middleware/EncryptCookies.php


// ============================================
// 12. TESTING FLASH MESSAGES
// ============================================

// Unit test:
/*
public function test_create_shows_success_message()
{
    $response = $this->post('/routes', [
        'route_name' => 'Test Route',
        'pickup_location_id' => 1,
        'delivery_location_ids' => [2, 3],
        'distance_km' => 10,
        'estimated_time' => 30,
        'status' => 'active',
    ]);

    $response->assertSessionHas('success');
}
*/


// ============================================
// CONTOH IMPLEMENTASI REAL
// ============================================

// File: app/Http/Controllers/RouteController.php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_name' => 'required|string',
            'pickup_location_id' => 'required|exists:locations,id',
            'delivery_location_ids' => 'required|array',
            'distance_km' => 'required|numeric',
            'estimated_time' => 'required|integer',
            'status' => 'required|in:active,inactive,completed',
        ]);

        try {
            $route = Route::create($validated);

            // Success!
            return redirect()->route('admin.routeIndex')
                ->with('success', "Rute '{$route->route_name}' berhasil dibuat!");

        } catch (\Exception $e) {
            // Error!
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat rute: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'route_name' => 'required|string',
            'status' => 'required|in:active,inactive,completed',
        ]);

        $route->update($validated);

        return redirect()->route('admin.routeIndex')
            ->with('success', 'Rute berhasil diperbarui!');
    }

    public function destroy(Route $route)
    {
        $routeName = $route->route_name;
        $route->delete();

        return redirect()->route('admin.routeIndex')
            ->with('success', "Rute '{$routeName}' berhasil dihapus!");
    }
}
