<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-6 py-16">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.2),_transparent_40%),radial-gradient(circle_at_bottom,_rgba(14,165,233,0.16),_transparent_45%)]"></div>

            <section class="w-full max-w-3xl rounded-3xl border border-white/15 bg-white/5 p-8 shadow-2xl shadow-black/30 backdrop-blur md:p-12">
                <div class="mb-8 flex items-center justify-between gap-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-300">Welcome</p>
                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-zinc-900 transition hover:bg-amber-300">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg border border-zinc-400/70 px-4 py-2 text-sm font-semibold text-zinc-100 transition hover:border-zinc-200 hover:text-white">
                                Log in
                            </a>

                            @if ($canRegister)
                                <a href="{{ route('register') }}" class="rounded-lg bg-sky-400 px-4 py-2 text-sm font-semibold text-zinc-900 transition hover:bg-sky-300">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <h1 class="text-balance text-4xl font-bold leading-tight md:text-5xl">
                    {{ config('app.name', 'Laravel') }} starter kit with a Blade first landing page.
                </h1>
                <p class="mt-5 max-w-2xl text-pretty text-zinc-300 md:text-lg">
                    This route now renders a Blade view directly. Authentication still works the same, and you can continue using Inertia for the rest of the app.
                </p>
            </section>
        </main>
    </body>
</html>
