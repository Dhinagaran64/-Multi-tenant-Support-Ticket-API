@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>
            <p class="text-primary fw-semibold mb-1">
                {{ auth()->user()->tenant->name }}
            </p>

            <h2 class="fw-bold mb-1">
                Welcome, {{ auth()->user()->name }}
            </h2>

            <p class="text-muted mb-0">
                Manage your support tickets and monitor your team's requests.
            </p>
        </div>

        <div>
            <a href="{{ route('tickets.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Manage Tickets
            </a>
        </div>

    </div>
</div>


{{-- Tenant Information --}}
<div class="row g-4 mb-4">

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>
                        <p class="text-muted mb-2">
                            Organization
                        </p>

                        <h4 class="fw-bold mb-0">
                            {{ auth()->user()->tenant->name }}
                        </h4>
                    </div>

                    <div class="dashboard-icon bg-primary-subtle text-primary">
                        🏢
                    </div>

                </div>

            </div>
        </div>
    </div>


    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>
                        <p class="text-muted mb-2">
                            Subscription
                        </p>

                        <h4 class="fw-bold mb-0 text-capitalize">
                            {{ auth()->user()->tenant->subscription_status }}
                        </h4>
                    </div>

                    <div class="dashboard-icon bg-success-subtle text-success">
                        ✓
                    </div>

                </div>

                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success">
                        Active
                    </span>
                </div>

            </div>
        </div>
    </div>


    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start">

                    <div>
                        <p class="text-muted mb-2">
                            Account
                        </p>

                        <h5 class="fw-bold mb-0">
                            {{ auth()->user()->name }}
                        </h5>

                        <small class="text-muted">
                            {{ auth()->user()->email }}
                        </small>
                    </div>

                    <div class="dashboard-icon bg-info-subtle text-info">
                        👤
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>


{{-- Quick Actions --}}
<div class="card shadow-sm mb-4">

    <div class="card-body p-4">

        <h5 class="fw-bold mb-1">
            Quick Actions
        </h5>

        <p class="text-muted mb-4">
            Quickly access the main support features.
        </p>

        <div class="row g-3">

            <div class="col-md-6">

                <a
                    href="{{ route('tickets.index') }}"
                    class="quick-action text-decoration-none"
                >

                    <div class="quick-action-icon bg-primary-subtle text-primary">
                        🎫
                    </div>

                    <div>
                        <h6 class="fw-bold mb-1">
                            Support Tickets
                        </h6>

                        <p class="text-muted mb-0 small">
                            Create, view and manage support tickets.
                        </p>
                    </div>

                    <div class="ms-auto text-muted">
                        →
                    </div>

                </a>

            </div>


            <div class="col-md-6">

                <div class="quick-action">

                    <div class="quick-action-icon bg-warning-subtle text-warning">
                        🤖
                    </div>

                    <div>
                        <h6 class="fw-bold mb-1">
                            AI Ticket Summaries
                        </h6>

                        <p class="text-muted mb-0 small">
                            AI summaries are generated automatically
                            after ticket creation.
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- Architecture Info --}}
<div class="card shadow-sm">

    <div class="card-body p-4">

        <div class="d-flex align-items-start gap-3">

            <div class="dashboard-icon bg-dark text-white">
                ⚡
            </div>

            <div>

                <h5 class="fw-bold">
                    Multi-Tenant Support System
                </h5>

                <p class="text-muted mb-2">
                    You are currently accessing the
                    <strong>{{ auth()->user()->tenant->name }}</strong>
                    tenant.
                </p>

                <small class="text-muted">
                    Your tickets and tenant data are isolated from other
                    organizations.
                </small>

            </div>

        </div>

    </div>

</div>

@endsection


@push('styles')
<style>

    .card {
        border: none;
        border-radius: 14px;
    }

    .dashboard-icon {
        width: 46px;
        height: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 12px;

        font-size: 20px;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 15px;

        padding: 18px;

        border: 1px solid #e9ecef;
        border-radius: 12px;

        color: inherit;

        transition: all 0.2s ease;
    }

    .quick-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .quick-action-icon {
        width: 44px;
        height: 44px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 10px;

        font-size: 19px;
    }

</style>
@endpush