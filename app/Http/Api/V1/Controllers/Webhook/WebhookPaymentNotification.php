<?php

namespace App\Http\Api\V1\Controllers\Webhook;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;

class WebhookPaymentNotification
{
    public function __invoke(Request $request)
    {
        $payload = $request->all();

        // Logging para depuración
        Log::info('Webhook de pago recibido', $payload);

        // Suponiendo que el payload incluye un 'subscription_id' y 'status'
        $subscriptionId = $payload['subscription_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$subscriptionId || !$status) {
            return response()->json(['error' => 'Datos incompletos'], 422);
        }

        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return response()->json(['error' => 'Suscripción no encontrada'], 404);
        }

        // Actualizar el estado de la suscripción (esto es ejemplo)
        $subscription->status = $status;
        $subscription->save();

        return response()->json(['message' => 'Suscripción actualizada'], 200);
    }
}
