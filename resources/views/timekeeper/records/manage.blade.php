<x-layout documentTitle="Gestione Report Gara">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Gestione Report per la Gara del
            {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F Y')) }} a {{ $race->place }}
        </h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('records.store', $race) }}" class="mb-5">
            @csrf
            <div class="mb-3">
                <label for="description" class="form-label">Nuovo Report</label>
                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Aggiungi Report</button>
        </form>

        @foreach ($records as $record)
            <div class="mb-4 p-3 border-start border-3 border-primary bg-light rounded">
                <form method="POST" action="{{ route('records.update', $record) }}" class="mb-2">
                    @csrf
                    @method('PUT')
                    <textarea name="description" class="form-control mb-2" rows="3" required>{{ $record->description }}</textarea>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Aggiorna</button>
                </form>

                <form method="POST" action="{{ route('records.destroy', $record) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                </form>
            </div>
    </div>
    @endforeach
    </div>
</x-layout>
