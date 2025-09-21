{{-- resources/views/secretariat/racesTemp/index.blade.php --}}
<x-layout documentTitle="Temp Races List">
    <main class="container mt-5 pt-5" id="main-content">
        <h1 class="mb-4">Gare temporanee</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        @endif

        <div class="card shadow-sm rounded-4">
            <div class="card-body p-3">
                <table
                    class="table table-striped table-hover align-middle table-bordered table-border-black table-fixed mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Gara</th>
                            <th>Tipo</th>
                            <th>Periodo</th>
                            <th>Luogo</th>
                            <th>Ente fatturazione</th>
                            <th>Allegato</th>
                            <th class="text-center">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($racesTemp as $race)
                            @php
                                $start = $race->date_of_race
                                    ? \Illuminate\Support\Carbon::parse($race->date_of_race)->format('d/m/Y')
                                    : null;
                                $end = $race->date_end
                                    ? \Illuminate\Support\Carbon::parse($race->date_end)->format('d/m/Y')
                                    : null;
                                // periodo su due righe se c'è una data di fine
$periodo = $start ? ($end ? $start . '<br>' . $end : $start) : '—';

$allegatoUrl = null;
if (
    !empty($race->programma_allegato) &&
    \Illuminate\Support\Facades\Storage::disk('public')->exists(
                                        $race->programma_allegato,
                                    )
                                ) {
                                    $allegatoUrl = \Illuminate\Support\Facades\Storage::url($race->programma_allegato);
                                }
                            @endphp
                            <tr>
                                <td class="fw-medium">{{ $race->name }}</td>
                                <td>{{ $race->type ?? '—' }}</td>
                                <td>{!! $periodo !!}</td>
                                <td>{{ $race->place ?? '—' }}</td>
                                <td>{{ $race->ente_fatturazione ?? '—' }}</td>
                                <td>
                                    @if ($allegatoUrl)
                                        <a href="{{ $allegatoUrl }}" class="btn btn-sm btn-outline-secondary"
                                            target="_blank" rel="noopener">
                                            Visualizza
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <form method="POST" action="{{ route('race-temp.accept', $race->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">Accetta</button>
                                        </form>
                                        <form method="POST" action="{{ route('race-temp.reject', $race->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Rifiuta</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Nessuna richiesta presente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-layout>
