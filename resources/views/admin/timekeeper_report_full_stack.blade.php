<x-layout documentTitle="Report Full — {{ $user->name }} {{ $user->surname }}">
    <main class="container-fluid mt-5 pt-5">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h1 class="mb-1">Report Full (tutte le gare)</h1>
                <div class="text-muted">
                    Cronometrista: <strong>{{ $user->name }} {{ $user->surname }}</strong>
                </div>
            </div>
        </div>

        @if ($reports->isEmpty())
            <div class="alert alert-info">Nessuna gara trovata.</div>
        @else
            <div class="accordion" id="accReports">
                @foreach ($reports as $i => $data)
                    @php
                        $race = $data['race'];
                        $uid = $data['uid'];
                        $collapseId = "collapse_$uid";
                        $headingId = "heading_$uid";
                    @endphp

                    <div class="accordion-item mb-2">
                        <h2 class="accordion-header" id="{{ $headingId }}">
                            <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                {{ $race->name }} — {{ $race->place ?? '—' }}
                                ({{ \Carbon\Carbon::parse($race->date_of_race)->format('d/m/Y') }}
                                @if ($race->date_end)
                                    → {{ \Carbon\Carbon::parse($race->date_end)->format('d/m/Y') }}
                                @endif)
                            </button>
                        </h2>

                        <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                            aria-labelledby="{{ $headingId }}" data-bs-parent="#accReports">
                            <div class="accordion-body">
                                @include('secretariat.races.partials.report_full_table', $data)
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</x-layout>
