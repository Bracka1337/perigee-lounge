<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $user->sendEmailVerificationNotification();

            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            Log::error('Email verification resend failed for user: '.$user->id, [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('warning', __('auth.email-verification-failed'));
        }
    }
}
