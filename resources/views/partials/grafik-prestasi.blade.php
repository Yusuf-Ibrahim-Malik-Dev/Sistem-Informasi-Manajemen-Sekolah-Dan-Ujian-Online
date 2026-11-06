@php
    $chartId = $chartId ?? 'grafikPrestasi';
    $chartTitle = $chartTitle ?? 'Grafik Prestasi per Kelas';
    $chartDescription = $chartDescription ?? 'Rata-rata nilai raport peserta berdasarkan jenjang dan kelas.';
    $jenjangAktif = array_key_first($chartPrestasi);
@endphp

<div class="glass-card card-pad mb-4" data-aos="fade-up">
    <div class="section-head flex-wrap gap-2">
        <div>
            <h3 class="section-title">{{ $chartTitle }}</h3>
            <p class="section-sub">{{ $chartDescription }}</p>
        </div>
        <div class="d-flex flex-wrap gap-1" role="tablist">
            @foreach ($chartPrestasi as $kode => $data)
                <button type="button"
                        class="btn btn-sm {{ $kode === $jenjangAktif ? 'btn-gradient' : 'btn-ghost' }}"
                        data-chart-tab="{{ $chartId }}"
                        data-jenjang="{{ $kode }}">
                    {{ $data['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div style="height:320px">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const id = @json($chartId);
            const dataJenjang = @json($chartPrestasi);
            const canvas = document.getElementById(id);

            if (!canvas || !window.Chart) {
                return;
            }

            let aktif = @json($jenjangAktif);

            const chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: dataJenjang[aktif].labels,
                    datasets: [{
                        label: 'Rata-rata nilai ' + dataJenjang[aktif].label,
                        data: dataJenjang[aktif].nilai,
                        backgroundColor: 'rgba(99, 102, 241, .72)',
                        hoverBackgroundColor: 'rgba(79, 70, 229, .9)',
                        borderRadius: 8,
                        maxBarThickness: 54,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 450 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return 'Rata-rata: ' + context.parsed.y.toFixed(1);
                                },
                                afterLabel: function (context) {
                                    return 'Nilai raport: ' + dataJenjang[aktif].jumlah[context.dataIndex];
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { stepSize: 20 },
                            grid: { color: 'rgba(148, 163, 184, .16)' },
                        },
                        x: {
                            grid: { display: false },
                        },
                    },
                },
            });

            document.querySelectorAll('[data-chart-tab="' + id + '"]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    aktif = tombol.dataset.jenjang;

                    document.querySelectorAll('[data-chart-tab="' + id + '"]').forEach(function (item) {
                        item.classList.toggle('btn-gradient', item === tombol);
                        item.classList.toggle('btn-ghost', item !== tombol);
                    });

                    chart.data.labels = dataJenjang[aktif].labels;
                    chart.data.datasets[0].data = dataJenjang[aktif].nilai;
                    chart.data.datasets[0].label = 'Rata-rata nilai ' + dataJenjang[aktif].label;
                    chart.update();
                });
            });
        });
    </script>
@endpush
