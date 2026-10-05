<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Finance - Personal Finance Manager</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-950 text-white antialiased">

    {{-- Navbar --}}
    <header class="border-b border-white/10">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">

            <a href="/" class="flex items-center gap-3">
                <img
                    src="{{ asset('images/MyFinance.png') }}"
                    alt="My Finance"
                    class="h-10 w-10 rounded-xl object-contain"
                >

                <div>
                    <div class="text-lg font-bold leading-tight">
                        My Finance
                    </div>
                    <div class="text-xs text-zinc-400">
                        Personal Finance Manager
                    </div>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="hidden rounded-xl px-5 py-2.5 text-sm font-medium text-zinc-300 transition hover:bg-white/5 hover:text-white sm:block"
                    >
                        Masuk
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500"
                    >
                        Daftar Gratis
                    </a>
                @endauth
            </div>

        </div>
    </header>


    {{-- Hero --}}
    <main>

        <section class="relative overflow-hidden">

            {{-- Decoration --}}
            <div class="absolute left-1/2 top-0 -z-10 h-[500px] w-[700px] -translate-x-1/2 rounded-full bg-blue-600/20 blur-[130px]"></div>

            <div class="mx-auto grid max-w-7xl items-center gap-16 px-6 py-20 lg:grid-cols-2 lg:px-8 lg:py-28">

                {{-- Left --}}
                <div>

                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-500/20 bg-blue-500/10 px-4 py-2 text-sm text-blue-300">
                        <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                        Kelola keuangan lebih mudah
                    </div>

                    <h1 class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                        Kendalikan
                        <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
                            keuanganmu
                        </span>
                        dalam satu tempat.
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-8 text-zinc-400 sm:text-lg">
                        Catat pemasukan dan pengeluaran, pantau saldo berbagai akun,
                        kelola kategori transaksi, dan lihat laporan keuangan secara
                        sederhana melalui My Finance.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-4">

                        @guest
                            <a
                                href="{{ route('register') }}"
                                class="rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-xl shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-500"
                            >
                                Mulai Sekarang
                            </a>

                            <a
                                href="{{ route('login') }}"
                                class="rounded-xl border border-white/10 bg-white/5 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10"
                            >
                                Saya Sudah Punya Akun
                            </a>
                        @else
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                            >
                                Buka Dashboard
                            </a>
                        @endguest

                    </div>

                    <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-zinc-400">
                        <span>✓ Gratis digunakan</span>
                        <span>✓ Multi akun</span>
                        <span>✓ Data terpisah per pengguna</span>
                    </div>

                </div>


                {{-- Right Preview --}}
                <div class="relative">

                    <div class="rounded-3xl border border-white/10 bg-zinc-900/80 p-5 shadow-2xl shadow-black/40 backdrop-blur">

                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p class="text-xs text-zinc-500">
                                    Total Saldo
                                </p>

                                <p class="mt-1 text-3xl font-bold">
                                    Rp 8.750.000
                                </p>
                            </div>

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400">
                                Rp
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-4">

                            <div class="rounded-2xl border border-emerald-500/10 bg-emerald-500/5 p-4">
                                <p class="text-xs text-zinc-400">
                                    Pemasukan
                                </p>
                                <p class="mt-2 text-xl font-bold text-emerald-400">
                                    + Rp 5.500.000
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    Bulan ini
                                </p>
                            </div>

                            <div class="rounded-2xl border border-red-500/10 bg-red-500/5 p-4">
                                <p class="text-xs text-zinc-400">
                                    Pengeluaran
                                </p>
                                <p class="mt-2 text-xl font-bold text-red-400">
                                    - Rp 2.150.000
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    Bulan ini
                                </p>
                            </div>

                        </div>


                        {{-- Fake chart --}}
                        <div class="mt-4 rounded-2xl border border-white/5 bg-black/20 p-5">

                            <div class="mb-5 flex items-center justify-between">
                                <div>
                                    <p class="font-medium">
                                        Cash Flow
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        Ringkasan 6 bulan
                                    </p>
                                </div>

                                <span class="text-sm font-semibold text-emerald-400">
                                    +24.8%
                                </span>
                            </div>

                            <div class="flex h-32 items-end gap-3">

                                <div class="h-[35%] flex-1 rounded-t-lg bg-blue-600/40"></div>
                                <div class="h-[50%] flex-1 rounded-t-lg bg-blue-600/50"></div>
                                <div class="h-[43%] flex-1 rounded-t-lg bg-blue-600/50"></div>
                                <div class="h-[70%] flex-1 rounded-t-lg bg-blue-500/60"></div>
                                <div class="h-[60%] flex-1 rounded-t-lg bg-blue-500/70"></div>
                                <div class="h-[90%] flex-1 rounded-t-lg bg-blue-500"></div>

                            </div>

                        </div>


                        {{-- Transactions --}}
                        <div class="mt-4 space-y-3">

                            <div class="flex items-center justify-between rounded-xl bg-white/[0.03] p-3">
                                <div>
                                    <p class="text-sm font-medium">
                                        Gaji Bulanan
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        Pemasukan
                                    </p>
                                </div>

                                <p class="text-sm font-semibold text-emerald-400">
                                    + Rp 5.000.000
                                </p>
                            </div>

                            <div class="flex items-center justify-between rounded-xl bg-white/[0.03] p-3">
                                <div>
                                    <p class="text-sm font-medium">
                                        Makan & Minum
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        Pengeluaran
                                    </p>
                                </div>

                                <p class="text-sm font-semibold text-red-400">
                                    - Rp 75.000
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- Features --}}
        <section class="border-y border-white/10 bg-zinc-900/30">

            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-8">

                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-semibold text-blue-400">
                        FITUR MY FINANCE
                    </p>

                    <h2 class="mt-3 text-3xl font-bold sm:text-4xl">
                        Semua yang kamu butuhkan untuk mencatat keuangan.
                    </h2>

                    <p class="mt-4 text-zinc-400">
                        Sederhana, cepat dan dirancang agar mudah digunakan sehari-hari.
                    </p>
                </div>


                <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-4">

                    <div class="rounded-2xl border border-white/10 bg-zinc-900 p-6 transition hover:-translate-y-1 hover:border-blue-500/30">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-xl">
                            📊
                        </div>
                        <h3 class="font-semibold">
                            Dashboard
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Lihat saldo, cash flow, pemasukan dan pengeluaran secara cepat.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-zinc-900 p-6 transition hover:-translate-y-1 hover:border-blue-500/30">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-xl">
                            💳
                        </div>
                        <h3 class="font-semibold">
                            Banyak Akun
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Kelola rekening bank, tunai dan e-wallet dalam satu aplikasi.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-zinc-900 p-6 transition hover:-translate-y-1 hover:border-blue-500/30">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-xl">
                            ↔️
                        </div>
                        <h3 class="font-semibold">
                            Transaksi
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Catat pemasukan dan pengeluaran dengan kategori yang fleksibel.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-zinc-900 p-6 transition hover:-translate-y-1 hover:border-blue-500/30">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-xl">
                            📄
                        </div>
                        <h3 class="font-semibold">
                            Laporan
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Pantau mutasi dan laporan keuangan berdasarkan periode.
                        </p>
                    </div>

                </div>

            </div>

        </section>


        {{-- CTA --}}
        <section class="px-6 py-20">

            <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl border border-blue-500/20 bg-gradient-to-br from-blue-600/20 to-cyan-500/5 px-6 py-14 text-center sm:px-12">

                <h2 class="text-3xl font-bold">
                    Mulai kelola keuanganmu hari ini.
                </h2>

                <p class="mx-auto mt-4 max-w-xl text-zinc-400">
                    Buat akun My Finance dan mulai mencatat pemasukan,
                    pengeluaran, serta saldo dengan lebih teratur.
                </p>

                @guest
                    <a
                        href="{{ route('register') }}"
                        class="mt-8 inline-flex rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Buat Akun Gratis
                    </a>
                @else
                    <a
                        href="{{ route('dashboard') }}"
                        class="mt-8 inline-flex rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                    >
                        Masuk ke Dashboard
                    </a>
                @endguest

            </div>

        </section>

    </main>


    {{-- Footer --}}
    <footer class="border-t border-white/10">

        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-zinc-500 sm:flex-row lg:px-8">

            <div class="flex items-center gap-2">
                <img
                    src="{{ asset('images/MyFinance.png') }}"
                    class="h-7 w-7 rounded-lg object-contain"
                    alt="My Finance"
                >
                <span>
                    My Finance
                </span>
            </div>

            <p>
                © {{ date('Y') }} My Finance. Personal Finance Manager.
            </p>

        </div>

    </footer>

</body>
</html>