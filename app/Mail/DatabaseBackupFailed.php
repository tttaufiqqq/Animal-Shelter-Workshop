<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $manifest)
    {
    }

    public function build(): self
    {
        $degraded = ($this->manifest['status'] ?? null) === 'degraded';

        $subject = $degraded
            ? 'Database backup completed with orphaned references'
            : 'Database backup FAILED';

        return $this->subject($subject)
            ->view('emails.backup-alert')
            ->with(['degraded' => $degraded]);
    }
}
