<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Retainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
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
     * A fiók törlése — Rob kérésére (2026-07-25) csak owner/admin szerepkörnek engedélyezett,
     * sima "member" usernek nem (a Blade-nézet is elrejti a gombot, ez a szerver-oldali védelem).
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (! in_array($request->user()->role, ['owner', 'admin'], true)) {
            throw new AccessDeniedHttpException('Csak owner/admin törölheti a fiókot.');
        }

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

    /**
     * Adatmentés/export — GDPR best practice (lásd gdpr-terv.md): a fiók törlése előtt
     * automatikusan felajánlott, letölthető JSON export a legfontosabb adatokról.
     * Csak owner/admin férhet hozzá, ugyanúgy, mint a törléshez.
     */
    public function export(Request $request): JsonResponse
    {
        if (! in_array($request->user()->role, ['owner', 'admin'], true)) {
            throw new AccessDeniedHttpException('Csak owner/admin exportálhatja a fiók adatait.');
        }

        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => $request->user()->account->only(['name', 'slug', 'locale', 'timezone']),
            'organizations' => Organization::all(),
            'contacts' => Contact::all(),
            'leads' => Lead::all(),
            'deals' => Deal::all(),
            'projects' => Project::all(),
            'retainers' => Retainer::all(),
        ];

        $filename = 'crm-export-'.now()->format('Y-m-d').'.json';

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
