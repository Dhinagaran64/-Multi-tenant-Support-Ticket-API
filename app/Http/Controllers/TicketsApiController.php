<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTicketSummary;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TicketsApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'page' => 'numeric|gt:0',
                'limit' => 'numeric|gt:0',
                'ids' => 'array',
                'search' => 'nullable'
            ], [
                'page.numeric' => 'The page must be a numeric value.',
                'page.gt' => 'The page must be greater than 0.',
                'limit.numeric' => 'The limit must be a numeric value.',
                'limit.gt' => 'The limit must be greater than 0.',
                'ids.array' => 'The ids must be an array.'
            ]);
        } catch (ValidationException $e) {
            return error_response($e);
        }

        $tenant = $request->attributes->get('tenant');

        $page = (int) ($validatedData['page'] ?? 1);
        $limit = (int) ($validatedData['limit'] ?? 10);

        $query = Ticket::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($validatedData) {
                    if (isset($validatedData['ids'])) {
                        $q->whereIn('id', $validatedData['ids']);
                    }
                    if (!empty($validatedData['search'])) {
                        $q->where('title', 'LIKE', '%' . $validatedData['search'] . '%');
                    }
                })
                ->orderBy('created_at', 'desc');

        $count = $query->count();
        $tickets = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        if (ceil($count / $limit) < $page) {
            return resp(204, false, 'Page not found.');
        }

        return resp(200, true, "Tickets retrieved successfully.", [
            'tickets' => $tickets,
            'count' => $count,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'assigned_to' => 'nullable|integer',
                'status' => 'nullable|string|max:50',
                'priority' => 'nullable|string|max:50',
            ], [
                'title.required' => 'The title is required.',
                'title.string' => 'The title must be a string.',
                'title.max' => 'The title may not be greater than 255 characters.',
                'description.string' => 'The description must be a string.',
                'assigned_to.integer' => 'The assigned user must be a valid ID.',
                'status.string' => 'The status must be a string.',
                'priority.string' => 'The priority must be a string.',
            ]);
        } catch (ValidationException $e) {
            return error_response($e);
        }

        $tenant = $request->attributes->get('tenant');

        if (array_key_exists('assigned_to', $validatedData) && !is_null($validatedData['assigned_to'])) {
            $assignedUser = User::where('tenant_id', $tenant->id)->find($validatedData['assigned_to']);

            if (!$assignedUser) {
                return resp(422, false, 'The assigned user does not exist.');
            }
        }

        $ticket = Ticket::create([
            'tenant_id' => $tenant->id,
            'created_by' => $request->user()->id,
            'assigned_to' => $validatedData['assigned_to'] ?? null,
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? null,
            'status' => $validatedData['status'] ?? 'open',
            'priority' => $validatedData['priority'] ?? 'medium',
        ]);

        GenerateTicketSummary::dispatch($tenant->id, $ticket->id);

        $ticket->load(['creator', 'assignee']);

        return resp(201, true, 'Ticket created successfully.',[
                'ticket' => $ticket,
            ]
        );
    }
    public function detail(Request $request, $id)
    {
        $tenant = $request->attributes->get('tenant');

        $ticket = Ticket::with(['creator', 'assignee'])->where('tenant_id', $tenant->id)->find($id);
        if (is_null($ticket)) {
            return resp(203, false, "Ticket not found.");
        }

        return resp(200, true, "Ticket retrieved successfully.", [
            'ticket' => $ticket,
        ]);
    }

    public function update(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer',
                'assigned_to' => 'nullable|integer',
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|string|max:50',
                'priority' => 'nullable|string|max:50',
            ], [
                'id.required' => 'The ID is required.',
                'id.integer' => 'The ID must be a valid integer.',
                'assigned_to.integer' => 'The assigned user must be a valid ID.',
                'title.required' => 'The title is required.',
                'title.string' => 'The title must be a string.',
                'title.max' => 'The title may not be greater than 255 characters.',
                'description.string' => 'The description must be a string.',
                'status.string' => 'The status must be a string.',
                'priority.string' => 'The priority must be a string.',
            ]);
        } catch (ValidationException $e) {
            return error_response($e);
        }

        $tenant = $request->attributes->get('tenant');

        $ticket = Ticket::where('tenant_id', $tenant->id)->find($validatedData['id']);
        if (!$ticket) {
            return resp(404, false, 'Ticket not found.');
        }

        if (array_key_exists('assigned_to', $validatedData) && !is_null($validatedData['assigned_to'])) {
            $assignedUser = User::where('tenant_id', $tenant->id)->find($validatedData['assigned_to']);

            if (!$assignedUser) {
                return resp(422, false, 'The assigned user does not exist.');
            }
        }

        $ticket->update($validatedData);
        $ticket->fresh();

        $ticket->load(['creator', 'assignee']);

        return resp(200, true, 'Ticket updated successfully.', [
                'ticket' => $ticket,
            ]
        );
    }

    public function action(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ids' => 'required|array',
                'action_flag' => 'required|string',
            ], [
                'ids.required' => 'The IDs field is required.',
                'ids.array' => 'The IDs field must be an array.',
                'action_flag.required' => 'The action flag is required.',
                'action_flag.string' => 'The action flag must be a string.'
            ]);
        } catch (ValidationException $e) {
            return error_response($e);
        }

        $tenant = $request->attributes->get('tenant');

        $tickets = Ticket::where('tenant_id', $tenant->id)->whereIn('id', $validatedData['ids'])->get();

        if ($validatedData['action_flag'] == 'delete') {
            $tickets->each(fn ($ticket) => $ticket->delete());
        }

        return resp(200, true, 'Action performed.');
    }

    public function assign(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ticket_id' => 'required|integer',
                'assigned_to' => 'required|integer'
            ], [
                'ticket_id.required' => 'The ticket_id is required.',
                'ticket_id.integer' => 'The ticket_id must be a integer.',
                'assigned_to.required' => 'The assigned_to is required.',
                'assigned_to.integer' => 'The assigned_to must be a integer.',
            ]);
        } catch (ValidationException $e) {
            return error_response($e);
        }

        $tenant = $request->attributes->get('tenant');

        $ticket = Ticket::where('tenant_id', $tenant->id)->find($validatedData['ticket_id']);
        if (is_null($ticket)) {
            return resp(422, false, 'The ticket does not exist.');
        }

        $assignedUser = User::where('tenant_id', $tenant->id)->find($validatedData['assigned_to']);
        if (is_null($assignedUser)) {
            return resp(422, false, 'The assigned user does not exist.');
        }

        $ticket->update(['assigned_to' => $validatedData['assigned_to']]);

        $ticket->load(['creator', 'assignee']);

        return resp(201, true, 'Ticket assigned successfully.',[
                'ticket' => $ticket,
            ]
        );
    }
}
