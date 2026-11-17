<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RenderLoungePhotosJob;
use App\Services\Render\RenderStudio;
use Illuminate\Http\RedirectResponse;

class LoungePhotoController extends Controller
{
    /**
     * Queues a fresh set of room and table pictures, for after the layout changed. The headless
     * browser loads the studio from APP_URL, never from an address taken from the request.
     */
    public function store(string $locale, RenderStudio $studio): RedirectResponse
    {
        abort_unless($studio->enabled(), 404);

        RenderLoungePhotosJob::dispatch((string) config('app.url'));

        return back()->with('status', __('admin-floors-photos-queued'));
    }
}
