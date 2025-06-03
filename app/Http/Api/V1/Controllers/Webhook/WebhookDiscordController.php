<?php

namespace App\Http\Api\V1\Controllers\Webhook;
use App\Http\Api\V1\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ChatUser;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class WebhookDiscordController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->all();
        Log::info('Mensaje Discord recibido', $data);

        // Validar tipo de interacción (ping, command, etc)
        if (isset($data['type'])) {
            // Responder PING para verificación Discord
            if ($data['type'] == 1) {
                return response()->json(['type' => 1]);
            }
        }

        // Solo responder comandos de tipo 2 (application_command)
        if (!isset($data['type']) || $data['type'] != 2) {
            return response()->json(['error' => 'No es comando válido'], 400);
        }

        // Extraer info básica
        $userId = $data['member']['user']['id'] ?? null;
        $userName = $data['member']['user']['username'] ?? 'unknown';
        $guildId = $data['guild_id'] ?? null;
        $commandName = $data['data']['name'] ?? null;
        $commandOptions = $data['data']['options'] ?? [];

        // Suponiendo que el texto a analizar viene en la opción "mensaje"
        $messageText = '';
        foreach ($commandOptions as $opt) {
            if ($opt['name'] == 'mensaje') {
                $messageText = $opt['value'];
            }
        }

        if (!$userId || !$messageText) {
            return response()->json([
                'type' => 4, // respuesta inmediata al usuario
                'data' => ['content' => 'Falta información o mensaje vacío.']
            ]);
        }

        // Buscar o crear usuario
        $user = ChatUser::firstOrCreate(
            ['platform' => 'discord', 'external_id' => $userId, 'username' => $userName],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Buscar sesión activa o crear
        $session = ChatSession::where('chat_user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$session) {
            $session = ChatSession::create([
                'chat_user_id' => $user->id,
                'status' => 'active',
            ]);
        }

        // Guardar mensaje usuario
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'message' => $messageText,
            'direction' => 'inbound',
            'status' => 'received',
            'sender' => 'user',
            'platform' => 'discord',
            'metadata' => $data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Obtener últimos mensajes para contexto (puedes aplicar resumen igual que Telegram)
        $lastMessages = ChatMessage::where('chat_session_id', $session->id)
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $messagesForLLM = [
            [
                'role' => 'system',
                'content' => 'Eres un asistente de psicología empático y respetuoso que escucha atentamente y responde con apoyo emocional y consejos útiles. Responde de forma resumida y clara.'
            ],
        ];

        foreach ($lastMessages as $msg) {
            $messagesForLLM[] = [
                'role' => $msg->sender === 'bot' ? 'assistant' : 'user',
                'content' => $msg->message,
            ];
        }

        // Consultar DeepSeek para generar respuesta
        $llmResponse = $this->askDeepSeek($messagesForLLM);

        // Guardar respuesta bot
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'message' => $llmResponse,
            'direction' => 'outbound',
            'sender' => 'bot',
            'status' => 'sent',
            'platform' => 'discord',
        ]);

        // Responder a Discord (type=4 para respuesta inmediata visible)
        return response()->json([
            'type' => 4,
            'data' => [
                'content' => $llmResponse,
            ]
        ]);
    }

    // Método askDeepSeek igual que el de Telegram
    protected function askDeepSeek(array $messages): string
    {
        // ... tu implementación ...
    }
}
