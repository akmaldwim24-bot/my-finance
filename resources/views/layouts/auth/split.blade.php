<!DOCTYPE html>

<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="dark"
>

<head>
    @include('partials.head')
</head>

<body
    class="min-h-screen antialiased
           bg-white dark:bg-zinc-950"
>

    <div
        class="grid min-h-screen
               grid-cols-1 lg:grid-cols-2"
    >

        {{-- LEFT BRANDING --}}
        <div
            class="relative hidden overflow-hidden
                   bg-gradient-to-br
                   from-blue-700 via-blue-600 to-indigo-700
                   p-10 text-white lg:flex lg:flex-col"
        >

            {{-- DECORATION --}}
            <div
                class="absolute -left-24 -top-24
                       h-72 w-72 rounded-full
                       bg-white/10 blur-3xl"
            ></div>

            <div
                class="absolute -bottom-24 -right-24
                       h-80 w-80 rounded-full
                       bg-cyan-300/10 blur-3xl"
            ></div>


            {{-- LOGO --}}
            <a
                href="{{ route('home') }}"
                class="relative z-10 flex items-center gap-3"
                wire:navigate
            >

                <div
                    class="flex h-12 w-12 items-center
                           justify-center rounded-2xl
                           bg-white/15 backdrop-blur"
                >
                    <img
                        src="{{ asset('images/MyFinance.png') }}"
                        alt="My Finance"
                        class="h-10 w-10 object-contain"
                    >
                </div>

                <div>
                    <div class="text-xl font-bold">
                        My Finance
                    </div>

                    <div class="text-sm text-blue-100">
                        Personal Finance Manager
                    </div>
                </div>

            </a>


            {{-- CONTENT --}}
            <div
                class="relative z-10 my-auto
                       max-w-xl"
            >

                <div
                    class="mb-6 inline-flex
                           rounded-full bg-white/10
                           px-4 py-2 text-sm
                           text-blue-100 backdrop-blur"
                >
                    Kelola keuangan dengan lebih mudah
                </div>

                <h1
                    class="text-4xl font-bold
                           leading-tight xl:text-5xl"
                >
                    Kendalikan keuanganmu dalam satu tempat.
                </h1>

                <p
                    class="mt-5 max-w-lg
                           text-base leading-7
                           text-blue-100"
                >
                    Catat pemasukan, pengeluaran,
                    saldo akun, dan pantau kondisi
                    keuangan secara lebih teratur.
                </p>


                {{-- FEATURE --}}
                <div
                    class="mt-10 grid grid-cols-1
                           gap-4 sm:grid-cols-2"
                >

                    <div
                        class="rounded-2xl border
                               border-white/10
                               bg-white/10 p-4
                               backdrop-blur"
                    >
                        <div class="text-2xl">
                            📊
                        </div>

                        <div class="mt-2 font-semibold">
                            Dashboard
                        </div>

                        <div class="mt-1 text-sm text-blue-100">
                            Pantau kondisi keuangan dengan cepat.
                        </div>
                    </div>


                    <div
                        class="rounded-2xl border
                               border-white/10
                               bg-white/10 p-4
                               backdrop-blur"
                    >
                        <div class="text-2xl">
                            💰
                        </div>

                        <div class="mt-2 font-semibold">
                            Transaksi
                        </div>

                        <div class="mt-1 text-sm text-blue-100">
                            Catat pemasukan dan pengeluaran.
                        </div>
                    </div>


                    <div
                        class="rounded-2xl border
                               border-white/10
                               bg-white/10 p-4
                               backdrop-blur"
                    >
                        <div class="text-2xl">
                            🏦
                        </div>

                        <div class="mt-2 font-semibold">
                            Banyak Akun
                        </div>

                        <div class="mt-1 text-sm text-blue-100">
                            Kelola bank, tunai, dan e-wallet.
                        </div>
                    </div>


                    <div
                        class="rounded-2xl border
                               border-white/10
                               bg-white/10 p-4
                               backdrop-blur"
                    >
                        <div class="text-2xl">
                            📄
                        </div>

                        <div class="mt-2 font-semibold">
                            Laporan
                        </div>

                        <div class="mt-1 text-sm text-blue-100">
                            Lihat mutasi dan laporan keuangan.
                        </div>
                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div
                class="relative z-10
                       text-sm text-blue-100"
            >
                © {{ date('Y') }} My Finance
            </div>

        </div>


        {{-- RIGHT AUTH FORM --}}
        <div
            class="flex min-h-screen
                   items-center justify-center
                   bg-zinc-50 px-6 py-10
                   dark:bg-zinc-950"
        >

            <div class="w-full max-w-md">

                {{-- MOBILE BRAND --}}
                <div
                    class="mb-8 flex flex-col
                           items-center lg:hidden"
                >
                    <img
                        src="{{ asset('images/MyFinance.png') }}"
                        alt="My Finance"
                        class="h-16 w-16 object-contain"
                    >

                    <div
                        class="mt-3 text-xl font-bold
                               text-zinc-900 dark:text-white"
                    >
                        My Finance
                    </div>

                    <div
                        class="text-sm text-zinc-500"
                    >
                        Personal Finance Manager
                    </div>
                </div>


                {{-- FORM CARD --}}
                <div
                    class="rounded-2xl border
                           border-zinc-200 bg-white
                           p-7 shadow-sm
                           dark:border-zinc-800
                           dark:bg-zinc-900 sm:p-8"
                >

                    {{ $slot }}

                </div>


                <div
                    class="mt-6 text-center
                           text-xs text-zinc-500"
                >
                    My Finance • Kelola keuangan dengan lebih rapi
                </div>

            </div>

        </div>

    </div>


    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts

</body>

</html>