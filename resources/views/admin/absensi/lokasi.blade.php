@extends('layouts.admin')

@section('title', 'Lokasi & Kebijakan Absensi')
@section('heading', 'Lokasi & Kebijakan Absensi')
@section('eyebrow', 'Data Peserta')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sekolah</label>
                <select name="school_id" class="form-select" onchange="this.form.submit()" @disabled($schools->count() <= 1)>
                    @foreach ($schools as $option)
                        <option value="{{ $option->id }}" @selected($school->id === $option->id)>{{ $option->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7 text-md-end">
                <button type="button" class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#locationCreate">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Titik Lokasi
                </button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-lg-7" data-aos="fade-up">
            <div class="glass-card card-pad h-100">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Titik Presensi</h3>
                        <p class="section-sub">Radius default {{ $defaultRadius }} meter. Validasi jarak dilakukan di server dengan rumus Haversine.</p>
                    </div>
                    <span class="badge-soft info"><i class="bi bi-geo-alt"></i> {{ $locations->count() }} titik</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Koordinat</th>
                                <th class="text-center">Radius</th>
                                <th class="text-center">Akurasi Maks</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($locations as $location)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $location->name }}</div>
                                        <div class="text-muted-2" style="font-size:.74rem">
                                            @if ($location->valid_from || $location->valid_to)
                                                Berlaku {{ $location->valid_from?->format('d M Y') ?? '...' }} - {{ $location->valid_to?->format('d M Y') ?? '...' }}
                                            @else
                                                Tanpa batas tanggal
                                            @endif
                                        </div>
                                    </td>
                                    <td style="font-size:.8rem">
                                        {{ $location->latitude }}, {{ $location->longitude }}
                                        <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener" class="ms-1" title="Buka di peta">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">{{ $location->radius_meters }} m</td>
                                    <td class="text-center">{{ $location->max_accuracy_meters }} m</td>
                                    <td class="text-center">
                                        <span class="badge-soft {{ $location->is_active ? 'success' : 'secondary' }}">{{ $location->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#locationEdit{{ $location->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.absensi.lokasi.destroy', $location) }}" class="d-inline" onsubmit="return confirm('Hapus titik lokasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-ghost text-danger" @disabled($location->sessions_count > 0) title="{{ $location->sessions_count > 0 ? 'Sudah dipakai sesi presensi' : 'Hapus' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-geo-alt"></i>
                                            Belum ada titik presensi. Tambahkan titik lokasi sekolah terlebih dahulu.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Kebijakan Absensi Harian</h3>
                        <p class="section-sub">Berlaku untuk {{ $school->label }}.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.absensi.lokasi.policy') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="school_id" value="{{ $school->id }}">

                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Default sekolah</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $policy->academic_year_id) === (string) $year->id)>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Jam Layanan Mulai</label>
                            <input type="time" name="service_start" value="{{ old('service_start', $policy->service_start ? substr($policy->service_start, 0, 5) : '') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Layanan Selesai</label>
                            <input type="time" name="service_end" value="{{ old('service_end', $policy->service_end ? substr($policy->service_end, 0, 5) : '') }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Toleransi Terlambat (menit)</label>
                            <input type="number" name="late_tolerance_minutes" min="0" max="720" value="{{ old('late_tolerance_minutes', $policy->late_tolerance_minutes ?? 0) }}" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Akurasi GPS Maks (meter)</label>
                            <input type="number" name="max_accuracy_meters" min="1" max="100000" value="{{ old('max_accuracy_meters', $defaultAccuracy) }}" class="form-control">
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="require_location" id="require_location" value="1" @checked(old('require_location', $policy->require_location ?? true))>
                        <label class="form-check-label" for="require_location">Wajib berada dalam radius lokasi</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="require_checkin" id="require_checkin" value="1" @checked(old('require_checkin', $policy->require_checkin ?? true))>
                        <label class="form-check-label" for="require_checkin">Aktifkan check-in</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="require_checkout" id="require_checkout" value="1" @checked(old('require_checkout', $policy->require_checkout ?? false))>
                        <label class="form-check-label" for="require_checkout">Aktifkan check-out</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="allow_manual" id="allow_manual" value="1" @checked(old('allow_manual', $policy->allow_manual ?? true))>
                        <label class="form-check-label" for="allow_manual">Izinkan koreksi manual petugas</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_required" id="is_required" value="1" @checked(old('is_required', $policy->is_required ?? false))>
                        <label class="form-check-label" for="is_required">Absensi harian wajib</label>
                    </div>

                    <button class="btn btn-gradient w-100">
                        <i class="bi bi-save me-1"></i> Simpan Kebijakan
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('admin.absensi.partials.location-modal', ['modalId' => 'locationCreate', 'location' => null, 'defaultRadius' => $defaultRadius, 'defaultAccuracy' => $defaultAccuracy])

    @foreach ($locations as $location)
        @include('admin.absensi.partials.location-modal', ['modalId' => 'locationEdit'.$location->id, 'location' => $location, 'defaultRadius' => $defaultRadius, 'defaultAccuracy' => $defaultAccuracy])
    @endforeach
