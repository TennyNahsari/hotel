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
        if (empty($settings['bank_accounts']) || !is_array($settings['bank_accounts']) || count($settings['bank_accounts']) === 0) {
            $settings['bank_accounts'] = $default['bank_accounts'];
        }
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

    /**
     * Get Social Media Settings (Public & Protected API)
     */
    public function getSocialSettings()
    {
        $default = [
            'instagram' => 'https://instagram.com/aurahotels',
            'twitter' => 'https://twitter.com/aurahotels',
            'youtube' => 'https://youtube.com/@aurahotels',
            'facebook' => 'https://facebook.com/aurahotels',
            'linkedin' => 'https://linkedin.com/company/aurahotels',
            'threads' => 'https://threads.net/@aurahotels',
        ];

        $settings = Setting::get('social_settings', $default);

        return response()->json([
            'status' => 'success',
            'data' => array_merge($default, is_array($settings) ? $settings : [])
        ]);
    }

    /**
     * Update Social Media Settings (Protected API for Admin)
     */
    public function updateSocialSettings(Request $request)
    {
        $request->validate([
            'instagram' => 'nullable|string',
            'twitter' => 'nullable|string',
            'youtube' => 'nullable|string',
            'facebook' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'threads' => 'nullable|string',
        ]);

        $default = [
            'instagram' => 'https://instagram.com/aurahotels',
            'twitter' => 'https://twitter.com/aurahotels',
            'youtube' => 'https://youtube.com/@aurahotels',
            'facebook' => 'https://facebook.com/aurahotels',
            'linkedin' => 'https://linkedin.com/company/aurahotels',
            'threads' => 'https://threads.net/@aurahotels',
        ];

        $currentSettings = Setting::get('social_settings', $default);

        $keys = ['instagram', 'twitter', 'youtube', 'facebook', 'linkedin', 'threads'];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                $currentSettings[$key] = $request->input($key) ?? '';
            }
        }

        Setting::set('social_settings', $currentSettings);

        return response()->json([
            'message' => 'Pengaturan media sosial berhasil diperbarui!',
            'data' => $currentSettings
        ]);
    }

    /**
     * Get Hero Sliders (Public & Protected API)
     */
    public function getHeroSliders(Request $request)
    {
        $branchId = $request->query('branch_id');
        if ($branchId === 'null' || $branchId === 'undefined' || $branchId === '') {
            $branchId = null;
        }

        $sliders = null;
        if ($branchId) {
            $sliders = Setting::get('hero_sliders', null, $branchId);
        }

        if (empty($sliders) || !is_array($sliders)) {
            $sliders = Setting::get('hero_sliders', [], null);
        }

        if (!is_array($sliders)) {
            $sliders = [];
        }

        foreach ($sliders as &$slide) {
            if (!empty($slide['image_path'])) {
                $slide['image_url'] = asset('storage/' . $slide['image_path']);
            } elseif (empty($slide['image_url'])) {
                $slide['image_url'] = null;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $sliders
        ]);
    }

    /**
     * Update Hero Sliders (Protected API for Admin)
     */
    public function updateHeroSliders(Request $request)
    {
        $request->validate([
            'sliders' => 'present|array',
            'branch_id' => 'nullable',
        ]);

        $rawBranchId = $request->input('branch_id');
        $branchId = ($rawBranchId === 'null' || $rawBranchId === 'undefined' || $rawBranchId === '') ? null : $rawBranchId;
        $sliders = $request->input('sliders', []);

        $cleanSliders = [];
        foreach ($sliders as $index => $slide) {
            $isActive = $slide['is_active'] ?? true;
            if (is_string($isActive)) {
                $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
            }

            $cleanSliders[] = [
                'id' => $slide['id'] ?? ('slide_' . time() . '_' . $index),
                'title' => $slide['title'] ?? '',
                'subtitle' => $slide['subtitle'] ?? '',
                'badge' => $slide['badge'] ?? '',
                'image_url' => $slide['image_url'] ?? '',
                'image_path' => $slide['image_path'] ?? null,
                'button_primary_text' => $slide['button_primary_text'] ?? '',
                'button_primary_action' => $slide['button_primary_action'] ?? 'booking',
                'button_primary_link' => $slide['button_primary_link'] ?? '',
                'button_secondary_text' => $slide['button_secondary_text'] ?? '',
                'button_secondary_action' => $slide['button_secondary_action'] ?? 'hall',
                'button_secondary_link' => $slide['button_secondary_link'] ?? '',
                'is_active' => (bool) $isActive,
                'sort_order' => intval($slide['sort_order'] ?? ($index + 1)),
            ];
        }

        Setting::set('hero_sliders', $cleanSliders, $branchId);

        foreach ($cleanSliders as &$slide) {
            if (!empty($slide['image_path'])) {
                $slide['image_url'] = asset('storage/' . $slide['image_path']);
            }
        }

        return response()->json([
            'message' => 'Pengaturan hero slider berhasil disimpan!',
            'data' => $cleanSliders
        ]);
    }

    /**
     * Upload Hero Slider Image File
     */
    public function uploadHeroSliderImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file = $request->file('image');
        $filename = 'slider_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('hero_sliders', $filename, 'public');

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar slider berhasil diunggah',
            'image_path' => $path,
            'image_url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Delete Hero Slider Image File
     */
    public function deleteHeroSliderImage(Request $request)
    {
        $request->validate([
            'image_path' => 'required|string',
        ]);

        $path = $request->input('image_path');
        if (!empty($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar slider berhasil dihapus'
        ]);
    }
}
