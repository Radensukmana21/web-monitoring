@extends('main')
@section('title', 'Dashboard')
@section('breadcumb-2', 'Dashboard')
@section('breadcumb-3', 'Index')

@section('content')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Selamat Datang,</strong> {{ Auth::user()->name }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Alert Notifikasi -->
    <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

    <div class="card mb-5 mb-xl-8">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Data Layanan</span>
            </h3>
        </div>

        <div class="row">
            <!-- Diagram Batang -->
            <div class="col-lg-6 col-md-12">
                <canvas id="densityCanvas"></canvas>
            </div>

            <!-- Kalender -->
            <div class="col-lg-6 col-md-12 d-flex justify-content-center">
                <div class="calendar-input border rounded"></div>
            </div>
        </div><br><br>

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

                <style>
                    .pagination svg {
                        width: 1em;
                        height: 1em;
                    }

                    .pagination nav>div:first-child {
                        display: none;
                    }
                </style>

            @endif
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

                    // Sembunyikan modal
                    $('#loadingModal').modal('hide');


                },
                error: function (xhr) {
                    console.log('Gagal scan:', xhr.responseText);
                    $('#loadingModal').modal('hide');

                    alert('Scan gagal. Cek koneksi atau permission.');
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
        var densityCanvas = document.getElementById('densityCanvas').getContext('2d');

        const chartLabels = @json($chartData->pluck('name'));
        const chartCounts = @json($chartData->pluck('total_today'));

        var barChart = new Chart(densityCanvas, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Jumlah File Masuk Hari Ini',
                    data: chartCounts,
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
    </script>
@endpush