<?php

namespace App\Jobs;

use App\Actions\Render\RenderLoungePhotos;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Regenerates every room and table picture after the layout changed. It runs on the queue
 * because rendering takes a while and the headless browser has to load pages from this app.
 */
class RenderLoungePhotosJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public string $rootUrl) {}

    public function handle(RenderLoungePhotos $render): void
    {
        $render->handle($this->rootUrl);
    }

    /**
     * The admin already left the page, so the reason goes to the log.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Regenerating the lounge photos failed: '.$exception->getMessage(), ['root_url' => $this->rootUrl]);
    }
}
