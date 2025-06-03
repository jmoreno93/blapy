<?php

namespace App\Http\Api\V1\Controllers\Subscriptions;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionUpdateController extends Controller
{
    public function __invoke(Request $request, Subscription $subscription)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|string',
            'payment_reference' => 'sometimes|string|unique:subscriptions,payment_reference,' . $subscription->id,
            'status' => 'sometimes|string',
            'started_at' => 'sometimes|date',
            'expires_at' => 'sometimes|date|after_or_equal:started_at',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subscription->update($validator->validated());

        return response()->json($subscription);
    }
}
