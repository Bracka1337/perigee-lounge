<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, #[CurrentUser] User $user): View
    {
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, #[CurrentUser] User $user): RedirectResponse
    {
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', __('client-profile-updated'));
    }

    /**
     * Delete the user's account.
     */
    //    public function destroy(Request $request): RedirectResponse
    //    {
    //        $request->validateWithBag('userDeletion', [
    //            'password' => ['required', 'current_password'],
    //        ]);
    //
    //        $user = $user;
    //
    //        Auth::logout();
    //
    //        $user->delete();
    //
    //        $request->session()->invalidate();
    //        $request->session()->regenerateToken();
    //
    //        return Redirect::to('/');
    //    }
}
