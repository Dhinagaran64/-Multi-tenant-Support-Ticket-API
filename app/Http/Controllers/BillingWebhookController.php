<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();

        $signature = $request->header('X-Billing-Signature');

        $normalizedPayload = json_encode(json_decode($rawPayload, true), JSON_UNESCAPED_SLASHES);

        $expectedSignature = hash_hmac(
            'sha256',
            $normalizedPayload,
            config('services.billing.webhook_secret')
        );

        Log::info('Webhook Debugging', [
            'got_signature'      => $signature,
            'expected_signature' => $expectedSignature,
            'payload_empty'      => empty($payload),
            'secret_exists'      => !empty(config('services.billing.webhook_secret'))
        ]);

        // if (app()->environment('production')) {
        //     if (!$signature || !hash_equals($expectedSignature, $signature)) {
        //         return response()->json(['message' => 'Invalid signature.'], 401);
        //     }
        // }

        if (!$signature || !hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Invalid signature.'
            ], 401);
        }

        $data = $request->json()->all();

        if (!isset($data['id']) || !isset($data['type']) || 
            !isset($data['data']['tenant_id']) || !isset($data['data']['status'])
        ) {
            return response()->json([
                'message' => 'Invalid webhook payload.'
            ], 422);
        }

        $eventId = $data['id'];

        if (WebhookEvent::where('event_id', $eventId)->exists()) {
            return response()->json([
                'message' => 'Webhook already processed.'
            ], 200);
        }

        $tenant = Tenant::find($data['data']['tenant_id']);

        if (!$tenant) {
            return response()->json([
                'message' => 'Tenant not found.'
            ], 404);
        }

        DB::transaction(function () use ($data, $tenant) {

            $tenant->update([
                'subscription_status' => $data['data']['status'],
            ]);

            WebhookEvent::create([
                'event_id' => $data['id'],
                'event_type' => $data['type'],
                'processed_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Webhook processed successfully.'
        ], 200);
    }
}