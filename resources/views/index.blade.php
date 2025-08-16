@extends('main')
@section('title', 'Dashboard')
@section('breadcumb-2', 'Dashboard')
@section('breadcumb-3', 'Index')

@section('content')
    @if (session('welcome'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="welcomeAlert">
            <strong>{{ session('welcome') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Alert Notifikasi -->
    <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

    <div class="card mb-5 mb-xl-8">
        <!-- Diagram Batang -->
        <div class="row">
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="range" class="form-select" onchange="this.form.submit()">
                            <option value="today" {{ $range === 'today' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="7days" {{ $range === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                            <option value="30days" {{ $range === '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                            <option value="all" {{ $range === 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Grafik BRK + Kalender -->
            <div class="col-lg-8 col-md-12 mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1"> BRK</span>
                    </h3>
                </div>
                <canvas id="chartBrk" height="120"></canvas>
            </div>

            <div class="col-lg-4 col-md-12 mb-5 d-flex align-items-center justify-content-center">
                <div class="calendar-input border rounded p-3 w-400"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12 mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1"> Bengkulu</span>
                    </h3>
                </div>
                <canvas id="chartBengkulu" height="120"></canvas>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12 mb-5">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1"> Sumut</span>
                    </h3>
                </div>
                <canvas id="chartSumut" height="120"></canvas>
            </div>
        </div>


        <button class="btn btn-primary" onclick="testScan()">Scan Lokal Disk</button>

        <!-- Modal Loading -->
        <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content text-center p-4">
                    <div class="row mb-2 justify-content-center">
                        <div class="col-md-6 text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                    <div>Memindai file lokal...</div>
                </div>
            </div>
        </div><br><br>

        <!-- Notifikasi Hari Ini -->
        <div class="col-md-12 text-center">
            <span class="fw-bolder fs-3 mb-1">Notifikasi Hari Ini</span>

            @if ($regionsWithIncoming->isEmpty())
                <div class="alert alert-danger mt-4" role="alert">
                    <strong>Tidak ada data!</strong>
                </div>
            @else
                @foreach ($regionsWithIncoming as $region)
                    <div class="alert alert-success mt-3" role="alert">
                        <strong>{{ $region->name }}</strong> memiliki {{ $region->incomingFiles->count() }} file masuk hari ini.
                    </div>
                @endforeach

                <div class="d-flex justify-content-center mt-4">
                    {{ $regionsWithIncoming->links('pagination::bootstrap-5') }}
                </div>

            @endif
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .calendar-input .flatpickr-calendar {
            font-size: 20px;
            /* default 14px */
        }
    </style>
@endpush

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        setTimeout(() => {
            const alertElement = document.getElementById('welcomeAlert');
            if (alertElement) {
                // hapus class "show" biar animasi fade jalan
                alertElement.classList.remove('show');

                // tunggu durasi transisi bootstrap (150ms default), lalu benar2 close
                setTimeout(() => {
                    const alert = bootstrap.Alert.getOrCreateInstance(alertElement);
                    alert.close();
                }, 150);
            } 
        }, 3000); // delay 3 detik
    </script>
    <script>
        function testScan() {
            // Tampilkan modal loading
            $('#loadingModal').modal('show');

            $.ajax({
                url: "{{ route('scan.localdisk') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    console.log('Scan selesai:', response.message);
                    console.log(response.output);

                    $('#loadingModal').modal('hide');

                    let alertHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Scan berhasil!</strong><br>
                ${response.output.join('<br>')}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

                    $('#alert-container').html(alertHTML);
                }

            });
        }


    </script>
    <script>
        // Kalender
        const calendarData = @json($calendarData);
        const datesWithData = calendarData.map(item => ({
            date: item.date,
            count: item.count
        }));

        flatpickr(".calendar-input", {
            inline: true,
            locale: "id",
            disableMobile: true,
            onDayCreate: function (dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj.toISOString().slice(0, 10);
                const entry = datesWithData.find(item => item.date === date);

                if (entry) {
                    dayElem.style.backgroundColor = '#d4edda'; // Hijau
                    dayElem.innerHTML += `<div style="font-size:10px;color:#155724;">+${entry.count}</div>`;
                } else {
                    dayElem.style.backgroundColor = '#f8d7da'; // Merah
                    dayElem.innerHTML += `<div style="font-size:10px;color:#721c24;">0</div>`;
                }
            }
        });
        // Grafik Batang
        const chartConfigs = [
            {
                id: 'chartBrk',
                label: 'File Masuk BRK Hari Ini',
                labels: @json($chartBrk->pluck('name')),
                data: @json($chartBrk->pluck('total_today'))
            },
            {
                id: 'chartBengkulu',
                label: 'File Masuk Bengkulu Hari Ini',
                labels: @json($chartBengkulu->pluck('name')),
                data: @json($chartBengkulu->pluck('total_today'))
            },
            {
                id: 'chartSumut',
                label: 'File Masuk Sumut Hari Ini',
                labels: @json($chartSumut->pluck('name')),
                data: @json($chartSumut->pluck('total_today'))
            }
        ];

        chartConfigs.forEach(config => {
            const ctx = document.getElementById(config.id).getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: config.labels,
                    datasets: [{
                        label: config.label,
                        data: config.data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            stepSize: 1
                        }
                    }
                }
            });
        });
    </script>
@endpush