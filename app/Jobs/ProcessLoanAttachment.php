<?php

namespace App\Jobs;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessLoanAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?string $traceId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Loan $loan,
        public string $tempPath,
        public string $originalName
    ) {
        $this->traceId = \App\Support\Tracing::getTraceId();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->traceId) {
            \App\Support\Tracing::setTraceId($this->traceId);
        }

        $span = \App\Support\Tracing::startSpan('job.process_attachment', "Processing attachment for loan #{$this->loan->loan_number}");

        if (! Storage::disk('local')->exists($this->tempPath)) {
            if ($span) {
                $span->finish();
            }

            return;
        }

        $extension = pathinfo($this->originalName, PATHINFO_EXTENSION);
        $filename = Str::random(40).'.'.$extension;
        $attachmentPath = 'loan-attachments/'.$filename;

        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        $stream = Storage::disk('local')->readStream($this->tempPath);
        Storage::disk($disk)->put($attachmentPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        // Update loan attachments
        $loan = \App\Models\Loan::withoutGlobalScopes()->find($this->loan->id);
        if ($loan) {
            $currentAttachments = $loan->attachments ?? [];
            $currentAttachments[] = $attachmentPath;
            $loan->forceFill(['attachments' => $currentAttachments])->saveQuietly();
        }

        // Cleanup temp file
        Storage::disk('local')->delete($this->tempPath);

        if ($span) {
            $span->finish();
        }
    }
}
