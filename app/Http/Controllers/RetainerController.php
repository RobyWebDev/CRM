<?php

namespace App\Http\Controllers;

use App\Models\Retainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetainerController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $retainers = Retainer::query()
            ->with('contact', 'serviceType')
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status), fn ($q) => $q->where('status', 'active'))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('retainers.index', ['retainers' => $retainers, 'status' => $status]);
    }

    public function show(Retainer $retainer): View
    {
        $retainer->load('contact', 'organization', 'serviceType', 'deal', 'tasks', 'invoices');

        return view('retainers.show', ['retainer' => $retainer]);
    }

    public function edit(Retainer $retainer): View
    {
        return view('retainers.edit', ['retainer' => $retainer]);
    }

    public function update(Request $request, Retainer $retainer): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,other'],
            'billing_day' => ['nullable', 'integer', 'min:1', 'max:28'],
            'status' => ['required', 'in:active,paused,ended'],
        ]);

        $retainer->update($data);

        if ($data['status'] === 'ended' && ! $retainer->ended_at) {
            $retainer->update(['ended_at' => now()->toDateString()]);
        }

        return redirect()->route('retainers.show', $retainer)->with('status', 'retainer-updated');
    }

    public function destroy(Retainer $retainer): RedirectResponse
    {
        $retainer->delete();

        return redirect()->route('retainers.index')->with('status', 'retainer-deleted');
    }
}
