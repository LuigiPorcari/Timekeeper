<x-layout documentTitle="Modifica Report Crono">
    <main class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">

                <h1 class="mb-4">Modifica Report Crono — {{ $race->name }}</h1>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                <div class="card tk-card">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-pen me-2"></i> Dati Crono (una volta per gara)
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('records.update', $entry) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            {{-- per tornare al giorno selezionato --}}
                            <input type="hidden" name="day" value="{{ $day }}">

                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Km</label>
                                    <input type="number" step="0.01" name="km"
                                        class="form-control @error('km') is-invalid @enderror"
                                        value="{{ old('km', $entry->km) }}">
                                    @error('km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Pedaggi / Trasporto</label>
                                    <input type="number" step="0.01" name="pedaggi"
                                        class="form-control @error('pedaggi') is-invalid @enderror"
                                        value="{{ old('pedaggi', $entry->pedaggi) }}">
                                    @error('pedaggi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Vitto</label>
                                    <input type="number" step="0.01" name="vitto"
                                        class="form-control @error('vitto') is-invalid @enderror"
                                        value="{{ old('vitto', $entry->vitto) }}">
                                    @error('vitto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Alloggio</label>
                                    <input type="number" step="0.01" name="alloggio"
                                        class="form-control @error('alloggio') is-invalid @enderror"
                                        value="{{ old('alloggio', $entry->alloggio) }}">
                                    @error('alloggio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Spese varie</label>
                                    <input type="number" step="0.01" name="spese_varie"
                                        class="form-control @error('spese_varie') is-invalid @enderror"
                                        value="{{ old('spese_varie', $entry->spese_varie) }}">
                                    @error('spese_varie')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Note</label>
                                    <textarea name="note" class="form-control" rows="3">{{ old('note', $entry->note) }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Aggiungi allegati</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Salva modifiche
                                </button>

                                <a href="{{ route('records.manage', ['race' => $race->id, 'day' => $day]) }}"
                                    class="btn btn-secondary">
                                    Annulla
                                </a>
                            </div>
                        </form>

                        @if ($entry->attachments && $entry->attachments->count())
                            <hr>
                            <h5 class="mb-2">Allegati già presenti</h5>
                            <ul class="mb-0">
                                @foreach ($entry->attachments as $a)
                                    <li>
                                        <a href="{{ asset('storage/' . $a->file_path) }}" target="_blank">
                                            {{ $a->original_name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </main>
</x-layout>
