{{-- resources/views/secretariat/races/report_full.blade.php --}}

<x-layout documentTitle="Report Completo">
    <main class="container-fluid mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    @if (Auth::user()->is_segretarian)
                    <a href="{{ route('secretariat.races.reportFullExcel', $race) }}" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Esporta Excel (Prima Nota)
                    </a>
                    @endif

                    <div>
                        <h1 class="mb-1">Report Completo</h1>
                        <div class="text-muted">
                            Gara: <strong>{{ $race->name }}</strong>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        {{-- <button class="btn btn-outline-secondary" type="button" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Stampa
                        </button> --}}

                        {{-- Back url: se non passato, fallback segreteria --}}
                        {{-- <a href="{{ $backUrl ?? route('secretariat.races.report', ['race' => $race->id]) }}"
                            class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i> Torna indietro
                        </a> --}}
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                {{-- Qui includo la tabella riutilizzabile --}}
                @include('secretariat.races.partials.report_full_table', [
                    'race' => $race,
                    'raceDaysCount' => $raceDaysCount,
                    'days' => $days,
                    'dscRace' => $dscRace,
                    'settings' => $settings,
                    'rows' => $rows,
                    'backUrl' => $backUrl ?? null,
                    'uid' => $uid ?? 'single_' . $race->id,
                ])

            </div>
        </div>
    </main>
</x-layout>
