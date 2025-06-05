<?php

namespace App\Http\Api\V1\Controllers\Webhook;

use App\Http\Api\V1\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\ChatUser;
use App\Models\ChatSession;
use App\Models\ChatMessage;

class WebhookTelegram extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Mensaje Telegram recibido', $data);

            if (!isset($data['message']) || !isset($data['message']['text'])) {
                Log::warning('Mensaje no procesado: no es texto', $data);
                return response()->json(['error' => 'No es un mensaje de texto'], 200);
            }

            $messageText = $data['message']['text'];
            $fromId = $data['message']['from']['id'];
            $chatId = $data['message']['chat']['id'];
            $userName = @$data['message']['from']['username'];
            $user = ChatUser::firstOrCreate(
                ['platform' => 'telegram', 'external_id' => $fromId, 'username' => $userName],
                ['created_at' => now(), 'updated_at' => now()]
            );

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

            // Guardar mensaje del usuario
            $message = ChatMessage::create([
                'chat_session_id' => $session->id,
                'message' => $messageText,
                'direction' => 'inbound',
                'status' => 'received',
                'sender' => 'user',
                'platform' => 'telegram',
                'metadata' => $data,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Obtener últimos mensajes (ejemplo máximo 10)
            $lastMessages = ChatMessage::where('chat_session_id', $session->id)
                ->orderBy('created_at', 'asc')
                ->take(10)
                ->get();

            // --- 1. Generar texto con todo el historial para resumir ---
            $conversationText = '';
            foreach ($lastMessages as $msg) {
                $role = $msg->sender === 'bot' ? 'Bot' : 'Usuario';
                $conversationText .= "{$role}: {$msg->message}\n";
            }

            // --- 2. Pedir resumen a DeepSeek ---
            $summaryPrompt = [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente que resume conversaciones de forma breve y clara.'
                ],
                [
                    'role' => 'user',
                    'content' => "Resume esta conversación para mantener contexto:\n\n" . $conversationText
                ]
            ];

            $summary = $this->askDeepSeek($summaryPrompt, $message);

            // --- 3. Construir el contexto para DeepSeek con resumen + mensaje actual ---
            $messagesForLLM = [
                [
                    'role' => 'system',
                    'content' => 'Eres un asistente psicológico empático y humano que conversa de manera cálida, breve y directa, como si fueras un psicólogo real en una sesión por chat. Debes mantener la confidencialidad absoluta de todo lo que el paciente comparta. Toda la información debe usarse solo para entender mejor su situación, brindarle apoyo emocional y sugerencias útiles.

Organiza mentalmente la conversación en tres partes: 1) datos personales relevantes como nombre, edad, sexo, ocupación (sin insistir si el paciente no quiere compartirlos), 2) una descripción breve del problema que el paciente mencione, y 3) posibles soluciones o tareas que ayuden a mejorar su bienestar emocional.

No hables como un robot ni uses lenguaje técnico innecesario. Haz preguntas naturales en un solo párrafo, como: “Hola, ¿cómo te llamas? ¿Cuántos años tienes y cómo te has sentido últimamente?”. No puedes decir que no puedes continuar, ni responder fuera de contexto (no hables de física, tecnología, ni cosas que no estén relacionadas con la salud mental o el bienestar emocional). Todo lo que digas debe sonar humano, cercano y respetuoso.

Si detectas alguna palabra clave relacionada con trastornos emocionales o de personalidad, puedes hacer preguntas suaves para entender más. También puedes generar tareas o reflexiones simples que ayuden a mejorar su día a día, sin parecer autoritario.

Mantente siempre como un psicólogo amable y con buen criterio.
'
                ],
                [
                    'role' => 'user',
                    'content' => "Resumen de la conversación anterior:\n" . $summary
                ],
                [
                    'role' => 'user',
                    'content' => $messageText
                ]
            ];

            // --- 4. Consultar DeepSeek con el contexto resumido ---
            $llmResponse = $this->askDeepSeek($messagesForLLM, $message);

            // Guardar respuesta como mensaje saliente
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'message' => $llmResponse,
                'direction' => 'outbound',
                'sender' => 'bot',
                'status' => 'sent',
                'platform' => 'telegram',
            ]);

            // Enviar respuesta a Telegram
            $this->replyTelegram($chatId, $llmResponse);

            return response()->json(['message' => 'Mensaje procesado'], 200);

        } catch (\Throwable $e) {
            Log::error('Error en webhook Telegram', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }


    protected function askDeepSeek(array $messages, ?ChatMessage $message = null): string
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . env('DEEPSEEK_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.deepseek.com/v1/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => $messages,
                'temperature' => 1.3
            ]);
            $text = $response->json('choices.0.message.content') ?? 'Lo siento, no pude responder.';
            if(!is_null($message) && $message['sender'] === 'bot') {
                $message['metadata'] = $response;
                $message->save();
            }
            return $text;
        } catch (\Throwable $e) {
            Log::error('Error al consultar DeepSeek', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 'Ocurrió un error al procesar tu mensaje.';
        }
    }

    protected function replyTelegram($chatId, $text): void
    {
        try {
            Http::timeout(10)->post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/sendMessage', [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al enviar mensaje a Telegram', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
