<x-layouts::app :title="__('Dashboard')">

    <div class="flex h-full w-full flex-1 flex-col gap-6">

        <!-- Welcome Header -->
        <div class="rounded-2xl bg-gradient-to-r from-green-950 via-green-800 to-green-600 p-8 text-white shadow-lg">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                <div>
                    <p class="text-green-200 text-sm font-medium">
                        Guidance Office
                    </p>

                    <h1 class="mt-1 text-3xl font-bold">
                        Welcome to KAJOREC
                    </h1>

                    <p class="mt-2 text-green-100">
                        Guidance Office Record Management and Appointment System
                    </p>
                </div>

                <div class="text-left md:text-right">
                    <p class="text-sm text-green-200">
                        {{ now()->format('l') }}
                    </p>

                    <p class="text-xl font-semibold">
                        {{ now()->format('F d, Y') }}
                    </p>
                </div>

            </div>

        </div>


        <!-- Statistics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <!-- Students -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Total Students
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            0
                        </h2>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-2xl">
                        👨‍🎓
                    </div>

                </div>

                <p class="mt-4 text-xs text-neutral-500">
                    Registered student records
                </p>

            </div>


            <!-- Appointments -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Today's Appointments
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            0
                        </h2>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">
                        📅
                    </div>

                </div>

                <p class="mt-4 text-xs text-neutral-500">
                    Scheduled for today
                </p>

            </div>


            <!-- Pending -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Pending Appointments
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            0
                        </h2>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-100 text-2xl">
                        ⏳
                    </div>

                </div>

                <p class="mt-4 text-xs text-neutral-500">
                    Waiting for approval
                </p>

            </div>


            <!-- Referrals -->
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Active Referrals
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            0
                        </h2>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-2xl">
                        🔄
                    </div>

                </div>

                <p class="mt-4 text-xs text-neutral-500">
                    Current student referrals
                </p>

            </div>

        </div>


        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Upcoming Appointments -->
            <div class="lg:col-span-2 rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-5 dark:border-neutral-700">

                    <div>
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            Upcoming Appointments
                        </h2>

                        <p class="text-sm text-neutral-500">
                            Latest scheduled appointments
                        </p>
                    </div>

                    <a href="#" class="text-sm font-medium text-green-700 hover:text-green-800">
                        View All
                    </a>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-left text-sm">

                        <thead class="bg-neutral-50 dark:bg-neutral-800">

                            <tr>
                                <th class="px-6 py-3 font-medium text-neutral-500">
                                    Student
                                </th>

                                <th class="px-6 py-3 font-medium text-neutral-500">
                                    Date
                                </th>

                                <th class="px-6 py-3 font-medium text-neutral-500">
                                    Time
                                </th>

                                <th class="px-6 py-3 font-medium text-neutral-500">
                                    Status
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-neutral-500">
                                    No upcoming appointments.
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- Quick Actions -->
            <div class="rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="border-b border-neutral-200 px-6 py-5 dark:border-neutral-700">

                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                        Quick Actions
                    </h2>

                    <p class="text-sm text-neutral-500">
                        Common Guidance Office tasks
                    </p>

                </div>


                <div class="space-y-3 p-6">

                    <a href="#"
                       class="flex items-center gap-4 rounded-xl border border-neutral-200 p-4 transition hover:border-green-500 hover:bg-green-50 dark:border-neutral-700 dark:hover:bg-green-950">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                            👨‍🎓
                        </div>

                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">
                                Add Student
                            </p>

                            <p class="text-xs text-neutral-500">
                                Create a student record
                            </p>
                        </div>

                    </a>


                    <a href="#"
                       class="flex items-center gap-4 rounded-xl border border-neutral-200 p-4 transition hover:border-blue-500 hover:bg-blue-50 dark:border-neutral-700 dark:hover:bg-blue-950">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            📅
                        </div>

                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">
                                New Appointment
                            </p>

                            <p class="text-xs text-neutral-500">
                                Schedule an appointment
                            </p>
                        </div>

                    </a>


                    <a href="#"
                       class="flex items-center gap-4 rounded-xl border border-neutral-200 p-4 transition hover:border-purple-500 hover:bg-purple-50 dark:border-neutral-700 dark:hover:bg-purple-950">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                            🔄
                        </div>

                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">
                                Add Referral
                            </p>

                            <p class="text-xs text-neutral-500">
                                Create a student referral
                            </p>
                        </div>

                    </a>


                    <a href="#"
                       class="flex items-center gap-4 rounded-xl border border-neutral-200 p-4 transition hover:border-yellow-500 hover:bg-yellow-50 dark:border-neutral-700 dark:hover:bg-yellow-950">

                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100">
                            📊
                        </div>

                        <div>
                            <p class="font-medium text-neutral-900 dark:text-white">
                                Generate Report
                            </p>

                            <p class="text-xs text-neutral-500">
                                View guidance reports
                            </p>
                        </div>

                    </a>

                </div>

            </div>

        </div>


        <!-- Recent Activity -->
        <div class="rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="border-b border-neutral-200 px-6 py-5 dark:border-neutral-700">

                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                    Recent Activity
                </h2>

                <p class="text-sm text-neutral-500">
                    Recent actions performed in KAJOREC
                </p>

            </div>

            <div class="px-6 py-10 text-center text-sm text-neutral-500">
                No recent activity.
            </div>

        </div>

    </div>

</x-layouts::app>