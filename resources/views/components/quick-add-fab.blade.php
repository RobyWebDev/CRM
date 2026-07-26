{{--
    Gyors-felvétel lebegő gomb (Rob saját AI-javaslata, crm_projekt.md 8. szekció) —
    terepen, telefonon egy kattintással felvehető egy új lead vagy kontakt, anélkül,
    hogy a jelenlegi oldalról el kellene navigálni a menüig. Minden bejelentkezett
    oldalon látszik (lásd layouts/app.blade.php).
--}}
<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50" @keydown.escape.window="open = false">
    <div x-show="open" x-transition @click.outside="open = false" class="mb-2 flex flex-col gap-2 items-end" style="display: none;">
        <a href="{{ route('leads.create') }}"
           class="bg-surface border border-line rounded-full px-4 py-2 shadow-lg text-ink text-fluid-base whitespace-nowrap hover:bg-surface-hover">
            {{ __('+ Új lead') }}
        </a>
        <a href="{{ route('contacts.create') }}"
           class="bg-surface border border-line rounded-full px-4 py-2 shadow-lg text-ink text-fluid-base whitespace-nowrap hover:bg-surface-hover">
            {{ __('+ Új kontakt') }}
        </a>
    </div>

    <button @click="open = !open" type="button" :aria-expanded="open.toString()"
            class="w-14 h-14 rounded-full bg-accent text-accent-ink shadow-lg flex items-center justify-center text-fluid-2xl leading-none focus:outline-none focus-visible:ring-2 focus-visible:ring-line-strong focus-visible:ring-offset-2 focus-visible:ring-offset-page"
            aria-label="{{ __('Gyors felvétel') }}">
        <span aria-hidden="true" x-text="open ? '×' : '+'"></span>
    </button>
</div>
