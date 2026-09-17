<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Tenant A
        |--------------------------------------------------------------------------
        */

        $tenantA = Tenant::create([
            'name' => 'Acme Corporation',
            'subscription_status' => 'active',
        ]);

        $userA = User::create([
            'name' => 'John Doe',
            'email' => 'john@acme.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenantA->id,
        ]);

        $agentA = User::create([
            'name' => 'Support Agent A',
            'email' => 'agent@acme.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenantA->id,
        ]);

        Ticket::create([
            'tenant_id' => $tenantA->id,
            'created_by' => $userA->id,
            'assigned_to' => $agentA->id,
            'title' => 'Unable to login',
            'description' => 'Customer is unable to login to the application.',
            'status' => 'open',
            'priority' => 'high',
            'summary_status' => 'pending',
        ]);

        Ticket::create([
            'tenant_id' => $tenantA->id,
            'created_by' => $userA->id,
            'assigned_to' => null,
            'title' => 'Dashboard loading slowly',
            'description' => 'Dashboard takes several seconds to load.',
            'status' => 'in_progress',
            'priority' => 'medium',
            'summary_status' => 'pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Tenant B
        |--------------------------------------------------------------------------
        */

        $tenantB = Tenant::create([
            'name' => 'Globex Industries',
            'subscription_status' => 'active',
        ]);

        $userB = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@globex.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenantB->id,
        ]);

        $agentB = User::create([
            'name' => 'Support Agent B',
            'email' => 'agent@globex.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenantB->id,
        ]);

        Ticket::create([
            'tenant_id' => $tenantB->id,
            'created_by' => $userB->id,
            'assigned_to' => $agentB->id,
            'title' => 'Payment issue',
            'description' => 'Customer is facing an issue with payment.',
            'status' => 'open',
            'priority' => 'high',
            'summary_status' => 'pending',
        ]);

        Ticket::create([
            'tenant_id' => $tenantB->id,
            'created_by' => $userB->id,
            'assigned_to' => null,
            'title' => 'Unable to download report',
            'description' => 'Report download is not working.',
            'status' => 'resolved',
            'priority' => 'low',
            'summary_status' => 'completed',
            'ai_summary' => 'Customer reported an issue with downloading a report.',
        ]);
    }
}