@extends('templates/wrapper', [
    'css' => ['body' => 'bg-neutral-900'],
])

@section('title')
    {{ $appName }} - Paket Harga
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
                    <a href="{{ route('index') }}" class="text-neutral-300 hover:text-white transition">Dashboard</a>
                </nav>
            </div>
        </header>

        {{-- Heading --}}
        <section class="max-w-7xl mx-auto px-6 pt-12 pb-8 text-center">
            <h1 class="text-3xl font-bold">Pilih Paket Server</h1>
            <p class="mt-2 text-neutral-400">Pilih paket sesuai kebutuhanmu, bayar via QRIS, server auto-deploy.</p>
        </section>

        {{-- Pricing grid --}}
        <section class="max-w-7xl mx-auto px-6 pb-20">
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

        {{-- Purchase History --}}
        @if($invoices->isNotEmpty())
        <section class="max-w-7xl mx-auto px-6 pb-20">
            <h2 class="text-2xl font-bold mb-6">Riwayat Pembelian</h2>
            <div class="overflow-x-auto rounded-xl border border-neutral-700">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-800 text-neutral-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Invoice</th>
                            <th class="px-4 py-3 text-left">Paket</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Total</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Server</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-700">
                        @foreach($invoices as $inv)
                        <tr class="hover:bg-neutral-800/40 transition">
                            <td class="px-4 py-3 font-mono text-xs">{{ $inv->order_id }}</td>
                            <td class="px-4 py-3">{{ $inv->package->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($inv->status === 'paid') bg-green-700/40 text-green-300 border border-green-600
                                    @elseif($inv->status === 'pending') bg-yellow-700/40 text-yellow-300 border border-yellow-600
                                    @else bg-red-700/40 text-red-300 border border-red-600 @endif">
                                    {{ strtoupper($inv->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">Rp {{ number_format($inv->total_payment, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-neutral-400">{{ $inv->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @if($inv->server_id)
                                    <a href="/server/{{ $inv->server_id }}" class="text-blue-400 hover:underline">Lihat</a>
                                @else
                                    <span class="text-neutral-600">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Footer --}}
        <footer class="border-t border-neutral-800 py-6 text-center text-sm text-neutral-500">
            &copy; {{ date('Y') }} {{ $appName }}. Powered by Pterodactyl Panel.
        </footer>
    </div>
@endsection
