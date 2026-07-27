@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'packages'])

@section('title')
    Price Packages
@endsection

@section('content-header')
    <h1>Price Packages<small>Kelola paket harga panel.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.settings') }}">Settings</a></li>
        <li class="active">Packages</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Daftar Paket</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.packages.create') }}" class="btn btn-sm btn-primary">+ Paket Baru</a>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Price</th>
                                <th>RAM</th>
                                <th>CPU</th>
                                <th>Disk</th>
                                <th>Sort</th>
                                <th>Status</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $pkg)
                                <tr>
                                    <td><strong>{{ $pkg->name }}</strong></td>
                                    <td><code>{{ $pkg->slug }}</code></td>
                                    <td>Rp {{ number_format($pkg->price, 0, ',', '.') }}</td>
                                    <td>{{ $pkg->ram }} GB</td>
                                    <td>{{ $pkg->cpu }}%</td>
                                    <td>{{ $pkg->disk }} GB</td>
                                    <td>{{ $pkg->sort }}</td>
                                    <td>
                                        @if($pkg->is_active)
                                            <span class="label label-success">Active</span>
                                        @else
                                            <span class="label label-default">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.packages.edit', $pkg->id) }}" class="btn btn-xs btn-primary">Edit</a>
                                        <form method="POST" action="{{ route('admin.packages.destroy', $pkg->id) }}" style="display:inline" onsubmit="return confirm('Hapus paket ini?')">
                                            {!! csrf_field() !!}
                                            {!! method_field('DELETE') !!}
                                            <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Belum ada paket. Klik "+ Paket Baru" untuk membuat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($packages, 'links'))
                    <div class="box-footer">{{ $packages->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection