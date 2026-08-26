<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Get Payment Settings (Public API)
     */
    public function getPaymentSettings()
    {
        $default = [
            'bank_accounts' => [
                [
                    'bank_name' => 'Bank BCA',
                    'account_number' => '8830-192-800',
                    'account_holder' => 'PT AURA Hospitality Indonesia',
                    'is_active' => true,
                ],
                [
                    'bank_name' => 'Bank Mandiri',
                    'account_number' => '137-00-9918-2200',
                    'account_holder' => 'PT AURA Hospitality Indonesia',
                    'is_active' => true,
                ],
            ],
            'qris_image_path' => null,
            'qris_notes' => 'Pindai kode QRIS menggunakan m-Banking atau e-Wallet (Gopay, OVO, Dana, LinkAja, ShopeePay) untuk pembayaran.',
            'whatsapp_number' => '6281234567890',
        ];

        $settings = Setting::get('payment_settings', $default);
        if (empty($settings['whatsapp_number'])) {
            $settings['whatsapp_number'] = '6281234567890';
        }

        // Standardize output & construct full public URL for QRIS image
        if (!empty($settings['qris_image_path'])) {
            $settings['qris_url'] = asset('storage/' . $settings['qris_image_path']);
        } else {
            $settings['qris_url'] = null;
        }

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * Update Payment Settings & Manage QRIS Image (Protected API for Admin)
     */
    public function updatePaymentSettings(Request $request)
    {
        $request->validate([
            'bank_accounts' => 'nullable',
            'qris_notes' => 'nullable|string',
            'whatsapp_number' => 'nullable|string',
            'qris_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120',
            'delete_qris' => 'nullable|boolean',
        ]);

        $default = [
            'bank_accounts' => [],
            'qris_image_path' => null,
            'qris_notes' => 'Pindai kode QRIS menggunakan m-Banking atau e-Wallet untuk pembayaran.',
            'whatsapp_number' => '6281234567890',
        ];

        $currentSettings = Setting::get('payment_settings', $default);

        // Process Bank Accounts payload
        if ($request->has('bank_accounts')) {
            $bankAccounts = $request->input('bank_accounts');
            if (is_string($bankAccounts)) {
                $decoded = json_decode($bankAccounts, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $bankAccounts = $decoded;
                }
            }
            if (is_array($bankAccounts)) {
                $currentSettings['bank_accounts'] = $bankAccounts;
            }
        }

        // Process QRIS Notes
        if ($request->has('qris_notes')) {
            $currentSettings['qris_notes'] = $request->input('qris_notes');
        }

        // Process WhatsApp Number
        if ($request->has('whatsapp_number')) {
            $rawWa = $request->input('whatsapp_number');
            $cleanWa = preg_replace('/[^0-9]/', '', $rawWa);
            $currentSettings['whatsapp_number'] = $cleanWa;
        }

        // Handle Delete QRIS
        $shouldDeleteQris = filter_var($request->input('delete_qris'), FILTER_VALIDATE_BOOLEAN);
        if ($shouldDeleteQris) {
            if (!empty($currentSettings['qris_image_path']) && Storage::disk('public')->exists($currentSettings['qris_image_path'])) {
                Storage::disk('public')->delete($currentSettings['qris_image_path']);
            }
            $currentSettings['qris_image_path'] = null;
        } 
        // Handle Upload / Update QRIS image
        else if ($request->hasFile('qris_image')) {
            // Delete old QRIS image if present
            if (!empty($currentSettings['qris_image_path']) && Storage::disk('public')->exists($currentSettings['qris_image_path'])) {
                Storage::disk('public')->delete($currentSettings['qris_image_path']);
            }

            $file = $request->file('qris_image');
            $filename = 'qris_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('qris', $filename, 'public');

            $currentSettings['qris_image_path'] = $path;
        }

        // Save updated settings
        Setting::set('payment_settings', $currentSettings);

        // Attach full URL for response
        if (!empty($currentSettings['qris_image_path'])) {
            $currentSettings['qris_url'] = asset('storage/' . $currentSettings['qris_image_path']);
        } else {
            $currentSettings['qris_url'] = null;
        }

        return response()->json([
            'message' => 'Pengaturan pembayaran & QRIS berhasil diperbarui!',
            'data' => $currentSettings
        ]);
    }
}
