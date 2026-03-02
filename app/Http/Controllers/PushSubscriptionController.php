<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string', 'url'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $request->string('endpoint'),
            $request->string('keys.p256dh'),
            $request->string('keys.auth'),
        );

        return response()->json(['status' => 'subscribed']);
    }

    public function destroy(Request $request): Response
    {
        $request->validate([
            'endpoint' => ['required', 'string', 'url'],
        ]);

        $request->user()->deletePushSubscription($request->string('endpoint'));

        return response()->noContent();
    }
}
