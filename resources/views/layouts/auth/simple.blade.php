<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface text-ink antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-4 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-2" wire:navigate>
                    <span class="flex size-10 items-center justify-center rounded-xl bg-primary text-white">
                        <x-app-logo-icon class="size-6" />
                    </span>
                    <span class="text-xl font-bold text-primary">{{ config('app.name', 'SIGALON') }}</span>
                </a>
                <div class="flex flex-col gap-6 rounded-2xl bg-white p-6 shadow-card md:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
