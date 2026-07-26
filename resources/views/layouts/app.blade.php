<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-palette="{{ auth()->user()?->account?->theme_palette ?? 'forest' }}"
      @if (auth()->user()?->theme_mode) data-theme="{{ auth()->user()->theme_mode }}" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-fluid-base text-ink antialiased bg-page">
        <div class="min-h-dvh">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-surface border-b border-line shadow">
                    <div class="max-w-7xl mx-auto py-fluid-sm px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            @auth
                <x-quick-add-fab />
            @endauth
        </div>
    </body>
</html>
