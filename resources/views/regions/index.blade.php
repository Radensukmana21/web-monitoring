@extends('main')
@section('title', 'Daftar Wilayah')
@section('breadcumb-2', 'Monitoring')
@section('breadcumb-3', 'Daftar Wilayah')

@section('content')
<h4 class="mb-4">Daftar Wilayah & Mitra</h4>

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

<div class="mt-4">
    {{ $regions->links() }}
</div>
@endsection
