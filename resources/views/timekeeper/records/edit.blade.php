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

                @php
                    $currentVitto = old('vitto_documentato', $entry->vitto);

                    $vittoTipo = old('vitto_tipo');

                    if ($vittoTipo === null) {
                        if ($entry->vitto === null) {
                            $vittoTipo = '';
                        } elseif ((float) $entry->vitto === 15.0) {
                            $vittoTipo = 'forfettario';
                        } elseif ((float) $entry->vitto === 0.0) {
                            $vittoTipo = 'offerto';
                        } else {
                            $vittoTipo = 'documentato';
                        }
                    }

                    $vittoDocumentato = old('vitto_documentato');

                    if ($vittoDocumentato === null && $vittoTipo === 'documentato') {
                        $vittoDocumentato = $entry->vitto;
                    }
                @endphp

                <div class="card tk-card">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-pen me-2"></i> Dati Crono (una volta per gara)
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('records.update', $entry) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

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
                                    <select name="vitto_tipo"
                                        class="form-select js-vitto-tipo @error('vitto_tipo') is-invalid @enderror">
                                        <option value="">-- Seleziona --</option>
                                        <option value="forfettario"
                                            {{ $vittoTipo === 'forfettario' ? 'selected' : '' }}>
                                            Forfettario - 15€
                                        </option>
                                        <option value="offerto" {{ $vittoTipo === 'offerto' ? 'selected' : '' }}>
                                            Offerto - 0€
                                        </option>
                                        <option value="documentato"
                                            {{ $vittoTipo === 'documentato' ? 'selected' : '' }}>
                                            Documentato
                                        </option>
                                    </select>
                                    @error('vitto_tipo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4 js-vitto-documentato-wrap" style="display: none;">
                                    <label class="form-label">Importo vitto documentato</label>
                                    <input type="number" step="0.01" min="0" name="vitto_documentato"
                                        class="form-control js-vitto-documentato @error('vitto_documentato') is-invalid @enderror"
                                        value="{{ $vittoDocumentato }}">
                                    @error('vitto_documentato')
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('.js-vitto-tipo');
            const wrapper = document.querySelector('.js-vitto-documentato-wrap');
            const input = document.querySelector('.js-vitto-documentato');

            function toggleVittoDocumentato() {
                if (!select || !wrapper || !input) {
                    return;
                }

                const isDocumentato = select.value === 'documentato';

                wrapper.style.display = isDocumentato ? 'block' : 'none';
                input.required = isDocumentato;

                if (!isDocumentato) {
                    input.value = '';
                }
            }

            if (select) {
                select.addEventListener('change', toggleVittoDocumentato);
                toggleVittoDocumentato();
            }
        });
    </script>
</x-layout>
