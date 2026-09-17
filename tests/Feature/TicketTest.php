<?php

namespace Tests\Feature;

use App\Jobs\GenerateTicketSummary;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket_and_cannot_see_another_tenants_tickets(): void
    {
        Queue::fake();

        // Create two tenants
        $tenantA = Tenant::create([
            'name' => 'Tenant A',
            'subscription_status' => 'active',
        ]);

        $tenantB = Tenant::create([
            'name' => 'Tenant B',
            'subscription_status' => 'active',
        ]);

        // Create users for each tenant
        $userA = User::factory()->create([
            'tenant_id' => $tenantA->id,
        ]);

        $userB = User::factory()->create([
            'tenant_id' => $tenantB->id,
        ]);

        // Tenant A creates a ticket
        $responseA = $this->actingAs($userA)
            ->postJson('/api/tickets/create', [
                'title' => 'Tenant A Ticket',
                'description' => 'This ticket belongs to Tenant A.',
                'priority' => 'high',
            ]);

        $responseA->assertOk();

        $ticketAId = $responseA->json('data.ticket.id');

        // Make sure the ticket was created under Tenant A
        $this->assertDatabaseHas('tickets', [
            'id' => $ticketAId,
            'tenant_id' => $tenantA->id,
            'created_by' => $userA->id,
        ]);

        // Make sure the summary job was dispatched
        Queue::assertPushed(GenerateTicketSummary::class);

        // Create a ticket directly for Tenant B
        $ticketB = Ticket::create([
            'tenant_id' => $tenantB->id,
            'created_by' => $userB->id,
            'title' => 'Tenant B Ticket',
            'description' => 'This ticket belongs to Tenant B.',
            'status' => 'open',
            'priority' => 'medium',
            'summary_status' => 'pending',
        ]);

        // Tenant A requests its tickets
        $response = $this->actingAs($userA)
            ->postJson('/api/tickets/list', []);

        $response->assertOk();

        // Tenant A should see its own ticket
        $response->assertJsonFragment([
            'id' => $ticketAId,
        ]);

        // Tenant A must NOT see Tenant B's ticket
        $response->assertJsonMissing([
            'id' => $ticketB->id,
        ]);
    }
}