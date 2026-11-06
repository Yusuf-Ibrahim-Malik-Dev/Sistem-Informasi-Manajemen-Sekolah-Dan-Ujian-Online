@extends('layouts.admin')
@section('title', 'Default Lokasi & Pencarian')
@section('heading', 'Default Lokasi & Pencarian')
@section('eyebrow', 'Sistem')

@section('content')
    <div class="row g-4">
        <div class="col-lg-10" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Default Lokasi & Pencarian</h3>
                        <p class="section-sub">Nilai-nilai ini dibaca dari database, jadi dapat diubah tanpa menyentuh kode.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.pengaturan.lokasi.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Hasil Pencarian (Geocode)</label>
                            <input type="number" name="geocode_limit" class="form-control" value="{{ old('geocode_limit', $konfigurasi['geocode_limit']) }}" min="1" max="20" step="1">
                            <div class="form-text">Jumlah hasil maksimal yang ditampilkan saat admin mencari titik lokasi.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Negara Geocode</label>
                            <input type="text" name="geocode_country" class="form-control" value="{{ old('geocode_country', $konfigurasi['geocode_country']) }}" placeholder="id">
                            <div class="form-text">Kode negara ISO (misal: id) yang membatasi hasil geocoding.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Radius Presensi Bawaan (meter)</label>
                            <input type="number" name="default_radius_meters" class="form-control" value="{{ old('default_radius_meters', $konfigurasi['default_radius_meters']) }}" min="1" max="100000" step="1">
                            <div class="form-text">Radius bawaan saat menambah titik lokasi tanpa radius tertulis.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Akurasi GPS Maks Bawaan (meter)</label>
                            <input type="number" name="default_max_accuracy_meters" class="form-control" value="{{ old('default_max_accuracy_meters', $konfigurasi['default_max_accuracy_meters']) }}" min="1" max="100000" step="1">
                            <div class="form-text">Nilai akurasi GPS maksimum yang dianggap valid saat check-in.</div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan Default</button>
                        <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
