<footer class="border-t border-gray-200 bg-white">

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">

        <div class="grid gap-8 md:grid-cols-3">

            <!-- BRAND -->
            <div>

                <h2 class="text-xl font-extrabold tracking-wide text-[#176b3a]">
                    KAJOREC
                </h2>

                <p class="mt-3 max-w-sm text-sm leading-6 text-gray-500">
                    Guidance Office Record Management and Appointment System
                    for Benguet State University – Bokod Campus.
                </p>

            </div>


            <!-- QUICK LINKS -->
            <div>

                <h3 class="text-sm font-semibold text-gray-900">
                    Quick Links
                </h3>

                <ul class="mt-4 space-y-3 text-sm">

                    <li>
                        <a
                            href="{{ url('/') }}"
                            class="text-gray-500 transition hover:text-[#176b3a]"
                        >
                            Home
                        </a>
                    </li>

                    <li>
                        <a
                            href="#about"
                            class="text-gray-500 transition hover:text-[#176b3a]"
                        >
                            About
                        </a>
                    </li>

                    <li>
                        <a
                            href="#contact"
                            class="text-gray-500 transition hover:text-[#176b3a]"
                        >
                            Contact
                        </a>
                    </li>

                </ul>

            </div>


            <!-- CONTACT -->
            <div id="contact">

                <h3 class="text-sm font-semibold text-gray-900">
                    Guidance Office
                </h3>

                <div class="mt-4 space-y-3 text-sm text-gray-500">

                    <p>
                        Benguet State University – Bokod Campus
                    </p>

                    <p>
                        College of Applied Technology
                    </p>

                </div>

            </div>

        </div>


        <!-- BOTTOM -->
        <div class="mt-10 flex flex-col gap-3 border-t border-gray-100 pt-6 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between">

            <p>
                © {{ date('Y') }} KAJOREC. All rights reserved.
            </p>

            <p>
                Benguet State University – Bokod Campus
            </p>

        </div>

    </div>

</footer>
