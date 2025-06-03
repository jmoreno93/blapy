<?php

namespace App\Http\Api\V1\Controllers\Subscriptions;

use App\Models\Subscription;

class SubscriptionDeleteController
{
    public function __invoke(Subscription $subscription)
    {
        $subscription->delete();

        return response()->json(null, 204);
    }
}
