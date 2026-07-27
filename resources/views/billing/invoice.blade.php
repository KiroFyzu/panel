@extends('templates/wrapper', [
    'css' => ['body' => 'bg-neutral-900'],
])

@section('title')
    Invoice {{ $invoice->order_id }}
@endsection

@section('user-data')
    @if(!is_null(Auth::user()))
        <script>
            window.PterodactylUser = {!! json_encode(Auth::user()->toVueObject()) !!};
        </script>
    @endif
@endsection

@section('container')
    <div class="min-h-screen bg-neutral-900 text-neutral-100">
        <header class="border-b border-neutral-800">
            <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="{{ route('index') }}" class="flex items-center gap-3 text-neutral-300 hover:text-white">
                    <img src="/favicons/favicon-32x32.png" alt="logo" class="w-8 h-8">
                    <span class="font-semibold">{{ config('app.name', 'Pterodactyl') }}</span>
                </a>
                <a href="{{ route('account') }}" class="text-sm text-neutral-400 hover:text-white">Dashboard</a>
            </div>
        </header>

        <div class="max-w-4xl mx-auto px-6 py-10">
            <div class="rounded-xl border border-neutral-700 bg-neutral-800/60 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-neutral-400">Invoice</div>
                        <div class="text-xl font-semibold mt-1">{{ $invoice->order_id }}</div>
                        <div class="mt-2 text-sm text-neutral-400">
                            Paket: <strong class="text-neutral-200">{{ $invoice->package->name }}</strong>
                            &middot; Node: <strong class="text-neutral-200">{{ $invoice->node->name }}</strong>
                            &middot; Egg: <strong class="text-neutral-200">{{ $invoice->egg->name }}</strong>
                        </div>
                    </div>
                    <div id="status-badge" class="px-3 py-1 rounded-full text-xs font-medium
                        @if($invoice->status === 'paid') bg-green-700/40 text-green-300 border border-green-600
                        @elseif($invoice->status === 'pending') bg-yellow-700/40 text-yellow-300 border border-yellow-600
                        @else bg-red-700/40 text-red-300 border border-red-600 @endif">
                        {{ strtoupper($invoice->status) }}
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-md bg-neutral-900 p-4 border border-neutral-700">
                        <div class="text-xs text-neutral-400">Total Bayar</div>
                        <div class="mt-1 text-2xl font-bold">Rp {{ number_format($invoice->total_payment, 0, ',', '.') }}</div>
                        @if($invoice->fee > 0)
                            <div class="text-xs text-neutral-500 mt-1">Termasuk fee Rp {{ number_format($invoice->fee, 0, ',', '.') }}</div>
                        @endif
                    </div>
                    <div class="rounded-md bg-neutral-900 p-4 border border-neutral-700">
                        <div class="text-xs text-neutral-400">Metode</div>
                        <div class="mt-1 text-lg font-semibold">QRIS</div>
                    </div>
                    <div class="rounded-md bg-neutral-900 p-4 border border-neutral-700">
                        <div class="text-xs text-neutral-400">Batas Waktu</div>
                        <div id="countdown" class="mt-1 text-lg font-mono" data-expired="{{ $invoice->expired_at?->toIso8601String() }}">
                            @if($invoice->expired_at) --:--:-- @else - @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-col items-center">
                    <div id="qrcode" class="bg-white p-4 rounded-md" data-qr="{{ $invoice->payment_number }}"></div>
                    <p class="mt-4 text-sm text-neutral-400 text-center max-w-md">
                        Scan QR di atas menggunakan aplikasi e-wallet atau mobile banking Anda (QRIS).
                        Setelah pembayaran berhasil, server akan otomatis dibuat.
                    </p>

                    <button type="button" id="check-btn" class="mt-6 px-4 py-2 rounded-md bg-neutral-700 hover:bg-neutral-600 text-white text-sm transition">
                        Cek Status Pembayaran
                    </button>
                </div>

                @if($invoice->status === 'paid' && $invoice->server_id)
                    <div class="mt-6 p-4 rounded-md bg-green-900/30 border border-green-700 text-green-200 text-sm">
                        Server berhasil dibuat. <a href="/server/{{ $invoice->server_id }}" class="underline font-semibold">Buka Server &rarr;</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Inline QR generator via qrcodejs (CDN) + simple polling --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            const qrEl = document.getElementById('qrcode');
            const data = qrEl.dataset.qr;
            if (data) {
                qrEl.innerHTML = '';
                new QRCode(qrEl, {
                    text: data,
                    width: 220,
                    height: 220,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                });
            }

            const cd = document.getElementById('countdown');
            const exp = cd.dataset.expired;
            if (exp) {
                const target = new Date(exp).getTime();
                function tick() {
                    const diff = target - Date.now();
                    if (diff <= 0) {
                        cd.textContent = 'EXPIRED';
                        return;
                    }
                    const m = Math.floor(diff / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    cd.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                    setTimeout(tick, 1000);
                }
                tick();
            }

            const btn = document.getElementById('check-btn');
            btn.addEventListener('click', pollStatus);
            let pollTimer = null;
            function pollStatus() {
                fetch('{{ route('billing.invoice.status', ['invoice' => $invoice->id]) }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'paid') {
                        window.location.reload();
                    } else {
                        if (!pollTimer) pollTimer = setInterval(pollStatus, 5000);
                    }
                })
                .catch(() => {
                    if (!pollTimer) pollTimer = setInterval(pollStatus, 5000);
                });
            }
            // Auto-poll every 5s if still pending
            @if($invoice->status === 'pending')
                pollTimer = setInterval(pollStatus, 5000);
            @endif
        })();
    </script>
@endsection