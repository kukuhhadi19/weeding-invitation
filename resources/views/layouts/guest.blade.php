<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Wedding Invitation') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-white min-h-screen text-foreground antialiased selection:bg-primary/20 selection:text-primary">
        <div class="min-h-screen flex w-full">
            <!-- Left Side: Hero Image (Desktop only) -->
            <div class="hidden lg:flex lg:w-1/2 relative bg-muted items-center justify-center overflow-hidden">
                <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?q=80&w=2070&auto=format&fit=crop" 
                     alt="Wedding Couple" 
                     class="absolute inset-0 w-full h-full object-cover">
                
                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-black/10"></div>
                
                <!-- Overlay branding & quote -->
                <div class="absolute bottom-12 left-12 right-12 text-white">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-8 rounded-3xl max-w-lg shadow-2xl">
                        <svg class="size-8 text-white/70 mb-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>
                            <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>
                        </svg>
                        <h2 class="text-3xl font-bold mb-3 leading-tight tracking-tight">Create unforgettable digital experiences for your special day.</h2>
                        <p class="text-white/80 text-base leading-relaxed">Kelola undangan pernikahan digital dengan mudah, bagikan momen bahagia, dan kumpulkan doa dari orang tersayang.</p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Content Area -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-white relative">
                <!-- Decorative background blur -->
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="w-full max-w-[440px] flex flex-col z-10 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
