<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPaymentSettings = [
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

        if (!Setting::get('payment_settings')) {
            Setting::set('payment_settings', $defaultPaymentSettings);
        }
    }
}
