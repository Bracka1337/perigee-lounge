<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RenderLoungePhotosJob;
use App\Services\Render\RenderStudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoungePhotoController extends Controller
{
    /**
     * Queues a fresh set of room and table pictures, for after the layout changed.
     */
    public function store(Request $request, string $locale, RenderStudio $studio): RedirectResponse
    {
        abort_unless($studio->enabled(), 404);

        RenderLoungePhotosJob::dispatch($request->root());

        return back()->with('status', __('admin-floors-photos-queued'));
    }
}
