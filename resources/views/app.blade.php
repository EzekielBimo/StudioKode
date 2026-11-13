<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-[#0B0E0C]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0B0E0C">
        <meta name="color-scheme" content="dark">

        <title inertia>{{ config('app.name', 'StudioKode') }}</title>

        <!-- Google Fonts: Cormorant Garamond (Luxury Display Serif) & Plus Jakarta Sans (Geometric Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap">
        </noscript>

        <!-- Scripts & Styles -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] selection:text-[#F4F6F0] min-h-screen flex flex-col">
        <script data-page="app" type="application/json">{!! json_encode($page, JSON_HEX_TAG) !!}</script>
        <div id="app" data-page="{{ json_encode($page) }}">
            <!-- Instant Branded Loading Shell for StudioKode -->
            <div id="app-skeleton" class="min-h-screen flex flex-col items-center justify-center bg-[#0B0E0C] text-[#F4F2ED] px-6 select-none" style="background-color: #0B0E0C; color: #F4F2ED;">
                <div class="relative z-10 max-w-xl w-full flex flex-col items-center text-center space-y-6">
                    <!-- StudioKode Logo Brand Mark -->
                    <div class="w-16 h-16 rounded-2xl bg-[#142018] border border-[#23382B] flex items-center justify-center shadow-2xl mb-1" style="background-color: #142018; border: 1px solid #23382B;">
                        <svg class="w-8 h-8 text-[#88B896] animate-pulse" style="color: #88B896;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                    </div>

                    <div class="space-y-1.5">
                        <div class="text-xs uppercase tracking-[0.25em] text-[#88B896] font-medium" style="color: #88B896; letter-spacing: 0.25em;">
                            StudioKode
                        </div>
                        <div class="text-[11px] tracking-[0.18em] text-[#A8B0A6]/70 uppercase font-light" style="letter-spacing: 0.18em;">
                            Landscape Architecture
                        </div>
                    </div>

                    <!-- Subtle pulse placeholders matching dark theme -->
                    <div class="w-full max-w-sm pt-4 space-y-3">
                        <div class="h-6 bg-[#16221A] rounded-full w-4/5 mx-auto animate-pulse" style="height: 1.5rem; background-color: #16221A; border-radius: 9999px; width: 80%; margin: 0 auto;"></div>
                        <div class="h-3.5 bg-[#142018] rounded-full w-3/5 mx-auto animate-pulse opacity-75" style="height: 0.875rem; background-color: #142018; border-radius: 9999px; width: 60%; margin: 0 auto;"></div>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-3">
                        <div class="h-9 w-28 bg-[#1C3024] rounded-lg animate-pulse" style="height: 2.25rem; width: 7rem; background-color: #1C3024; border-radius: 0.5rem;"></div>
                        <div class="h-9 w-28 bg-[#16221A] rounded-lg animate-pulse" style="height: 2.25rem; width: 7rem; background-color: #16221A; border-radius: 0.5rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
