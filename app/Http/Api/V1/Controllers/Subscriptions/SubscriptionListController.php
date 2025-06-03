<?php

namespace App\Http\Api\V1\Controllers\Subscriptions;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionListController extends Controller
{
    public function __invoke()
    {
        return Subscription::all();
    }
}
