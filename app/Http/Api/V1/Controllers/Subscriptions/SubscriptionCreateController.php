<?php

namespace App\Http\Api\V1\Controllers\Subscriptions;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionCreateController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'plan_name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_reference' => 'required|string|unique:subscriptions,payment_reference',
            'status' => 'required|string',
            'started_at' => 'required|date',
            'expires_at' => 'required|date|after_or_equal:started_at',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subscription = Subscription::create($validator->validated());

        return response()->json($subscription, 201);
    }
}
