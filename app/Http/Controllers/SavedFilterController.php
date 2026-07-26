<?php

namespace App\Http\Controllers;

use App\Models\SavedFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Mentett szűrők/nézetek (Rob saját AI-javaslata, crm_projekt.md 8. szekció) —
 * egy adott listaoldal (kontaktok, leadek) szűrés-kombinációja névvel elmenthető,
 * hogy egy kattintással újra alkalmazható legyen. Csak a szerzőjéhez tartozik.
 */
class SavedFilterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'resource' => ['required', 'in:contacts,leads'],
            'name' => ['required', 'string', 'max:100'],
            'query_string' => ['nullable', 'string', 'max:2000'],
        ]);

        SavedFilter::create($data + ['user_id' => $request->user()->id]);

        return back()->with('status', 'saved-filter-created');
    }

    public function destroy(Request $request, SavedFilter $savedFilter): RedirectResponse
    {
        if ($savedFilter->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('Ez nem a te mentett szűrőd.');
        }

        $savedFilter->delete();

        return back()->with('status', 'saved-filter-deleted');
    }
}
