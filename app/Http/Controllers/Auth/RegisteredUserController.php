<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'indisposable', 'unique:'.User::class],
            'birth_date' => ['required', 'date', 'before_or_equal:'.Carbon::now()->subYears(20)->format('Y-m-d')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
            'newsletter_agreed' => ['sometimes', 'accepted'],
        ], [
            'birth_date.required' => __('validation.birth_date_required'),
            'birth_date.before_or_equal' => __('validation.birth_date_age'),
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'birth_date' => $request->birth_date,
            'locale' => app()->getLocale(),
            'password' => Hash::make($request->password),
            'terms_agreed' => true,
            'terms_agreed_at' => now(),
            'newsletter_agreed' => $request->boolean('newsletter_agreed'),
            'newsletter_agreed_at' => $request->boolean('newsletter_agreed') ? now() : null,
        ]);

        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            Log::error('Email verification sending failed for user: '.$user->id, [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            Auth::login($user);

            return redirect(route('verification.notice'))
                ->with('warning', __('auth.email-verification-failed'));
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
