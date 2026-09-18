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

        $tenantA = Tenant::updateOrCreate(
            [
                'name' => 'Acme Corporation',
            ],
            [
                'subscription_status' => 'active',
            ]
        );

        $userA = User::updateOrCreate(
            [
                'email' => 'john@acme.test',
            ],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantA->id,
            ]
        );

        $agentA = User::updateOrCreate(
            [
                'email' => 'agent@acme.test',
            ],
            [
                'name' => 'Support Agent A',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantA->id,
            ]
        );

        Ticket::updateOrCreate(
            [
                'tenant_id' => $tenantA->id,
                'title' => 'Unable to login',
            ],
            [
                'created_by' => $userA->id,
                'assigned_to' => $agentA->id,
                'description' => 'Customer is unable to login to the application.',
                'status' => 'open',
                'priority' => 'high',
                'summary_status' => 'pending',
                'ai_summary' => null,
            ]
        );

        Ticket::updateOrCreate(
            [
                'tenant_id' => $tenantA->id,
                'title' => 'Dashboard loading slowly',
            ],
            [
                'created_by' => $userA->id,
                'assigned_to' => null,
                'description' => 'Dashboard takes several seconds to load.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'summary_status' => 'pending',
                'ai_summary' => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Tenant B
        |--------------------------------------------------------------------------
        */

        $tenantB = Tenant::updateOrCreate(
            [
                'name' => 'Globex Industries',
            ],
            [
                'subscription_status' => 'active',
            ]
        );

        $userB = User::updateOrCreate(
            [
                'email' => 'jane@globex.test',
            ],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantB->id,
            ]
        );

        $agentB = User::updateOrCreate(
            [
                'email' => 'agent@globex.test',
            ],
            [
                'name' => 'Support Agent B',
                'password' => Hash::make('password'),
                'tenant_id' => $tenantB->id,
            ]
        );

        Ticket::updateOrCreate(
            [
                'tenant_id' => $tenantB->id,
                'title' => 'Payment issue',
            ],
            [
                'created_by' => $userB->id,
                'assigned_to' => $agentB->id,
                'description' => 'Customer is facing an issue with payment.',
                'status' => 'open',
                'priority' => 'high',
                'summary_status' => 'pending',
                'ai_summary' => null,
            ]
        );

        Ticket::updateOrCreate(
            [
                'tenant_id' => $tenantB->id,
                'title' => 'Unable to download report',
            ],
            [
                'created_by' => $userB->id,
                'assigned_to' => null,
                'description' => 'Report download is not working.',
                'status' => 'resolved',
                'priority' => 'low',
                'summary_status' => 'completed',
                'ai_summary' => 'Customer reported an issue with downloading a report.',
            ]
        );
    }
}