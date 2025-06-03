<?php

namespace App\Http\Api\V1\Controllers\Webhook;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ChatMessage;

class WebhookWhatsapp
{
    public function __invoke(Request $request)
    {
        $data = $request->all();

        Log::info('Mensaje WhatsApp recibido', $data);

        // Suponiendo estructura del payload de WhatsApp
        $message = $data['message'] ?? null;
        $from = $data['from'] ?? null;

        if (!$message || !$from) {
            return response()->json(['error' => 'Datos de mensaje incompletos'], 422);
        }

        // Guardamos mensaje en la tabla chat_messages
        ChatMessage::create([
            'user_identifier' => $from,
            'message' => $message,
            'platform' => 'whatsapp',
        ]);

        return response()->json(['message' => 'Mensaje guardado'], 201);
    }
}
