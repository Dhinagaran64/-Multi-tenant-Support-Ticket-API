<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\AiSummarizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateTicketSummary implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $tenantId,
        public int $ticketId
    ) {
    }

    public function handle(AiSummarizationService $aiService): void 
    {
        $ticket = Ticket::where('tenant_id', $this->tenantId)->find($this->ticketId);

        if (!$ticket) {
            return;
        }

        try {
            $summary = $aiService->summarize($ticket);

            $ticket->update([
                'ai_summary' => $summary,
                'summary_status' => 'completed',
            ]);
        } catch (\Throwable $exception) {
            $ticket->update([
                'summary_status' => 'failed',
            ]);

            throw $exception;
        }
    }
}