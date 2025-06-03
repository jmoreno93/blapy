<?php

namespace App\Http\Api\V1\Controllers\ChatUsers;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatUserCreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'platform' => 'required|string',
            'external_id' => 'required|string|unique:chat_users,external_id',
            'username' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chatUser = ChatUser::create($validator->validated());

        return response()->json($chatUser, 201);
    }
}
