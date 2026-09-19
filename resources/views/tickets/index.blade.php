@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">

    <div>
        <h2 class="fw-bold mb-1">Support Tickets</h2>
        <p class="text-muted mb-0">
            Create and manage support requests for
            <strong>{{ auth()->user()->tenant->name }}</strong>
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#createTicketModal"
    >
        + Create Ticket
    </button>

</div>


{{-- Statistics --}}
<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Tickets</small>
                <h3 class="fw-bold mb-0" id="ticketCount">0</h3>
            </div>
        </div>
    </div>

</div>


{{-- Tickets --}}
<div class="card shadow-sm">

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>AI Summary</th>
                        <th width="220">Action</th>
                    </tr>
                </thead>

                <tbody id="ticketTableBody">

                    <tr>
                        <td colspan="7" class="text-center py-5">
                            Loading tickets...
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- Create Ticket Modal --}}
<div
    class="modal fade"
    id="createTicketModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create Ticket</h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <form id="createTicketForm">

                <div class="modal-body">

                    <div id="createTicketError"></div>

                    <div class="mb-3">

                        <label class="form-label">
                            Title
                        </label>

                        <input
                            type="text"
                            id="ticketTitle"
                            class="form-control"
                            maxlength="255"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            id="ticketDescription"
                            class="form-control"
                            rows="4"
                            required
                        ></textarea>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Priority
                        </label>

                        <select
                            id="ticketPriority"
                            class="form-select"
                        >
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="createTicketBtn"
                    >
                        Create Ticket
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

{{-- Edit Ticket Modal --}}
<div
    class="modal fade"
    id="editTicketModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Edit Ticket
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>

            <form id="editTicketForm">

                <div class="modal-body">

                    <div id="editTicketError"></div>

                    <input
                        type="hidden"
                        id="editTicketId"
                    >

                    <div class="mb-3">

                        <label class="form-label">
                            Title
                        </label>

                        <input
                            type="text"
                            id="editTicketTitle"
                            class="form-control"
                            maxlength="255"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            id="editTicketDescription"
                            class="form-control"
                            rows="4"
                            required
                        ></textarea>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            id="editTicketStatus"
                            class="form-select"
                        >
                            <option value="open">
                                Open
                            </option>

                            <option value="in_progress">
                                In Progress
                            </option>

                            <option value="resolved">
                                Resolved
                            </option>

                            <option value="closed">
                                Closed
                            </option>
                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Priority
                        </label>

                        <select
                            id="editTicketPriority"
                            class="form-select"
                        >
                            <option value="low">
                                Low
                            </option>

                            <option value="medium">
                                Medium
                            </option>

                            <option value="high">
                                High
                            </option>
                        </select>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="updateTicketBtn"
                    >
                        Update Ticket
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

{{-- View Ticket Modal --}}
<div
    class="modal fade"
    id="viewTicketModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="viewTicketTitle">
                    Ticket
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="mb-3">

                    <label class="text-muted small">
                        Description
                    </label>

                    <div
                        id="viewTicketDescription"
                        class="mt-1"
                    ></div>

                </div>


                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="text-muted small">
                            Status
                        </label>

                        <div>
                            <span
                                class="badge bg-secondary"
                                id="viewTicketStatus"
                            ></span>
                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <label class="text-muted small">
                            Priority
                        </label>

                        <div>
                            <span
                                class="badge bg-secondary"
                                id="viewTicketPriority"
                            ></span>
                        </div>

                    </div>


                    <div class="col-md-4 mb-3">

                        <label class="text-muted small">
                            Summary Status
                        </label>

                        <div>
                            <span
                                class="badge bg-secondary"
                                id="viewTicketSummaryStatus"
                            ></span>
                        </div>

                    </div>

                </div>


                <div class="border rounded p-3 bg-light">

                    <h6 class="fw-bold">
                        AI Summary
                    </h6>

                    <p
                        id="viewTicketSummary"
                        class="mb-0 text-muted"
                    >
                        Summary is being generated...
                    </p>

                </div>

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

    .table th {
        white-space: nowrap;
    }

    .ticket-description {
        max-width: 280px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

</style>

@endpush


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    console.log('Tickets page loaded.');

    /*
     * Load tickets immediately when the page is ready.
     */
    loadTickets();


    /*
     * Create ticket
     */
    document
        .getElementById('createTicketForm')
        .addEventListener('submit', createTicket);

    document
    .getElementById('editTicketForm')
    .addEventListener('submit', updateTicket);

});


/*
|--------------------------------------------------------------------------
| API Helper
|--------------------------------------------------------------------------
*/

async function getToken() {
    const cookie = await cookieStore.get('access_token');
    return cookie ? decodeURIComponent(cookie.value) : null;
}

async function apiRequest(url, options = {}) {
    const token = await getToken();

    if (!token) {
        window.location.href = '/login';
        return null;
    }

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        ...(options.headers || {})
    };

    const response = await fetch(url, {
        ...options,
        headers
    });

    // Handle unauthenticated API request
    if (response.status === 401) {
        localStorage.removeItem('api_token');
        localStorage.removeItem('user');

        window.location.href = '/login';
        return null;
    }

    // Try to read JSON response
    let result;

    try {
        result = await response.json();
    } catch (error) {
        throw new Error(`Request failed with status ${response.status}`);
    }

    // Handle any API error including 404, 422, 403, 500
    if (!response.ok) {
        throw new Error(
            result.message || `Request failed with status ${response.status}`
        );
    }

    return result;
}


