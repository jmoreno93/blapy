<?php

namespace App\Http\Api\V1\Controllers;

use App\Models\ChatUser;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\PaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SubscriptionPaymentController extends Controller
{
    public function handlePayment(Request $request)
    {
        // Validación básica (adáptala según el proveedor)
        $request->validate([
            'platform' => 'required|string',         // telegram, whatsapp, etc.
            'external_id' => 'required|string',      // ID del usuario en esa plataforma
            'provider_driver' => 'required|string',  // paypal, stripe, etc.
            'external_payment_id' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // 1. Obtener o crear el ChatUser
            $chatUser = ChatUser::firstOrCreate(
                ['platform' => $request->platform, 'external_id' => $request->external_id],
                ['username' => $request->input('username'), 'phone' => $request->input('phone')]
            );

            // 2. Crear suscripción (1 mes por defecto, puedes ajustar)
            $start = Carbon::now();
            $end = $start->copy()->addMonth();

            $subscription = Subscription::create([
                'chat_user_id' => $chatUser->id,
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active',
            ]);

            // 3. Obtener el proveedor
            $provider = PaymentProvider::where('driver', $request->provider_driver)->firstOrFail();

            // 4. Registrar el pago
            Payment::create([
                'subscription_id' => $subscription->id,
                'payment_provider_id' => $provider->id,
                'external_payment_id' => $request->external_payment_id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'status' => 'completed', // Puedes hacer lógica para 'pending'
                'metadata' => $request->metadata,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Suscripción y pago registrados con éxito.',
                'subscription_id' => $subscription->id,
                'user_id' => $chatUser->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error: ' . $e->getMessage()
            ], 500);
        }
    }
}
