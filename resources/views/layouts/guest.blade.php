<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">

    <title>{{ $title ?? 'Bantuan' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,500;1,500&family=Noto+Color+Emoji&family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Open+Sans:ital,wght@0,500;1,500&family=Quicksand&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Google reCAPTCHA v3 --}}
    {!! RecaptchaV3::initJs() !!}

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* ── Hide reCAPTCHA badge ── */
        .grecaptcha-badge {
            visibility: hidden !important;
        }

        .input-purple:focus {
            border-color: #7c3aed !important;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .18);
            outline: none;
        }

        .input-red:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .18);
            outline: none;
        }
    </style>
</head>

<body class="min-h-screen font-sans antialiased transition-colors duration-300 scrollbar-thin overscroll-none">

    <div class="relative min-h-screen overflow-hidden bg-gray-50 dark:bg-gray-950">
        {{-- Background blobs --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-indigo-300/30 dark:bg-indigo-600/15 blur-3xl">
            </div>
            <div
                class="absolute -bottom-40 -left-32 w-[28rem] h-[28rem] rounded-full bg-purple-300/30 dark:bg-purple-600/15 blur-3xl">
            </div>
            <div
                class="absolute top-1/3 right-1/4 w-72 h-72 rounded-full bg-fuchsia-200/20 dark:bg-fuchsia-600/10 blur-3xl">
            </div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 pt-5 pb-12 sm:px-6 lg:pt-10">
            @isset($aside)
                <div class="grid gap-8 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:items-start lg:gap-12">
                    <aside class="lg:sticky lg:top-8">
                        <div class="mb-6 flex items-center justify-between lg:mb-8 lg:justify-start lg:gap-4">
                            <a href="/" class="flex items-center gap-2.5">
                                <span class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                                    Bale
                                </span>
                            </a>
                            <div class="lg:hidden">
                                <x-core::dark-mode-toggle />
                            </div>
                        </div>
                        {{ $aside }}
                    </aside>

                    <main class="w-full min-w-0">
                        {{ $slot }}
                    </main>
                </div>
            @else
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8 flex items-center justify-between">
                        <a href="/" class="flex items-center gap-2.5">
                            <span class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                                Bale
                            </span>
                        </a>
                        <x-core::dark-mode-toggle />
                    </div>
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            @endisset
        </div>

        {{-- Footer --}}
        <footer class="relative z-10 mx-auto w-full max-w-6xl px-4 pb-6 sm:px-6 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-600">
                &copy; {{ date('Y') }} Bale System
            </p>
        </footer>
    </div>

    @livewireScripts

    <x-core::toast />
</body>

</html>