/*
|--------------------------------------------------------------------------
| Load Tickets
|--------------------------------------------------------------------------
*/

async function loadTickets() {

    const tbody = document.getElementById('ticketTableBody');

    console.log('Loading tickets...');


    try {

        const result = await apiRequest('/api/tickets/list', {
            method: 'POST',
            body: JSON.stringify({})
        });


        /*
         * If apiRequest redirected because token was missing
         */
        if (!result) {
            return;
        }


        /*
         * Your API response:
         *
         * {
         *     code: 200,
         *     success: true,
         *     message: "...",
         *     data: {
         *         tickets: [...],
         *         count: 5
         *     }
         * }
         */

        const tickets = result.data?.tickets || [];

        const count = result.data?.count || 0;


        document.getElementById('ticketCount').innerText = count;


        if (tickets.length === 0) {

            tbody.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="text-center text-muted py-5"
                    >
                        No tickets found.
                    </td>
                </tr>
            `;

            return;
        }


        tbody.innerHTML = tickets.map(ticket => {

            return `
                <tr>

                    <td>
                        <strong>#${ticket.id}</strong>
                    </td>


                    <td>
                        <div class="fw-semibold">
                            ${escapeHtml(ticket.title)}
                        </div>
                    </td>


                    <td>
                        <div class="ticket-description">
                            ${escapeHtml(ticket.description)}
                        </div>
                    </td>


                    <td>
                        <span class="badge ${getStatusClass(ticket.status)}">
                            ${formatText(ticket.status)}
                        </span>
                    </td>


                    <td>
                        <span class="badge ${getPriorityClass(ticket.priority)}">
                            ${formatText(ticket.priority)}
                        </span>
                    </td>


                    <td>
                        <span class="badge ${getSummaryClass(ticket.summary_status)}">
                            ${formatText(ticket.summary_status)}
                        </span>
                    </td>


                    <td>
                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    onclick="viewTicket(${ticket.id})">
                                View
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    onclick="editTicket(${ticket.id})">
                                Edit
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="deleteTicket(${ticket.id})">
                                Delete
                            </button>
                        </div>
                    </td>

                </tr>
            `;

        }).join('');


    } catch (error) {

        console.error('Load tickets error:', error);

        tbody.innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="text-center text-danger py-5"
                >
                    ${escapeHtml(error.message)}
                </td>
            </tr>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| Create Ticket
|--------------------------------------------------------------------------
*/

async function createTicket(event) {

    event.preventDefault();


    const button = document.getElementById('createTicketBtn');

    const errorContainer =
        document.getElementById('createTicketError');


    errorContainer.innerHTML = '';

    button.disabled = true;
    button.innerText = 'Creating...';


    try {

        const result = await apiRequest('/api/tickets/create', {

            method: 'POST',

            body: JSON.stringify({

                title:
                    document.getElementById('ticketTitle').value,

                description:
                    document.getElementById('ticketDescription').value,

                priority:
                    document.getElementById('ticketPriority').value

            })

        });


        if (!result) {
            return;
        }


        /*
         * Close modal
         */
        const modalElement =
            document.getElementById('createTicketModal');

        const modal =
            bootstrap.Modal.getInstance(modalElement);

        modal.hide();


        /*
         * Reset form
         */
        document.getElementById('createTicketForm').reset();


        /*
         * Show success
         */
        showAlert(
            result.message || 'Ticket created successfully.',
            'success'
        );


        /*
         * Reload tickets
         */
        await loadTickets();


    } catch (error) {

        console.error('Create ticket error:', error);

        errorContainer.innerHTML = `
            <div class="alert alert-danger">
                ${escapeHtml(error.message)}
            </div>
        `;

    } finally {

        button.disabled = false;
        button.innerText = 'Create Ticket';

    }
}

async function editTicket(id) {

    try {

        const result =
            await apiRequest(`/api/tickets/detail/${id}`);

        if (!result) {
            return;
        }

        const ticket = result.data?.ticket;

        if (!ticket) {
            throw new Error('Ticket data not found.');
        }


        document.getElementById('editTicketId').value =
            ticket.id;

        document.getElementById('editTicketTitle').value =
            ticket.title || '';

        document.getElementById('editTicketDescription').value =
            ticket.description || '';

        document.getElementById('editTicketStatus').value =
            ticket.status || 'open';

        document.getElementById('editTicketPriority').value =
            ticket.priority || 'medium';


        document.getElementById('editTicketError').innerHTML = '';


        const modalElement =
            document.getElementById('editTicketModal');

        const modal =
            new bootstrap.Modal(modalElement);

        modal.show();

    } catch (error) {

        console.error('Edit ticket error:', error);

        showAlert(
            error.message,
            'danger'
        );
    }
}

async function updateTicket(event) {

    event.preventDefault();

    const button =
        document.getElementById('updateTicketBtn');

    const errorContainer =
        document.getElementById('editTicketError');

    errorContainer.innerHTML = '';

    button.disabled = true;
    button.innerText = 'Updating...';


    const ticketId =
        document.getElementById('editTicketId').value;


    try {

        const result = await apiRequest(
            `/api/tickets/update`,
            {
                method: 'POST',

                body: JSON.stringify({

                    id: ticketId,

                    title:
                        document.getElementById(
                            'editTicketTitle'
                        ).value,

                    description:
                        document.getElementById(
                            'editTicketDescription'
                        ).value,

                    status:
                        document.getElementById(
                            'editTicketStatus'
                        ).value,

                    priority:
                        document.getElementById(
                            'editTicketPriority'
                        ).value

                })
            }
        );


        if (!result) {
            return;
        }


        const modalElement =
            document.getElementById('editTicketModal');

        const modal =
            bootstrap.Modal.getInstance(modalElement);

        modal.hide();


        showAlert(
            result.message || 'Ticket updated successfully.',
            'success'
        );


        await loadTickets();


    } catch (error) {

        console.error(
            'Update ticket error:',
            error
        );

        errorContainer.innerHTML = `
            <div class="alert alert-danger">
                ${escapeHtml(error.message)}
            </div>
        `;

    } finally {

        button.disabled = false;
        button.innerText = 'Update Ticket';

    }
}


async function deleteTicket(id) {
    const confirmed = confirm(
        'Are you sure you want to delete this ticket?'
    );

    if (!confirmed) {
        return;
    }

    try {
        const result = await apiRequest('/api/tickets/action/update', {
            method: 'POST',
            body: JSON.stringify({
                ids: [id],
                action_flag: 'delete'
            })
        });

        if (!result) return;

        showAlert(
            result.message || 'Ticket deleted successfully.',
            'success'
        );

        await loadTickets();

    } catch (error) {
        console.error('Delete ticket error:', error);

        showAlert(
            error.message || 'Failed to delete ticket.',
            'danger'
        );
    }
}


/*
|--------------------------------------------------------------------------
| View Ticket
|--------------------------------------------------------------------------
*/

async function viewTicket(id) {

    try {

        const result =
            await apiRequest(`/api/tickets/detail/${id}`);


        if (!result) {
            return;
        }


        /*
         * Expected API:
         *
         * data: {
         *     ticket: {...}
         * }
         */

        const ticket =
            result.data?.ticket;


        if (!ticket) {

            throw new Error(
                'Ticket data not found.'
            );
        }


        document.getElementById('viewTicketTitle').innerText =
            ticket.title;


        document.getElementById('viewTicketDescription').innerText =
            ticket.description;


        document.getElementById('viewTicketStatus').innerText =
            formatText(ticket.status);

        document.getElementById('viewTicketStatus').className =
            `badge ${getStatusClass(ticket.status)}`;


        document.getElementById('viewTicketPriority').innerText =
            formatText(ticket.priority);

        document.getElementById('viewTicketPriority').className =
            `badge ${getPriorityClass(ticket.priority)}`;


        document.getElementById('viewTicketSummaryStatus').innerText =
            formatText(ticket.summary_status);

        document.getElementById('viewTicketSummaryStatus').className =
            `badge ${getSummaryClass(ticket.summary_status)}`;


        document.getElementById('viewTicketSummary').innerText =
            ticket.ai_summary ||
            'Summary is being generated...';


        const modalElement =
            document.getElementById('viewTicketModal');


        const modal =
            new bootstrap.Modal(modalElement);


        modal.show();


    } catch (error) {

        console.error('View ticket error:', error);

        showAlert(
            error.message,
            'danger'
        );

    }
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatText(value) {

    if (!value) {
        return '-';
    }

    return value
        .replaceAll('_', ' ')
        .replace(/\b\w/g, character =>
            character.toUpperCase()
        );
}


function getStatusClass(status) {

    switch (status) {

        case 'open':
            return 'bg-primary';

        case 'in_progress':
            return 'bg-warning text-dark';

        case 'resolved':
            return 'bg-success';

        case 'closed':
            return 'bg-secondary';

        default:
            return 'bg-secondary';
    }
}


function getPriorityClass(priority) {

    switch (priority) {

        case 'high':
            return 'bg-danger';

        case 'medium':
            return 'bg-warning text-dark';

        case 'low':
            return 'bg-success';

        default:
            return 'bg-secondary';
    }
}


function getSummaryClass(status) {

    switch (status) {

        case 'completed':
            return 'bg-success';

        case 'pending':
            return 'bg-warning text-dark';

        case 'failed':
            return 'bg-danger';

        default:
            return 'bg-secondary';
    }
}


function escapeHtml(value) {

    if (value === null || value === undefined) {
        return '';
    }

    const div = document.createElement('div');

    div.textContent = value;

    return div.innerHTML;
}


function showAlert(message, type = 'success') {

    const container =
        document.getElementById('alertContainer');

    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show">
            ${escapeHtml(message)}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    `;

}

</script>

@endpush