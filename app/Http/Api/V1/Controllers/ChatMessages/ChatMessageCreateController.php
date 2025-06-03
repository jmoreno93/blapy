<?php

namespace App\Http\Api\V1\Controllers\ChatMessages;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatMessageCreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_user_id' => 'required|exists:chat_users,id',
            'message' => 'required|string',
            'platform' => 'required|string',
            'external_message_id' => 'required|string|unique:chat_messages,external_message_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chatMessage = ChatMessage::create($validator->validated());

        return response()->json($chatMessage, 201);
    }
}
