<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentProvidersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('payment_providers')->insert([
            ['name' => 'PayPal', 'driver' => 'paypal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stripe', 'driver' => 'stripe', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'MercadoPago', 'driver' => 'mercadopago', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
