<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KAJOREC</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .hero-bg {
            background: linear-gradient(135deg, #0f766e, #166534);
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Navbar -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <!-- Logo / KAJOREC -->
            <div class="flex items-center space-x-3">

                <img
                    src="{{ asset('images/kajorec_logo.png') }}"
                    class="w-10 h-10 object-contain"
                    alt="KAJOREC Logo"
                >

                <div>
                    <h1 class="font-bold text-xl text-green-800">
                        KAJOREC
                    </h1>

                    <p class="text-xs text-gray-500">
                        Guidance Office System
                    </p>
                </div>

            </div>

            <!-- Login -->
            <div>
                <a
                    href="/login"
                    class="bg-green-700 text-white px-6 py-2.5 rounded-lg
                           hover:bg-green-800 transition duration-200
                           font-medium"
                >
                    Login
                </a>
            </div>

        </div>
    </nav>


    <!-- Welcome / Hero -->
    <section class="hero-bg min-h-[calc(100vh-81px)] text-white">

        <div class="max-w-7xl mx-auto px-6 py-20">

            <div class="grid md:grid-cols-2 gap-10 items-center min-h-[70vh]">

                <!-- Welcome Content -->
                <div>

                    <p class="text-green-200 text-lg font-medium mb-3">
                        Welcome to
                    </p>

                    <h1 class="text-5xl md:text-6xl font-bold leading-tight">
                        <span class="text-yellow-300">
                            KAJOREC
                        </span>
                    </h1>

                    <h2 class="mt-4 text-2xl md:text-3xl font-semibold">
                        Guidance Office Record Management
                        and Appointment System
                    </h2>

                    <p class="mt-6 text-lg text-gray-100 leading-8 max-w-xl">
                        A web-based system designed for the
                        Benguet State University – Bokod Campus
                        Guidance Office to manage student records
                        and appointments efficiently and securely.
                    </p>

                    <!-- Login Button -->
                    <div class="mt-8">

                        <a
                            href="/login"
                            class="inline-block bg-yellow-400 text-gray-900
                                   font-semibold px-8 py-3.5 rounded-lg
                                   hover:bg-yellow-300
                                   transition duration-200
                                   shadow-lg"
                        >
                            Login to KAJOREC
                        </a>

                    </div>

                </div>


                <!-- Hero Image -->
                <div class="flex justify-center">

                    <img
                        src="{{ asset('images/hero.png') }}"
                        class="w-full max-w-lg"
                        alt="KAJOREC Guidance Office"
                    >

                </div>

            </div>

        </div>

    </section>

</body>
</html>