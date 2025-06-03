<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

class DeepSeekUtil
{
    public function chat(string $message, array $context = []): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('DEEPSEEK_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post(env('DEEPSEEK_API_URL'), [
            'model' => 'deepseek-chat',
            'messages' => array_merge(
                [['role' => 'system', 'content' => 'Eres un asistente psicológico empático y profesional.']],
                $context,
                [['role' => 'user', 'content' => $message]]
            ),
        ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception('Error al conectarse con DeepSeek: ' . $response->body());
    }
}