@endsection

@push('head')
<style>
    .lokasi-results {
        max-height: 240px;
        overflow-y: auto;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #ffffff;
        margin-top: 4px;
        z-index: 1080;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .lokasi-result-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .lokasi-result-item {
        padding: 8px 12px;
        border-bottom: 1px solid var(--line);
        cursor: pointer;
        transition: background 0.1s;
    }

    .lokasi-result-item:last-child {
        border-bottom: none;
    }

    .lokasi-result-item:hover,
    .lokasi-result-item[focus="true"] {
        background: #eef2ff;
        outline: none;
    }

    .lokasi-result-name {
        font-weight: 600;
        font-size: 0.85rem;
    }

    .lokasi-result-detail {
        font-size: 0.74rem;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lokasi-result-type {
        display: inline-block;
        font-size: 0.62rem;
        background: #eef2ff;
        color: #4f46e5;
        border-radius: 999px;
        padding: 1px 7px;
        margin-top: 2px;
        white-space: nowrap;
    }

    .lokasi-empty,
    .lokasi-loading {
        padding: 12px 14px;
        font-size: 0.78rem;
        color: var(--text-muted);
        text-align: center;
        background: #f8fafc;
        border: 1px dashed var(--line);
        border-radius: 10px;
    }

    .lokasi-note {
        margin-top: 8px;
        font-size: 0.74rem;
        color: #b45309;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const start = function () {
            const DEFAULT_RADIUS_METER = {{ $defaultRadius }} || 50;

            if (!window.L) {
                window.addEventListener('load', start);
                return;
            }

            document.querySelectorAll('.lokasi-picker').forEach(function (picker) {
                const modal = picker.closest('.modal');
                if (!modal) return;

                const latInput = modal.querySelector('input[name=latitude]');
                const lngInput = modal.querySelector('input[name=longitude]');
                const radiusInput = modal.querySelector('input[name=radius_meters]');
                if (!latInput || !lngInput) return;

                let map = null;
                let marker = null;
                let circle = null;

                const getLat = () => parseFloat(latInput.value);
                const getLng = () => parseFloat(lngInput.value);

                const getRadius = function () {
                    const value = radiusInput ? parseFloat(radiusInput.value) : NaN;
                    if (Number.isFinite(value)) {
                        return value;
                    }
                    return DEFAULT_RADIUS_METER;
                };

                const refresh = function () {
                    const lat = getLat();
                    const lng = getLng();

                    if (!map || !Number.isFinite(lat) || !Number.isFinite(lng)) return;

                    const ll = [lat, lng];

                    if (marker) map.removeLayer(marker);
                    if (circle) map.removeLayer(circle);

                    marker = L.marker(ll).addTo(map);
                    circle = L.circle(ll, {
                        radius: getRadius(),
                        color: '#4f46e5',
                        weight: 2,
                        dashArray: '6 6',
                        fillColor: '#6366f1',
                        fillOpacity: 0.12,
                    }).addTo(map);

                    map.setView(ll, 16);
                };

                const initMap = function () {
                    if (map) {
                        map.invalidateSize();
                        refresh();
                        return;
                    }

                    const lat = getLat();
                    const lng = getLng();
                    const center = (Number.isFinite(lat) && Number.isFinite(lng)) ? [lat, lng] : [-6.2, 106.8166667];

                    map = L.map(picker, { zoomControl: true }).setView(center, 16);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
                    }).addTo(map);

                    map.on('click', function (e) {
                        latInput.value = e.latlng.lat.toFixed(7);
                        lngInput.value = e.latlng.lng.toFixed(7);
                        refresh();
                    });

                    refresh();
                    window.setTimeout(function () { map.invalidateSize(); }, 250);
                };

                modal.addEventListener('shown.bs.modal', initMap);
                latInput.addEventListener('input', refresh);
                lngInput.addEventListener('input', refresh);
                if (radiusInput) radiusInput.addEventListener('input', refresh);

                const btn = modal.querySelector('[data-lokasi-saya]');
                if (btn) {
                    btn.addEventListener('click', function () {
                        if (!navigator.geolocation) return;

                        btn.disabled = true;
                        navigator.geolocation.getCurrentPosition(function (pos) {
                            latInput.value = pos.coords.latitude.toFixed(7);
                            lngInput.value = pos.coords.longitude.toFixed(7);
                            refresh();
                            btn.disabled = false;
                        }, function () {
                            btn.disabled = false;
                        }, { enableHighAccuracy: true, timeout: 15000 });
                    });
                }

                const searchInput = modal.querySelector('[data-lokasi-cari]');
                const searchBtn = modal.querySelector('[data-lokasi-cari-btn]');
                const resultsBox = modal.querySelector('.lokasi-results');
                const pakaiGoogle = modal.querySelector('#lokasi-pakai-google');

                if (searchInput && searchBtn && resultsBox) {
                    const geocodeUrl = '{{ route("admin.absensi.lokasi.geocode") }}';
                    const token = '{{ csrf_token() }}';
                    let timeout = null;

                    const selectResult = function (r) {
                        const lat = parseFloat(r.lat);
                        const lng = parseFloat(r.lon);

                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            latInput.value = lat.toFixed(7);
                            lngInput.value = lng.toFixed(7);
                        }

                        resultsBox.innerHTML = '';
                        if (map) {
                            map.setView([lat, lng], 17);
                        }
                        refresh();
                    };

                    const callGeocode = function (query) {
                        if (!query) {
                            resultsBox.innerHTML = '';
                            return;
                        }

                        searchBtn.disabled = true;
                        resultsBox.innerHTML = '<div class="lokasi-loading"><span class="spinner-border spinner-border-sm me-2"></span> Mencari...</div>';

                        fetch(geocodeUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({
                                q: query,
                                driver: pakaiGoogle ? pakaiGoogle.checked : null,
                            }),
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (data) {
                                resultsBox.innerHTML = '';

                                const results = data && data.results ? data.results : [];
                                if (!results.length) {
                                    const empty = document.createElement('div');
                                    empty.className = 'lokasi-empty';
                                    empty.innerHTML = '<i class="bi bi-search me-1"></i> Tidak ditemukan. Klik peta atau gunakan tombol GPS untuk menetapkan titik.';

                                    if (pakaiGoogle && !data.results) {
                                        const note = document.createElement('div');
                                        note.className = 'lokasi-note';
                                        note.innerHTML = '<small>Nama perusahaan tidak ditemukan di peta. Salin koordinat titiknya dari <a href="https://www.google.com/maps" target="_blank" rel="noopener">Google Maps</a>, lalu isi Latitude / Longitude.</small>';
                                        empty.appendChild(note);
                                    }

                                    resultsBox.appendChild(empty);
                                    return;
                                }

                                const ul = document.createElement('ul');
                                ul.className = 'lokasi-result-list';

                                for (const r of results) {
                                    const li = document.createElement('li');
                                    li.className = 'lokasi-result-item';
                                    li.tabIndex = -1;

                                    const name = document.createElement('div');
                                    name.className = 'lokasi-result-name';
                                    name.textContent = r.label;

                                    const detail = document.createElement('div');
                                    detail.className = 'lokasi-result-detail';
                                    detail.textContent = r.detail;

                                    const tag = document.createElement('span');
                                    tag.className = 'lokasi-result-type';
                                    tag.textContent = r.type;

                                    li.appendChild(name);
                                    li.appendChild(detail);
                                    li.appendChild(tag);
                                    li.dataset.lat = r.lat;
                                    li.dataset.lng = r.lon;

                                    li.addEventListener('click', function () {
                                        selectResult(r);
                                    });

                                    ul.appendChild(li);
                                }

                                resultsBox.appendChild(ul);

                                const active = resultsBox.querySelector('.lokasi-result-item');
                                if (active) {
                                    active.setAttribute('tabindex', '0');
                                    active.focus();
                                }
                            })
                            .catch(function () {
                                resultsBox.innerHTML = '<div class="lokasi-empty"><i class="bi bi-exclamation-triangle me-1"></i> Gagal terhubung. Coba sekali lagi.</div>';
                            })
                            .finally(function () {
                                searchBtn.disabled = false;
                            });
                    };

                    searchBtn.addEventListener('click', function () { callGeocode(searchInput.value.trim()); });
                    searchInput.addEventListener('input', function (event) {
                        const query = event.target.value.trim();

                        if (timeout) {
                            clearTimeout(timeout);
                        }

                        if (query.length < 2) {
                            resultsBox.innerHTML = '';
                            return;
                        }

                        timeout = setTimeout(function () { callGeocode(query); }, 250);
                    });

                    searchInput.addEventListener('keydown', function (event) {
                        const items = resultsBox.querySelectorAll('.lokasi-result-item');
                        if (!items.length) return;

                        let active = null;
                        items.forEach(function (el) {
                            if (el.getAttribute('tabindex') === '0') {
                                active = el;
                            }
                        });

                        if (event.key === 'Enter') {
                            if (active) {
                                event.preventDefault();
                                selectResult({ lat: active.dataset.lat, lng: active.dataset.lng });
                                resultsBox.innerHTML = '';
                            }
                        } else if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            const next = active ? active.nextElementSibling : items[0];
                            if (next) {
                                items.forEach(function (el) { el.removeAttribute('tabindex'); });
                                next.setAttribute('tabindex', '0');
                                next.focus();
                            }
                        } else if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            const prev = active ? active.previousElementSibling : null;
                            if (prev) {
                                items.forEach(function (el) { el.removeAttribute('tabindex'); });
                                prev.setAttribute('tabindex', '0');
                                prev.focus();
                            }
                        }
                    });

                    resultsBox.addEventListener('click', function (event) {
                        const item = event.target.closest('.lokasi-result-item');
                        if (!item) return;
                        selectResult({ lat: item.dataset.lat, lng: item.dataset.lng });
                        resultsBox.innerHTML = '';
                    });
                }
            });
        };

        start();
    })();
</script>
@endpush
