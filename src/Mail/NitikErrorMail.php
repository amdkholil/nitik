<?php

namespace Kholil\Nitik\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Kholil\Nitik\Models\NitikError;

class NitikErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NitikError $error)
    {
    }

    public function build(): static
    {
        return $this->subject("[Nitik Error Alert] {$this->error->level}: {$this->error->exception_class}")
            ->html("
                <h2>Nitik Error Alert</h2>
                <p><strong>Level:</strong> {$this->error->level}</p>
                <p><strong>Exception:</strong> {$this->error->exception_class}</p>
                <p><strong>Message:</strong> {$this->error->message}</p>
                <p><strong>File:</strong> {$this->error->file}:{$this->error->line}</p>
                <p><strong>Count:</strong> {$this->error->count}</p>
                <hr>
                <pre style='background:#f4f4f4;padding:10px;font-size:12px;'>{$this->error->stack_trace}</pre>
            ");
    }
}
