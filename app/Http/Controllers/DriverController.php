<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryCheckpoint;
use Google\Service\Drive\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    /**
     * Display active deliveries for driver
     */
    public function index()
    {
        $driver = Auth::user();

        $deliveries = Delivery::with(['route.pickupLocation', 'route.deliveryLocations', 'checkpoints.location'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        $completedToday = Delivery::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        return view('user.index', compact('deliveries', 'completedToday'));
    }

    /**
     * Show delivery detail for driver
     */
    public function show(Delivery $delivery)
    {
        if ($delivery->driver_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $delivery->load([
            'route.pickupLocation',
            'route.deliveryLocations',
            'checkpoints.location',
            'checkpoints.checkpointPhotos'
        ]);

        $currentCheckpoint = $delivery->getCurrentCheckpoint();
        $nextCheckpoint = $delivery->checkpoints()
            ->where('sequence', '>', $delivery->current_sequence)
            ->where('status', 'pending')
            ->orderBy('sequence')
            ->first();

        return view('user.show', compact('delivery', 'currentCheckpoint', 'nextCheckpoint'));
    }

    /**
     * Start delivery
     */
    public function startDelivery(Delivery $delivery)
    {
        try {
            // Authorization
            if ($delivery->driver_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Validation
            if (!$delivery->canStart()) {
                return response()->json(['error' => 'Delivery sudah dimulai atau selesai'], 400);
            }

            DB::beginTransaction();

            // Start delivery (updates delivery + first checkpoint)
            $delivery->start();

            // Verify first checkpoint was activated
            $firstCheckpoint = $delivery->checkpoints()
                ->where('sequence', 0)
                ->first();

            if (!$firstCheckpoint) {
                throw new \Exception('First checkpoint not found');
            }

            if ($firstCheckpoint->fresh()->status !== 'in_progress') {
                throw new \Exception('Failed to activate first checkpoint');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery berhasil dimulai!',
                'delivery' => $delivery->fresh()->load('checkpoints')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Arrive at checkpoint
     */
    public function arriveCheckpoint(Request $request, DeliveryCheckpoint $checkpoint)
    {
        try {
            $data = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            // Authorization
            if ($checkpoint->delivery->driver_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();

            // Mark arrival
            $checkpoint->markArrival($data['latitude'], $data['longitude']);

            // Update delivery location
            $checkpoint->delivery->updateLocation($data['latitude'], $data['longitude']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tiba di checkpoint!',
                'checkpoint' => $checkpoint->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Start loading/unloading
     */
    public function startLoading(DeliveryCheckpoint $checkpoint)
    {
        try {
            // Authorization
            if ($checkpoint->delivery->driver_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $checkpoint->startLoading();

            return response()->json([
                'success' => true,
                'message' => 'Loading/unloading dimulai!',
                'checkpoint' => $checkpoint->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * End loading/unloading
     */
    public function endLoading(DeliveryCheckpoint $checkpoint)
    {
        try {
            // Authorization
            if ($checkpoint->delivery->driver_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $checkpoint->endLoading();

            return response()->json([
                'success' => true,
                'message' => 'Loading/unloading selesai!',
                'checkpoint' => $checkpoint->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload photo
     */
    public function uploadPhoto(Request $request, DeliveryCheckpoint $checkpoint)
    {
        $request->validate([
            'photo'     => 'required|string',
            'type'      => 'required|in:proof,damage,other',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'caption'   => 'nullable|string|max:255'
        ]);

        try {
            // ==============================
            // DECODE BASE64
            // ==============================
            $photoData = $request->photo;

            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $matches)) {
                $imageType    = $matches[1];
                $base64String = substr($photoData, strpos($photoData, ',') + 1);
            } else {
                $imageType    = 'jpeg';
                $base64String = $photoData;
            }

            // Hapus whitespace/newline yang bisa menyebabkan file korup
            $base64String = preg_replace('/\s+/', '', $base64String);
            $photoBinary  = base64_decode($base64String, true);

            if ($photoBinary === false) {
                return response()->json(['success' => false, 'error' => 'Invalid base64 image data'], 400);
            }

            // ==============================
            // BUAT PATH DAN FILENAME
            // ==============================
            $filename = sprintf('delivery_%d_checkpoint_%d_%s_%s.%s',
                $checkpoint->delivery_id,
                $checkpoint->id,
                $checkpoint->type,
                now()->format('Ymd_His'),
                $imageType
            );

            $path = sprintf('deliveries/%s/%s/%s',
                now()->format('Y-m'),
                $checkpoint->delivery->delivery_code,
                $filename
            );

            // ==============================
            // UPLOAD KE GOOGLE DRIVE
            // ==============================
            $tmpPath  = storage_path('app/tmp_' . uniqid() . '.' . $imageType);
            file_put_contents($tmpPath, $photoBinary);

            $uploaded = Storage::disk('google')->put($path, fopen($tmpPath, 'r'));

            // Hapus file sementara setelah upload
            @unlink($tmpPath);

            if (!$uploaded) {
                throw new \Exception('Failed to upload photo to Google Drive');
            }

            // ==============================
            // AMBIL FILE ID GOOGLE DRIVE
            // Dilakukan sekali saat upload, disimpan ke DB
            // supaya tidak perlu hit API setiap kali foto ditampilkan
            // ==============================
            $driveFileId = $this->getDriveFileId($path);

            // Set file public agar bisa diakses tanpa login Google
            if ($driveFileId) {
                $this->makeGoogleDriveFilePublic($driveFileId);
            }

            // ==============================
            // SIMPAN KE DATABASE
            // ==============================
            $photo = $checkpoint->addPhoto(
                $path,
                $request->type,
                $request->latitude,
                $request->longitude,
                $request->caption,
                $driveFileId  // ← simpan file ID ke DB
            );

            // ==============================
            // BUAT URL UNTUK RESPONSE
            // ==============================
            $photoUrl = $driveFileId
                ? "https://drive.google.com/uc?export=view&id={$driveFileId}"
                : null;

            return response()->json([
                'success'   => true,
                'message'   => 'Foto berhasil diupload!',
                'photo'     => $photo,
                'photo_url' => $photoUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('Upload foto gagal', [
                'checkpoint_id' => $checkpoint->id,
                'error'         => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'error' => 'Gagal upload foto: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Complete checkpoint: Upload signature ke Google Drive
     */
    public function completedCheckpoint(Request $request, DeliveryCheckpoint $checkpoint)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'signature'      => 'nullable|string',
            'notes'          => 'nullable|string'
        ]);

        try {
            $signaturePath       = null;
            $signatureDriveId    = null;

            // ===================================
            // UPLOAD SIGNATURE KE GOOGLE DRIVE
            // ===================================
            if ($request->filled('signature')) {
                $signatureData = $request->signature;

                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $matches)) {
                    $imageType    = $matches[1];
                    $base64String = substr($signatureData, strpos($signatureData, ',') + 1);
                } else {
                    $imageType    = 'png';
                    $base64String = $signatureData;
                }

                // Bersihkan whitespace agar tidak korup
                $base64String    = preg_replace('/\s+/', '', $base64String);
                $signatureBinary = base64_decode($base64String, true);

                if ($signatureBinary !== false) {
                    $filename = sprintf('signature_delivery_%d_checkpoint_%d_%s.%s',
                        $checkpoint->delivery_id,
                        $checkpoint->id,
                        now()->format('Ymd_His'),
                        $imageType
                    );

                    $signaturePath = sprintf('signatures/%s/%s/%s',
                        now()->format('Y-m'),
                        $checkpoint->delivery->delivery_code,
                        $filename
                    );

                    // Upload via tmpfile agar tidak korup
                    $tmpPath = storage_path('app/tmp_sig_' . uniqid() . '.' . $imageType);
                    file_put_contents($tmpPath, $signatureBinary);
                    Storage::disk('google')->put($signaturePath, fopen($tmpPath, 'r'));
                    @unlink($tmpPath);

                    // Ambil file ID dan set public
                    $signatureFileId = $this->getDriveFileId($signaturePath);
                    if ($signatureFileId) {
                        $this->makeGoogleDriveFilePublic($signatureFileId);
                    }
                }
            }

            // ===================================
            // COMPLETE CHECKPOINT
            // ===================================
            $checkpoint->complete(
                $request->recipient_name,
                $signaturePath,
                $request->notes
            );

            // Simpan drive file id signature
            if ($signatureDriveId) {
                $checkpoint->update([
                    'signature_drive_file_id' => $signatureDriveId
                ]);
            }

            // ===================================
            // CEK SEMUA CHECKPOINT
            // ===================================
            $delivery = $checkpoint->delivery;

            $allCompleted = $delivery->checkpoints()
                ->where('status', '!=', 'completed')
                ->count() === 0;

            if ($allCompleted) {
                $delivery->complete();
                $message = '🎉 Semua checkpoint selesai! Delivery completed!';
            } else {
                $delivery->getNextCheckpoints();
                $message = 'Checkpoint berhasil diselesaikan!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'signature_url' => $checkpoint->fresh()->signature_url,
                'all_completed' => $allCompleted,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error'   => 'Gagal menyelesaikan checkpoint: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update GPS location
     */
    public function updateGps(Request $request, Delivery $delivery)
    {
        try {
            $data = $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'speed' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric',
                'heading' => 'nullable|numeric',
                'battery_level' => 'nullable|integer|min:0|max:100'
            ]);

            // Authorization
            if ($delivery->driver_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $delivery->updateLocation(
                $data['latitude'],
                $data['longitude'],
                [
                    'speed' => $data['speed'] ?? null,
                    'accuracy' => $data['accuracy'] ?? null,
                    'heading' => $data['heading'] ?? null,
                    'battery_level' => $data['battery_level'] ?? null
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'GPS updated'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get delivery history
     */
    public function history(Request $request)
    {
        $driver = Auth::user();

        $query = Delivery::with(['route.pickupLocation', 'route.deliveryLocations', 'checkpoints.location', 'checkpoints.checkpointPhotos'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'cancelled']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('data_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginate
        $deliveries = $query->orderBy('completed_at', 'desc')
                        ->paginate(10);

        return view('user.rekap-history', compact('deliveries'));
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Ambil File ID Google Drive dari path yang sudah diupload.
     * Dipanggil SEKALI saat upload, hasilnya disimpan ke DB (drive_file_id).
     *
     * @param string $path Path file di Google Drive
     * @return string|null File ID Google Drive (contoh: 1BxiMVs0XRA5nFMdKvBdBZjgmUUq...)
     */
    private function getDriveFileId(string $path): ?string
    {
        try {
            $adapter  = Storage::disk('google')->getAdapter();
            $metadata = $adapter->getMetadata($path);

            // File ID ada di extraMetadata, bukan di 'path'
            return $metadata['extraMetadata']['id']
                ?? $metadata['extra_metadata']['id']
                ?? null;

        } catch (\Exception $e) {
            Log::warning('Gagal mengambil Drive File ID', [
                'path'  => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Set file Google Drive menjadi public (anyone with link can view).
     * Dipanggil sekali setelah upload supaya foto bisa ditampilkan tanpa login.
     *
     * @param string $fileId File ID Google Drive
     */
    private function makeGoogleDriveFilePublic(string $fileId): bool
    {
        try {
            $adapter = Storage::disk('google')->getAdapter();
            $service = $adapter->getService();

            $permission = new Permission();
            $permission->setType('anyone');
            $permission->setRole('reader');

            $service->permissions->create($fileId, $permission);

            Log::info('Google Drive file set to public', ['file_id' => $fileId]);

            return true;

        } catch (\Exception $e) {
            Log::warning('Gagal set Google Drive file ke public', [
                'file_id' => $fileId,
                'error'   => $e->getMessage()
            ]);
            return false;
        }
    }
}