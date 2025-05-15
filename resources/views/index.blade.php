@extends('main')
@section('title', 'Dashboard')
@section('breadcumb-2', 'Dashboard')
@section('breadcumb-3', 'Index')

@section('content')
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Selamat Datang,</strong> {{ Auth::user()->name }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="card mb-5 mb-xl-8">
    <!--begin::Header-->
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bolder fs-3 mb-1">Data Layanan</span>
        </h3>
    </div>
    
    <div class="row">
        <!-- Diagram Batang di sebelah kiri -->
        <div class="col-lg-6 col-md-12">
            <canvas id="densityCanvas"></canvas>
        </div>

        <!-- Kalender di sebelah kanan -->
        <div class="col-lg-6 col-md-12, d-flex justify-content-center">
            <div class="calendar-input border rounded"></div>
        </div>
    </div><br><br>

   <div class="col-md-4 text-center">
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
    @endif
</div>
</div>

@endsection

@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script> <!-- Bahasa Indonesia -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const calendarData = @json($calendarData);

const datesWithData = calendarData.map(item => ({
    date: item.date,
    count: item.count
}));

flatpickr(".calendar-input", {
    inline: true,
    locale: "id",
    disableMobile: true,
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const date = dayElem.dateObj.toISOString().slice(0, 10);
        const entry = datesWithData.find(item => item.date === date);

        if (entry) {
            // Ada data
            dayElem.style.backgroundColor = '#d4edda'; // hijau
            dayElem.innerHTML += `<div style="font-size:10px;color:#155724;">+${entry.count}</div>`;
        } else {
            // Tidak ada data
            dayElem.style.backgroundColor = '#f8d7da'; // merah
            dayElem.innerHTML += `<div style="font-size:10px;color:#721c24;">0</div>`;
        }
    }
});

    // Mendapatkan elemen canvas untuk chart.js
    var densityCanvas = document.getElementById('densityCanvas').getContext('2d');

    // Data untuk grafik batang
    var densityData = {
        label: 'Density of Planets (kg/m3)',
        data: [5427, 5243, 5514, 3933, 1326, 687, 1271, 1638],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',  // Warna latar belakang
        borderColor: 'rgba(54, 162, 235, 1)',        // Warna border
        borderWidth: 1
    };

    // Opsi untuk grafik
    var chartOptions = {
        responsive: true,
        scales: {
            x: {
                beginAtZero: true
            }
        }
    };

    // Ambil data dari Laravel (convert ke JSON)
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
