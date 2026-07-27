@extends('layouts.admin')
@include('partials/admin.settings.nav', ['activeTab' => 'packages'])

@section('title')
    {{ $package->exists ? 'Edit' : 'Create' }} Package
@endsection

@section('content-header')
    <h1>{{ $package->exists ? 'Edit' : 'Create' }} Package<small>{{ $package->exists ? $package->name : 'Tambah paket harga baru.' }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.packages.index') }}">Packages</a></li>
        <li class="active">{{ $package->exists ? 'Edit' : 'Create' }}</li>
    </ol>
@endsection

@section('content')
    @yield('settings::nav')
    <div class="row">
        <div class="col-md-8">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Detail Paket</h3>
                </div>
                <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package->id) : route('admin.packages.store') }}">
                    {!! csrf_field() !!}
                    @if($package->exists)
                        {!! method_field('PATCH') !!}
                    @endif

                    <div class="box-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Nama</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $package->name) }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug', $package->slug) }}" required>
                                <p class="text-muted small">URL identifier (huruf kecil, strip). Contoh: starter, lite, basic</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $package->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Harga (Rp)</label>
                                <input type="number" name="price" class="form-control" value="{{ old('price', $package->price) }}" min="0" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Harga Coret (opsional)</label>
                                <input type="number" name="old_price" class="form-control" value="{{ old('old_price', $package->old_price) }}" min="0">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Sort Order</label>
                                <input type="number" name="sort" class="form-control" value="{{ old('sort', $package->sort ?? 0) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>RAM (GB)</label>
                                <input type="number" name="ram" class="form-control" value="{{ old('ram', $package->ram ?? 1) }}" min="1" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>CPU (%)</label>
                                <input type="number" name="cpu" class="form-control" value="{{ old('cpu', $package->cpu ?? 100) }}" min="0" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Disk (GB)</label>
                                <input type="number" name="disk" class="form-control" value="{{ old('disk', $package->disk ?? 3) }}" min="1" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                                Aktif (tampilkan di landing page)
                            </label>
                        </div>

                        <div class="form-group">
                            <label>Nodes Tersedia</label>
                            <select name="nodes[]" class="form-control" multiple size="5">
                                @foreach($nodes as $node)
                                    <option value="{{ $node->id }}" {{ in_array($node->id, old('nodes', $selectedNodes)) ? 'selected' : '' }}>{{ $node->name }} ({{ $node->fqdn }})</option>
                                @endforeach
                            </select>
                            <p class="text-muted small">Tahan Ctrl/Cmd untuk pilih lebih dari satu.</p>
                        </div>

                        <div class="form-group">
                            <label>Eggs Tersedia</label>
                            <select name="eggs[]" class="form-control" multiple size="5">
                                @foreach($eggs as $egg)
                                    <option value="{{ $egg->id }}" {{ in_array($egg->id, old('eggs', $selectedEggs)) ? 'selected' : '' }}>{{ $egg->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-muted small">User pilih salah satu saat checkout.</p>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ route('admin.packages.index') }}" class="btn btn-default">Batal</a>
                        <button type="submit" class="btn btn-primary pull-right">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection