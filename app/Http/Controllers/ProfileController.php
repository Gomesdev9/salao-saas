<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('profile/edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail(),
            'salon' => ['name' => $user->salon->name, 'pix_key' => $user->salon->pix_key],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Dados da empresa: nome e chave Pix (aparece na tela de receber pagamento).
     */
    public function updateSalon(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('salon', [
            'salon_name' => ['required', 'string', 'max:120'],
            'pix_key' => ['nullable', 'string', 'max:140'],
        ]);

        $request->user()->salon->update([
            'name' => $data['salon_name'],
            'pix_key' => $data['pix_key'] ?? null,
        ]);

        return Redirect::route('profile.edit')->with('status', 'Dados da empresa salvos.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
