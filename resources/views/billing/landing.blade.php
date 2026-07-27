@extends('templates/wrapper', [
    'css' => ['body' => 'bg-neutral-900'],
])

@section('title')
    {{ $appName }} - Paket Harga
@endsection

@section('meta')
    @parent
    <meta name="description" content="Paket harga panel Pterodactyl - pilih paket sesuai kebutuhan server Anda.">
@endsection

@section('user-data')
    {{-- No PterodactylUser for guests; React not mounting on this page anyway. --}}
@endsection

@section('container')
    <div class="min-h-screen bg-neutral-900 text-neutral-100">
        {{-- Header --}}
        <header class="border-b border-neutral-800">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/favicons/favicon-32x32.png" alt="logo" class="w-8 h-8">
                    <span class="font-semibold text-lg">{{ $appName }}</span>
                </div>
                <nav class="flex items-center gap-4 text-sm">
                    <a href="#paket" class="text-neutral-300 hover:text-white transition">Paket</a>
                    @auth
                        <a href="{{ route('account') }}" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white font-medium transition">Dashboard</a>
                    @else
                        <a href="{{ route('auth.login') }}" class="px-4 py-2 rounded-md bg-neutral-800 hover:bg-neutral-700 text-white font-medium transition border border-neutral-700">Login</a>
                        <a href="{{ route('auth.login') }}" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white font-medium transition">Login / Daftar</a>
                    @endauth
                </nav>
            </div>
        </header>

        {{-- Hero --}}
        <section class="max-w-7xl mx-auto px-6 pt-16 pb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight">
                Panel Pterodactyl <span class="text-blue-500">Mulai dari Rp 4.000</span>
            </h1>
            <p class="mt-4 text-neutral-400 max-w-2xl mx-auto">
                Pilih paket sesuai kebutuhan. Pembayaran otomatis via QRIS. Server ter-deploy dalam hitungan menit setelah pembayaran dikonfirmasi.
            </p>
        </section>

        {{-- Pricing grid --}}
        <section id="paket" class="max-w-7xl mx-auto px-6 pb-20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($packages as $pkg)
                    <div class="rounded-xl border border-neutral-700 bg-neutral-800/60 p-6 flex flex-col hover:border-blue-500 transition">
                        <div class="text-xs uppercase tracking-wider text-neutral-400">{{ $pkg->name }}</div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-bold">Rp {{ number_format($pkg->price, 0, ',', '.') }}</span>
                            <span class="text-neutral-500 text-sm">/bulan</span>
                        </div>
                        @if($pkg->old_price)
                            <div class="mt-1 text-sm text-neutral-500 line-through">Rp {{ number_format($pkg->old_price, 0, ',', '.') }}</div>
                        @endif

                        <ul class="mt-5 space-y-2 text-sm text-neutral-300 flex-1">
                            <li class="flex items-center gap-2">
                                <span class="text-blue-500">•</span> RAM <strong class="ml-auto">{{ $pkg->ram }} GB</strong>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-blue-500">•</span> CPU <strong class="ml-auto">{{ $pkg->cpu }}%</strong>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-blue-500">•</span> Disk <strong class="ml-auto">{{ $pkg->disk }} GB</strong>
                            </li>
                        </ul>

                        <a href="{{ route('billing.checkout', ['slug' => $pkg->slug]) }}"
                           class="mt-6 block text-center px-4 py-2.5 rounded-md bg-blue-600 hover:bg-blue-500 text-white font-semibold transition">
                            Order Sekarang
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-neutral-800 py-6 text-center text-sm text-neutral-500">
            &copy; {{ date('Y') }} {{ $appName }}. Powered by Pterodactyl Panel.
        </footer>
    </div>
@endsection