<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A paletta (account-szintű márka/vizuális identitás) és a sötét/világos mód
 * (user-szintű preferencia) beállítása — lásd docs/szinvilag-terv.md.
 */
class ThemeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme_palette' => ['required', 'in:forest,salesforce'],
            'theme_mode' => ['nullable', 'in:dark,light'],
        ]);

        $user = $request->user();
        $user->account->update(['theme_palette' => $data['theme_palette']]);
        $user->update(['theme_mode' => $data['theme_mode'] ?? null]);

        return back()->with('status', 'theme-updated');
    }
}
