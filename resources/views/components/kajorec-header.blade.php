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

                <a
                    href="{{ url('/') }}"
                    class="whitespace-nowrap text-sm font-medium text-gray-800 transition hover:text-[#176b3a]"
                >
                    Home
                </a>

                <a
                    href="#about"
                    class="whitespace-nowrap text-sm font-medium text-gray-800 transition hover:text-[#176b3a]"
                >
                    About
                </a>

                <a
                    href="#contact"
                    class="whitespace-nowrap text-sm font-medium text-gray-800 transition hover:text-[#176b3a]"
                >
                    Contact
                </a>

            </nav>


            <!-- CTA BUTTONS -->
            <div class="flex items-center gap-3">

                @auth

                    <!-- DASHBOARD -->
                    <a
                        href="{{ url('/dashboard') }}"
                        class="rounded-lg bg-[#176b3a] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#12552e]"
                    >
                        Dashboard
                    </a>

                @else

                    <!-- LOGIN -->
                    <a
                        href="{{ route('login') }}"
                        class="rounded-lg border border-[#176b3a] bg-white px-5 py-2.5 text-sm font-semibold text-[#176b3a] transition hover:bg-[#176b3a] hover:text-white"
                    >
                        Login
                    </a>

                    <!-- SIGN UP -->
                    <a
                        href="{{ route('register') }}"
                        class="rounded-lg bg-[#176b3a] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#12552e]"
                    >
                        Sign Up
                    </a>

                @endauth

            </div>

        </div>

    </div>
</header>