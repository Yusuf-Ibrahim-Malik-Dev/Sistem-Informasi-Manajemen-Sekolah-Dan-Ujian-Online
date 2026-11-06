@php
    $editing = $location !== null;
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ $editing ? route('admin.absensi.lokasi.update', $location) : route('admin.absensi.lokasi.store') }}">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif
                <input type="hidden" name="school_id" value="{{ $school->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">{{ $editing ? 'Edit Titik Lokasi' : 'Tambah Titik Lokasi' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Titik</label>
                            <input type="text" name="name" class="form-control" required maxlength="150" value="{{ $editing ? $location->name : old('name') }}" placeholder="Contoh: Gedung Utama / Kampus 2">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Cari Lokasi</label>
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control form-control-sm" data-lokasi-cari placeholder="Ketik nama tempat atau alamat, contoh: Gedung Utama atau SMA Nusantara 2">
                                <button type="button" class="btn btn-soft btn-sm text-nowrap" data-lokasi-cari-btn>
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                            </div>
                            <div class="lokasi-results"></div>
                            <div class="form-check form-switch mt-2" style="align-items:center">
                                <input class="form-check-input" type="checkbox" role="switch" id="lokasi-pakai-google" value="1">
                                <label class="form-check-label" for="lokasi-pakai-google">Gunakan Google Maps (jika kunci API tersedia)</label>
                            </div>
                            <div class="form-text mt-1">
                                Pencarian memakai OpenStreetMap (Nominatim). Nominatim adalah kamus tempat (place-name) dan
                                tidak menangkap nama perusahaan. Untuk nama bisnis, gunakan Google Maps di atas (opsional)
                                atau salin koordinat titiknya, lalu isi Latitude / Longitude.
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pilih Titik di Peta</label>
                            <div class="lokasi-picker" style="height:300px;border-radius:12px;overflow:hidden;border:1px solid var(--line);background:#eef2ff"></div>
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <button type="button" class="btn btn-soft btn-sm" data-lokasi-saya>
                                    <i class="bi bi-crosshair me-1"></i> Gunakan Lokasi Saya
                                </button>
                                <span class="text-muted-2" style="font-size:.76rem">Klik peta untuk menandai titik presensi secara akurat.</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.0000001" min="-90" max="90" name="latitude" class="form-control" required value="{{ $editing ? $location->latitude : old('latitude') }}" placeholder="-6.2000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.0000001" min="-180" max="180" name="longitude" class="form-control" required value="{{ $editing ? $location->longitude : old('longitude') }}" placeholder="106.8166667">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Radius Presensi (meter)</label>
                            <input type="number" min="1" max="100000" name="radius_meters" class="form-control" required value="{{ $editing ? $location->radius_meters : old('radius_meters', $defaultRadius) }}">
                            <div class="form-text">Default {{ $defaultRadius }} meter sesuai kebijakan awal.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Akurasi GPS Maks (meter)</label>
                            <input type="number" min="1" max="100000" name="max_accuracy_meters" class="form-control" required value="{{ $editing ? $location->max_accuracy_meters : old('max_accuracy_meters', $defaultAccuracy) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Berlaku Dari</label>
                            <input type="date" name="valid_from" class="form-control" value="{{ $editing ? $location->valid_from?->format('Y-m-d') : old('valid_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Berlaku Sampai</label>
                            <input type="date" name="valid_to" class="form-control" value="{{ $editing ? $location->valid_to?->format('Y-m-d') : old('valid_to') }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="{{ $modalId }}_active" value="1" @checked($editing ? $location->is_active : old('is_active', true))>
                                <label class="form-check-label" for="{{ $modalId }}_active">Lokasi aktif digunakan untuk presensi</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mb-0" style="font-size:.8rem">
                                <i class="bi bi-info-circle me-1"></i>
                                Koordinat diverifikasi di server. Buka tautan peta pada tabel setelah data disimpan untuk memastikan titik sudah benar.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
