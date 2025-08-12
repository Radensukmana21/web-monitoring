@extends('main')
@section('title', 'Daftar Wilayah & Mitra')
@section('breadcumb-2', 'Monitoring')
@section('breadcumb-3', $title)

@section('content')
    <!-- <h4 class="mb-4">{{ $label }}</h4> -->
<form method="GET" action="{{ url()->current() }}" class="row g-2 mb-4">
    <div class="col-auto">
        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control" placeholder="Tanggal Awal">
    </div>
    
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
    <div class="col-auto">
        <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

    <div class="accordion" id="regionAccordion">
        @foreach ($regions as $region)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $region->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $region->id }}" aria-expanded="false"
                        aria-controls="collapse{{ $region->id }}">
                        {{ $region->name }}
                        @if($region->file_count > 0)
                            <span class="badge bg-danger ms-3">{{ $region->file_count }}</span>
                        @endif
                    </button>
                </h2>
                <div id="collapse{{ $region->id }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $region->id }}" data-bs-parent="#regionAccordion">
                    <div class="accordion-body">
                        <ul class="list-group">
                            @foreach ($region->partners as $partner)
                                @if($partner->file_count > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $partner->name }}</span>
                                        <span class="badge bg-primary">{{ $partner->file_count }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $regions->links('pagination::bootstrap-5') }}
    </div>

@endsection