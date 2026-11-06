@extends('layouts.peserta')

@section('title', 'Absensi Harian')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Absensi Hari Ini</h3>
                        <p class="section-sub">{{ now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>

                @if ($today)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted-2" style="font-size:.82rem">Status</span>
                        <span class="badge-soft {{ $today->status_color }}">{{ $today->status_label }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size:.82rem">
                        <span class="text-muted-2">Jam Masuk</span>
                        <span class="fw-semibold">{{ $today->check_in_at?->translatedFormat('H:i:s') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size:.82rem">
                        <span class="text-muted-2">Jam Keluar</span>
                        <span class="fw-semibold">{{ $today->check_out_at?->translatedFormat('H:i:s') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size:.82rem">
                        <span class="text-muted-2">Metode</span>
                        <span class="fw-semibold">{{ $today->method_label }}</span>
                    </div>
                    @if ($today->distance_meters !== null)
                        <div class="d-flex justify-content-between mb-2" style="font-size:.82rem">
                            <span class="text-muted-2">Jarak dari titik</span>
                            <span class="fw-semibold">{{ round($today->distance_meters) }} m</span>
                        </div>
                    @endif
                    @if ($today->accuracy_meters !== null)
                        <div class="d-flex justify-content-between mb-2" style="font-size:.82rem">
                            <span class="text-muted-2">Akurasi GPS</span>
                            <span class="fw-semibold">{{ round($today->accuracy_meters) }} m</span>
                        </div>
                    @endif
                    @if ($today->notes)
                        <div class="mt-2 text-muted-2" style="font-size:.78rem">Catatan: {{ $today->notes }}</div>
                    @endif
                @else
                    <div class="empty-state py-4">
                        <i class="bi bi-geo-alt"></i>
                        Belum ada absensi hari ini.
                    </div>
                @endif

                <div class="mt-3" style="border-radius:14px;background:#f8fafc;border:1px solid var(--line);padding:12px;font-size:.8rem">
                    <div class="fw-semibold mb-2" style="font-size:.8rem">Jadwal Presensi</div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted-2">Jam Masuk</span>
                        <span class="fw-semibold">{{ $policy?->service_start ? substr((string) $policy->service_start, 0, 5) : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted-2">Jam Keluar</span>
                        <span class="fw-semibold">{{ $policy?->service_end ? substr((string) $policy->service_end, 0, 5) : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted-2">Titik presensi</span>
                        <span class="fw-semibold">{{ $location?->name ?? 'Belum diatur' }}</span>
                    </div>
                    @if ($location)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted-2">Radius</span>
                            <span class="fw-semibold">{{ $location->radius_meters }} meter</span>
                        </div>
                    @endif
                    @if ($session)
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted-2">Jendela presensi</span>
                            <span class="fw-semibold">{{ $session->starts_at?->format('H:i') ?? '00:00' }} &ndash; {{ $session->ends_at?->format('H:i') ?? '23:59' }}</span>
                        </div>
                    @endif
                </div>

                <div id="dailyAlert" class="alert d-none mt-3 mb-0"></div>

                <div class="d-grid gap-2 mt-3">
                    @if (! $today || ! $today->is_present)
                        <button type="button" class="btn btn-gradient" id="dailyCheckinBtn" data-url="{{ route('peserta.absensi.checkin') }}">
                            <i class="bi bi-geo-alt me-1"></i> Check-in Sekarang
                        </button>
                    @endif
                    @if ($today?->is_present && ! $today->check_out_at)
                        <button type="button" class="btn btn-soft" id="dailyCheckoutBtn" data-url="{{ route('peserta.absensi.checkout') }}">
                            <i class="bi bi-box-arrow-right me-1"></i> Check-out
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div id="gpsStatus" class="mt-2 text-small" style="font-size:.78rem"></div>
    </div>
</div>
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Peta Lokasi Presensi</h3>
                        <p class="section-sub">Titik sekolah, radius presensi, dan posisi GPS Anda saat ini.</p>
                    </div>
                    <button type="button" class="btn btn-soft btn-sm" id="locateBtn">
                        <i class="bi bi-crosshair me-1"></i> Lokasi Saya
                    </button>
                </div>

                @if ($location)
                    <div id="absensiMap"
                         style="height:400px;border-radius:14px;overflow:hidden;border:1px solid var(--line);background:#eef2ff"
                         data-lat="{{ $location->latitude }}"
                         data-lng="{{ $location->longitude }}"
                         data-radius="{{ (int) $location->radius_meters }}"
                         data-name="{{ $location->name }}"></div>
                    <div id="mapInfo" class="mt-3 d-flex flex-wrap gap-3" style="font-size:.8rem"></div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-map"></i>
                        Titik lokasi presensi belum diatur oleh sekolah. Hubungi petugas untuk mengatur lokasi dan radius presensi.
                    </div>
                @endif
            </div>

            <div class="glass-card card-pad mt-4">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Riwayat Absensi</h3>
                        <p class="section-sub">30 catatan absensi harian terakhir.</p>
                    </div>
                    <span class="badge-soft info"><i class="bi bi-calendar-check"></i> {{ $history->count() }} catatan</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-center">Status</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Metode</th>
                                <th>Jarak</th>
                                <th>Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $record)
                                <tr>
                                    <td class="fw-semibold text-nowrap">{{ $record->session?->session_date?->translatedFormat('d M Y') }}</td>
                                    <td class="text-center"><span class="badge-soft {{ $record->status_color }}">{{ $record->status_label }}</span></td>
                                    <td>{{ $record->check_in_at?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $record->check_out_at?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $record->method_label }}</td>
                                    <td>{{ $record->distance_meters !== null ? round($record->distance_meters) . ' m' : '-' }}</td>
                                    <td class="text-muted-2" style="font-size:.78rem">{{ $record->location?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="bi bi-calendar-x"></i>
                                            Belum ada riwayat absensi.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
    @keyframes lokasiDenyut {
        0% { transform: scale(.6); opacity: .9; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    .user-location-pin { background: transparent; border: 0; }
    .gps-status-off { color: #dc2626; }
    .gps-status-on { color: #16a34a; }
    .gps-status-warn { color: #d97706; }
    .gps-device-mobile { color: #2563eb; font-weight: 600; }
    .gps-device-desktop { color: #4b5563; }
</style>
<script>
    (function () {
        const alertBox = document.getElementById('dailyAlert');
        const checkin = document.getElementById('dailyCheckinBtn');
        const gpsStatus = document.getElementById('gpsStatus');

        const showAlert = (message, type) => {
            if (!alertBox) return;
            alertBox.className = 'alert alert-' + type + ' mt-3 mb-0';
            alertBox.textContent = message;
        };

        const post = async (url, body) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(body || {}),
            });
            return { response, data: await response.json() };
        };

        // Detect device type
        function detectDevice() {
            const ua = navigator.userAgent || '';
            const android = ua.indexOf('Android') > -1;
            const ios = /iPhone|iPad|iPod/i.test(ua);
            const ipad = /iPad/i.test(ua);
            const mobile = android || ios || /Android|iPhone|iPad|iPod/i.test(ua);
            const isMobile = mobile && window.innerWidth < 768;
            const isTablet = android || ios || /Android|Macintosh/i.test(ua) && window.innerWidth >= 768 && window.innerWidth < 1024;
            const isDesktop = !mobile && !isTablet;
            return { isMobile, isTablet, isDesktop, ua };
        }

        // Update GPS status UI
        function updateGpsStatus(device, status, detail) {
            if (!gpsStatus) return;
            const isOn = status === 'on' || status === 'checking' || status === 'success';
            const isOff = status === 'off' || status === 'denied' || status === 'error';
            let cls = 'gps-status-' + (isOn ? 'on' : 'off');
            let label = '';
            let icon = '';

            if (device === 'mobile') {
                icon = '&#128196;';
                label = 'HP (GPS perangkat)';
            } else if (device === 'desktop') {
                icon = '&#128205;';
                label = 'Komputer';
            } else {
                icon = '&#128221;';
                label = 'Tablet';
            }

            if (isOff) {
                cls = 'gps-status-off';
                label += ' - ' + (detail || 'Aktifkan lokasi terlebih dahulu');
            } else if (status === 'checking') {
                cls = 'gps-status-warn';
                label += ' - meminta izin lokasi...';
            } else if (status === 'success') {
                cls = 'gps-status-on';
                if (detail) label += ' - ' + detail;
            } else {
                cls = 'gps-status-warn';
                label += ' - ' + (detail || 'Tersedia');
            }

            gpsStatus.innerHTML = '<span class="gps-status-' + (isOn ? 'on' : 'off') + '">' + icon + ' ' + label + '</span>';
        }

        // Get current position with auto-retry
        function getPositionWithRetry(maxRetries, retryDelay) {
            return new Promise((resolve, reject) => {
                const attempt = (retryCount) => {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                updateGpsStatus('mobile', 'success', Math.round(position.coords.accuracy) + ' m');
                                resolve(position);
                            },
                            (error) => {
                                const messages = {
                                    1: 'Izin lokasi ditolak. Aktifkan izin lokasi di browser.',
                                    2: 'Lokasi tidak tersedia. Coba area terbuka.',
                                    3: 'Waktu habis. Coba lagi.',
                                };
                                if (retryCount < maxRetries) {
                                    setTimeout(() => attempt(retryCount + 1), retryDelay);
                                } else {
                                    updateGpsStatus('mobile', 'off', messages[error.code] || 'Gagal membaca lokasi.');
                                    reject(new Error(messages[error.code] || 'Gagal membaca lokasi.'));
                                }
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                        );
                    } else {
                        updateGpsStatus('mobile', 'off', 'Perangkat tidak mendukung GPS.');
                        reject(new Error('Perangkat tidak mendukung GPS.'));
                    }
                };
                attempt(0);
            });
        }

        if (checkin) {
            checkin.addEventListener('click', async function () {
                if (!navigator.geolocation) {
                    showAlert('Perangkat ini tidak mendukung GPS. Hubungi petugas untuk absensi manual.', 'danger');
                    updateGpsStatus('mobile', 'off', 'Perangkat tidak mendukung GPS.');
                    return;
                }

                const device = detectDevice();
                updateGpsStatus(device.isMobile ? 'mobile' : 'desktop', 'checking', 'Membaca lokasi...');

                checkin.disabled = true;
                checkin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengambil lokasi...';
                showAlert('Membaca lokasi perangkat...', 'info');

                try {
                    const position = await getPositionWithRetry(2, 3000);
                    const { response, data } = await post(checkin.dataset.url, {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy_meters: position.coords.accuracy,
                    });

                    if (response.ok && data.ok) {
                        showAlert(data.message, 'success');
                        setTimeout(() => window.location.reload(), 900);
                        return;
                    }
                    showAlert(data.message || 'Check-in gagal.', 'danger');
                } catch (error) {
                    showAlert(error.message || 'Gagal menghubungi server. Periksa koneksi.', 'danger');
                } finally {
                    checkin.disabled = false;
                    checkin.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Check-in Sekarang';
                }
            });
        }

        const checkout = document.getElementById('dailyCheckoutBtn');

        if (checkout) {
            checkout.addEventListener('click', async function () {
                checkout.disabled = true;
                try {
                    const { response, data } = await post(checkout.dataset.url, {});
                    if (response.ok && data.ok) {
                        showAlert(data.message, 'success');
                        setTimeout(() => window.location.reload(), 900);
                        return;
                    }
                    showAlert(data.message || 'Check-out gagal.', 'danger');
                } catch (error) {
                    showAlert('Gagal menghubungi server. Periksa koneksi.', 'danger');
                } finally {
                    checkout.disabled = false;
                }
            });
        }

        // Map initialization
        const initPetaAbsensi = function () {
            const mapEl = document.getElementById('absensiMap');
            if (!mapEl || !window.L) return;

            const schoolLat = parseFloat(mapEl.dataset.lat);
            const schoolLng = parseFloat(mapEl.dataset.lng);
            const radius = parseFloat(mapEl.dataset.radius || '50');
            const schoolName = mapEl.dataset.name || 'Titik presensi';
            const mapInfo = document.getElementById('mapInfo');

            if (!Number.isFinite(schoolLat) || !Number.isFinite(schoolLng)) return;

            const map = L.map(mapEl, { zoomControl: true }).setView([schoolLat, schoolLng], 16);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
            }).addTo(map);

            L.circle([schoolLat, schoolLng], {
                radius: radius,
                color: '#4f46e5',
                weight: 2,
                dashArray: '6 6',
                fillColor: '#6366f1',
                fillOpacity: 0.12,
            }).addTo(map);

            L.marker([schoolLat, schoolLng]).addTo(map)
                .bindPopup('<b>' + schoolName + '</b><br>Radius presensi ' + radius + ' m');

            const userIcon = L.divIcon({
                className: 'user-location-pin',
                html: '<span style="position:relative;display:block;width:18px;height:18px">' +
                    '<span style="position:absolute;inset:0;border-radius:50%;background:#2563eb;border:3px solid #fff;box-shadow:0 0 0 2px #2563eb,0 2px 8px rgba(0,0,0,.35)"></span>' +
                    '<span style="position:absolute;inset:-12px;border-radius:50%;background:rgba(37,99,235,.25);animation:lokasiDenyut 1.8s ease-out infinite"></span>' +
                    '</span>',
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });

            let userMarker = null;
            let userCircle = null;
            let locationUpdateTimer = null;

            const formatMeter = (value) => (value >= 1000 ? (value / 1000).toFixed(2) + ' km' : Math.round(value) + ' m');

            const jarakMeter = (lat1, lng1, lat2, lng2) => {
                const toRad = (v) => (v * Math.PI) / 180;
                const dLat = toRad(lat2 - lat1);
                const dLng = toRad(lng2 - lng1);
                const a = Math.sin(dLat / 2) ** 2
                    + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
                return 2 * 6371000 * Math.asin(Math.min(1, Math.sqrt(a)));
            };

            const renderInfo = (distance, accuracy, inside) => {
                if (!mapInfo) return;
                const status = distance === null
                    ? '<span class="badge-soft neutral">Belum ada lokasi</span>'
                    : (inside
                        ? '<span class="badge-soft success"><i class="bi bi-check2-circle"></i> Dalam radius</span>'
                        : '<span class="badge-soft danger"><i class="bi bi-x-circle"></i> Di luar radius</span>');

                mapInfo.innerHTML =
                    '<div><div class="text-muted-2">Jarak dari titik</div><div class="fw-semibold">' + (distance === null ? '-' : formatMeter(distance)) + '</div></div>' +
                    '<div><div class="text-muted-2">Akurasi GPS</div><div class="fw-semibold">' + (accuracy === null ? '-' : Math.round(accuracy) + ' m') + '</div></div>' +
                    '<div><div class="text-muted-2">Radius sekolah</div><div class="fw-semibold">' + radius + ' m</div></div>' +
                    '<div><div class="text-muted-2">Status</div><div class="fw-semibold">' + status + '</div></div>';
            };

            const locate = (button) => {
                if (!navigator.geolocation) {
                    renderInfo(null, null, false);
                    if (mapInfo) mapInfo.insertAdjacentHTML('beforeend', '<div class="text-danger">Perangkat tidak mendukung GPS.</div>');
                    updateGpsStatus('desktop', 'off', 'Perangkat tidak mendukung GPS.');
                    return;
                }

                const device = detectDevice();
                updateGpsStatus(device.isMobile ? 'mobile' : 'desktop', 'checking', 'Mencari lokasi...');

                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mencari lokasi...';
                }

                navigator.geolocation.getCurrentPosition((position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;

                    if (userMarker) map.removeLayer(userMarker);
                    if (userCircle) map.removeLayer(userCircle);

                    userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                    userCircle = L.circle([lat, lng], {
                        radius: accuracy > 0 ? accuracy : 10,
                        color: '#2563eb',
                        weight: 1,
                        fillColor: '#2563eb',
                        fillOpacity: 0.15,
                    }).addTo(map);

                    const distance = jarakMeter(schoolLat, schoolLng, lat, lng);
                    renderInfo(distance, accuracy, distance <= radius);

                    map.fitBounds(L.latLngBounds([schoolLat, schoolLng], [lat, lng]).pad(0.25));

                    const deviceType = device.isMobile ? 'HP' : 'Komputer';
                    updateGpsStatus(device.isMobile ? 'mobile' : 'desktop', 'success', deviceType + ' GPS - ' + Math.round(accuracy) + ' m');

                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-crosshair me-1"></i> Lokasi Saya';
                    }
                }, (error) => {
                    const messages = {
                        1: 'Izin lokasi ditolak. Aktifkan izin lokasi lalu coba lagi.',
                        2: 'Lokasi tidak tersedia. Coba di area terbuka.',
                        3: 'Waktu pengambilan lokasi habis.',
                    };
                    renderInfo(null, null, false);
                    if (mapInfo) mapInfo.insertAdjacentHTML('beforeend', '<div class="text-danger">' + (messages[error.code] || 'Gagal membaca lokasi.') + '</div>');
                    const device = detectDevice();
                    updateGpsStatus(device.isMobile ? 'mobile' : 'desktop', 'off', messages[error.code] || 'Gagal membaca lokasi.');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-crosshair me-1"></i> Lokasi Saya';
                    }
                }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
            };

            const locateBtn = document.getElementById('locateBtn');
            if (locateBtn) locateBtn.addEventListener('click', () => locate(locateBtn));

            // Auto-update location every 10 seconds if GPS is active
            const startAutoLocation = () => {
                if (navigator.geolocation) {
                    const updateLocation = () => {
                        navigator.geolocation.getCurrentPosition((position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const accuracy = position.coords.accuracy;

                            if (userMarker) map.removeLayer(userMarker);
                            if (userCircle) map.removeLayer(userCircle);

                            userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                            userCircle = L.circle([lat, lng], {
                                radius: accuracy > 0 ? accuracy : 10,
                                color: '#2563eb',
                                weight: 1,
                                fillColor: '#2563eb',
                                fillOpacity: 0.15,
                            }).addTo(map);

                            const distance = jarakMeter(schoolLat, schoolLng, lat, lng);
                            renderInfo(distance, accuracy, distance <= radius);

                            const device = detectDevice();
                            updateGpsStatus(device.isMobile ? 'mobile' : 'desktop', 'success', device.isMobile ? 'HP GPS' : 'Komputer GPS');

                            locationUpdateTimer = setTimeout(updateLocation, 10000);
                        }, () => {
                            // Permission denied, stop auto-update
                            if (locationUpdateTimer) {
                                clearTimeout(locationUpdateTimer);
                                locationUpdateTimer = null;
                            }
                        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
                    };
                    updateLocation();
                }
            };

            renderInfo(null, null, false);
            locate(null);

            // Start auto-location only if GPS is available
            if (navigator.geolocation) {
                // Wait for first location before starting auto-update
                const firstPosition = new Promise((resolve) => {
                    navigator.geolocation.getCurrentPosition(resolve, () => resolve(null), { timeout: 5000 });
                });

                firstPosition.then((pos) => {
                    if (pos) {
                        startAutoLocation();
                    }
                });
            }
        };

        if (window.L) { initPetaAbsensi(); } else { window.addEventListener('load', initPetaAbsensi); }
    })();
</script>
@endpush
