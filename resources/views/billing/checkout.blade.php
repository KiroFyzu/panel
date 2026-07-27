@extends('templates/wrapper', [
    'css' => ['body' => 'bg-neutral-900'],
])

@section('title')
    Checkout - {{ $package->name }}
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
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="{{ route('index') }}" class="flex items-center gap-3 text-neutral-300 hover:text-white transition">
                    <img src="/favicons/favicon-32x32.png" alt="logo" class="w-8 h-8">
                    <span class="font-semibold">{{ config('app.name', 'Pterodactyl') }}</span>
                </a>
                <a href="{{ route('index') }}" class="text-sm text-neutral-400 hover:text-white">&larr; Kembali</a>
            </div>
        </header>

        <div class="max-w-5xl mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Order summary --}}
            <aside class="lg:col-span-1 rounded-xl border border-neutral-700 bg-neutral-800/60 p-6 h-fit">
                <div class="text-xs uppercase tracking-wider text-neutral-400">{{ $package->name }}</div>
                <div class="mt-2 text-3xl font-bold">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                @if($package->old_price)
                    <div class="text-sm text-neutral-500 line-through">Rp {{ number_format($package->old_price, 0, ',', '.') }}</div>
                @endif
                <ul class="mt-5 space-y-2 text-sm text-neutral-300">
                    <li>RAM: <strong>{{ $package->ram }} GB</strong></li>
                    <li>CPU: <strong>{{ $package->cpu }}%</strong></li>
                    <li>Disk: <strong>{{ $package->disk }} GB</strong></li>
                </ul>
            </aside>

            {{-- Form --}}
            <main class="lg:col-span-2 rounded-xl border border-neutral-700 bg-neutral-800/60 p-6">
                @if($errors->any())
                    <div class="mb-4 p-3 rounded-md bg-red-900/40 border border-red-700 text-red-200 text-sm">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('billing.pay', ['slug' => $package->slug]) }}">
                    {!! csrf_field() !!}

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Pilih Node</label>
                            <select name="node_id" required class="w-full rounded-md bg-neutral-900 border border-neutral-700 px-3 py-2 text-neutral-100 focus:border-blue-500 focus:outline-none">
                                <option value="">-- Pilih Node --</option>
                                @foreach($nodes as $node)
                                    <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->location->short ?? $node->fqdn }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Pilih Egg</label>
                            <select name="egg_id" required class="w-full rounded-md bg-neutral-900 border border-neutral-700 px-3 py-2 text-neutral-100 focus:border-blue-500 focus:outline-none">
                                <option value="">-- Pilih Egg --</option>
                                @foreach($eggs as $egg)
                                    <option value="{{ $egg->id }}">{{ $egg->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-4 border-t border-neutral-700">
                            <button type="submit" class="w-full px-4 py-3 rounded-md bg-blue-600 hover:bg-blue-500 text-white font-semibold transition">
                                Lanjut ke Pembayaran
                            </button>
                            <p class="mt-2 text-xs text-neutral-500 text-center">Anda akan diarahkan ke halaman pembayaran QRIS.</p>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
@endsection