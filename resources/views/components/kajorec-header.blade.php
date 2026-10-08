@props(['variant' => 'public'])

@if($variant === 'app')

    {{-- APP / TEACHER DASHBOARD HEADER (Fixed Light Green & Non-overlapping) --}}
    <header class="w-full border-b border-emerald-200/80 bg-[#0b993e] shadow-sm">
        <div class="flex h-14 w-full items-center justify-between px-4 lg:px-6">

            {{-- LEFT: BRANDING --}}
            <div class="flex items-center gap-2.5">
                <img
                    src="{{ asset('images/BSUBC-logo.png') }}"
                    alt="Benguet State University Logo"
                    class="h-8 w-8 object-contain"
                >

                <div>
                    <h1 class="text-base font-extrabold leading-none tracking-wide text-[#176b3a]">
                        KAJOREC
                    </h1>

                    <p class="text-[10px] font-medium text-emerald-800">
                        Guidance Office Record Management System
                    </p>
                </div>
            </div>

            {{-- RIGHT: PROFILE & NOTIFICATIONS --}}
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="relative rounded-full p-1.5 text-sm text-emerald-800 transition hover:bg-emerald-200/60"
                >
                    🔔
                    <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-red-500"></span>
                </button>

                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#176b3a] text-xs font-bold text-white shadow-sm">
                    {{ strtoupper(substr(Auth::user()->name ?? 'TD', 0, 2)) }}
                </div>

                <div class="hidden sm:block">
                    <p class="text-xs font-semibold leading-tight text-gray-900">
                        {{ Auth::user()->name ?? 'Teacher' }}
                    </p>

                    <p class="text-[10px] leading-tight text-emerald-700">
                        {{ ucfirst(Auth::user()->role ?? 'Faculty') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="ml-1 inline">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-md border border-red-600 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-600 hover:text-white"
                    >
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </header>

@else

    {{-- PUBLIC WELCOME HEADER --}}
    <header class="sticky top-0 z-50 border-b border-gray-200 bg-white">
        <div class="mx-auto flex min-h-[100px] max-w-7xl items-center px-6 lg:px-8">

            <!-- LEFT: LOGO + BSU-BC -->
            <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3">
                <img
                    src="{{ asset('images/BSUBC-logo.png') }}"
                    alt="Benguet State University Logo"
                    style="width: 85px; height: 85px; object-fit: contain;"
                >

                <span class="whitespace-nowrap text-xl font-bold text-[#176b3a]">
                    BSU-BC
                </span>
            </a>

            <!-- RIGHT SIDE -->
            <div class="ml-auto flex items-center gap-10">

                <!-- NAVIGATION -->
                <nav class="flex items-center gap-8">
                    <a href="{{ url('/') }}" class="whitespace-nowrap text-sm font-medium text-gray-800 transition hover:text-[#176b3a]">
                        Home
                    </a>
                    <a href="#about" class="whitespace-nowrap text-sm font-medium text-gray-800 transition hover:text-[#176b3a]">
                        About
                    </a>
                    <a href="#contact" class="whitespace-nowrap text-sm font-medium text-gray-800 transition hover:text-[#176b3a]">
                        Contact
                    </a>
                </nav>

                <!-- CTA BUTTONS -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-lg border border-[#176b3a] bg-white px-5 py-2.5 text-sm font-semibold text-[#176b3a] transition hover:bg-[#176b3a] hover:text-white">
                        Login
                    </a>

                    <a href="{{ route('register') }}" class="rounded-lg bg-[#176b3a] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#12552e]">
                        Sign Up
                    </a>
                </div>

            </div>

        </div>
    </header>

@endif