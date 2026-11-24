<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-[#0B0E0C]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0B0E0C">
        <meta name="color-scheme" content="dark">

        <title inertia>{{ config('app.name', 'StudioKode') }}</title>

        <!-- Google Fonts: Instrument Serif (Editorial Display Serif) & Plus Jakarta Sans (Geometric Sans) -->
        <!-- Legacy Fallback Preservation: Cormorant Garamond -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap">
        </noscript>

        <!-- Scripts & Styles -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] selection:text-[#F4F6F0] min-h-screen flex flex-col">
        <!-- StudioKode Architectural Palette: Jet (#0D0D0D), Forest (#224F10), Lime (#B0E454), Ivory (#EAFFD0) -->
        <script data-page="app" type="application/json">{!! json_encode($page, JSON_HEX_TAG) !!}</script>
        <div id="app" data-page="{{ json_encode($page) }}">
            <!-- Instant Branded Loading Shell for StudioKode -->
            <div id="app-skeleton" class="min-h-screen flex flex-col items-center justify-center bg-[#0B0E0C] text-[#F4F2ED] px-6 select-none relative overflow-hidden" style="background-color: #0B0E0C; color: #F4F2ED;">
                <!-- Ambient Topographic Glow Background -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-40">
                    <div class="w-[450px] h-[450px] rounded-full bg-gradient-to-tr from-[#142018]/60 via-[#224F10]/25 to-transparent blur-3xl"></div>
                </div>

                <div class="relative z-10 max-w-xl w-full flex flex-col items-center text-center space-y-6">
                    <!-- StudioKode Official Logo Brand Mark -->
                    <div class="relative flex items-center justify-center p-3 rounded-2xl bg-[#142018]/70 border border-[#23382B]/80 shadow-[0_0_35px_rgba(34,79,16,0.3)] mb-1" style="background-color: #142018; border: 1px solid #23382B;">
                        <picture>
                            <source srcset="/assets/logo/kode-logo.webp" type="image/webp">
                            <img src="/assets/logo/kode-logo.png" alt="StudioKode" class="h-12 sm:h-14 w-auto object-contain animate-pulse drop-shadow-[0_0_15px_rgba(176,228,84,0.3)]" width="1024" height="409">
                        </picture>
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
                        <div class="h-1.5 bg-[#16221A] rounded-full w-4/5 mx-auto overflow-hidden" style="height: 0.375rem; background-color: #16221A; border-radius: 9999px; width: 80%; margin: 0 auto;">
                            <div class="h-full bg-gradient-to-r from-[#224F10] via-[#88B896] to-[#B0E454] w-2/3 animate-pulse rounded-full"></div>
                        </div>
                        <div class="text-[10px] tracking-[0.2em] font-mono text-[#88B896]/70 uppercase">
                            Calibrating Living Topography
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <div class="h-8 w-24 bg-[#1C3024]/60 border border-[#23382B]/40 rounded-lg animate-pulse" style="height: 2rem; width: 6rem; background-color: #1C3024; border-radius: 0.5rem;"></div>
                        <div class="h-8 w-24 bg-[#16221A]/60 border border-[#23382B]/30 rounded-lg animate-pulse" style="height: 2rem; width: 6rem; background-color: #16221A; border-radius: 0.5rem;"></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
