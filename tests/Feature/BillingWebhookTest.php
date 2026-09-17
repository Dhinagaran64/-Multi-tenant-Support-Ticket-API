<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_webhook_is_processed_only_once(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'subscription_status' => 'cancelled',
        ]);

        $payload = [
            'id' => 'evt_test_123',
            'type' => 'subscription.updated',
            'data' => [
                'tenant_id' => $tenant->id,
                'status' => 'active',
            ],
        ];

        $body = json_encode($payload);

        $signature = hash_hmac(
            'sha256',
            $body,
            config('services.billing.webhook_secret')
        );

        $firstResponse = $this->call(
            'POST',
            '/api/webhook/billing',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_BILLING_SIGNATURE' => $signature,
            ],
            $body
        );

        $firstResponse->assertOk();

        // Send the exact same webhook again
        $secondResponse = $this->call(
            'POST',
            '/api/webhook/billing',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_BILLING_SIGNATURE' => $signature,
            ],
            $body
        );

        $secondResponse->assertOk();

        $secondResponse->assertJson([
            'message' => 'Webhook already processed.',
        ]);

        // Only one webhook event should exist
        $this->assertDatabaseCount('webhook_events', 1);

        $this->assertDatabaseHas('webhook_events', [
            'event_id' => 'evt_test_123',
            'event_type' => 'subscription.updated',
        ]);

        // Tenant subscription should have been updated
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'subscription_status' => 'active',
        ]);
    }
}