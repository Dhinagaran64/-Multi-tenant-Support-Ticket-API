<?php

namespace App\Services;

use App\Models\Ticket;

class AiSummarizationService
{
    public function summarize(Ticket $ticket): string
    {
        sleep(3);

        return sprintf(
            'Customer reported an issue regarding "%s". Priority is %s.',
            $ticket->title,
            $ticket->priority
        );
    }
}