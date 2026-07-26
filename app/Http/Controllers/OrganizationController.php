<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Szervezetek (cégek) — MINIMÁLIS felület, mert eddig csak a kontakt-űrlap
 * lenyíló listájából voltak elérhetők, önálló kezelőfelület nélkül. Most, hogy
 * a kontakt-űrlapról "+ Új szervezet..."-tel gyorsan létrehozhatók (Rob kérése,
 * 2026-07-26), szükség van egy helyre, ahol utólag meg is tekinthetők/
 * szerkeszthetők — különben kezelhetetlen, láthatatlan rekordok halmozódnának.
 * Nincs önálló create/store — a létrehozás a kontakt-űrlap gyors-felvételén
 * keresztül történik, itt csak a már létező szervezetek karbantartása zajlik.
 */
class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $organizations = Organization::query()
            ->withCount('contacts')
            ->when($request->string('q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('q')->trim().'%');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('organizations.index', ['organizations' => $organizations, 'q' => $request->string('q')]);
    }

    public function show(Organization $organization): View
    {
        $organization->load(['contacts' => fn ($query) => $query->orderBy('first_name')]);

        return view('organizations.show', ['organization' => $organization]);
    }

    public function edit(Organization $organization): View
    {
        return view('organizations.edit', ['organization' => $organization]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
        ]);

        $organization->update($data);

        return redirect()->route('organizations.show', $organization)->with('status', 'organization-updated');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->delete();

        return redirect()->route('organizations.index')->with('status', 'organization-deleted');
    }
}
