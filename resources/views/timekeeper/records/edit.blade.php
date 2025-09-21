<x-layout documentTitle="Modifica Record">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="edit-record-title">

        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                <h1 id="edit-record-title" class="mb-4">Modifica Record</h1>

                {{-- Messaggi validazione --}}
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert" aria-live="assertive">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $race = $record->race;
                    $isLeader = auth()->user()->isLeaderOf($race);
                    // elenco campi come nella pagina di inserimento
                    $fields = [
                        'daily_service',
                        'special_service',
                        'rate_documented',
                        'km_documented',
                        'travel_ticket_documented',
                        'food_documented',
                        'accommodation_documented',
                        'various_documented',
                        'food_not_documented',
                        'daily_allowances_not_documented',
                        'special_daily_allowances_not_documented',
                    ];
                @endphp

                <div class="card tk-card">
                    <div class="card-header tk-card-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            <span>Dettagli record</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('records.update', $record) }}"
                            enctype="multipart/form-data" aria-describedby="edit-form-desc">
                            @csrf
                            @method('PUT')

                            <p id="edit-form-desc" class="visually-hidden">
                                Modulo per modificare un record di rendicontazione. Alcuni campi possono essere
                                riservati al DSC.
                            </p>

                            <input type="hidden" name="user_id" value="{{ $record->user_id }}">
                            <input type="hidden" name="race_id" value="{{ $race->id }}">

                            {{-- NUOVI CAMPI: Tipo + €/Km --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-3">
                                    <label for="type" class="form-label">Tipo *</label>
                                    <select id="type" name="type"
                                        class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="FC"
                                            {{ old('type', $record->type) === 'FC' ? 'selected' : '' }}>FC — Fuori città
                                        </option>
                                        <option value="CM"
                                            {{ old('type', $record->type) === 'CM' ? 'selected' : '' }}>CM — Comunale
                                        </option>
                                        <option value="CP"
                                            {{ old('type', $record->type) === 'CP' ? 'selected' : '' }}>CP — Provinciale
                                        </option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-3">
                                    <label for="euroKM" class="form-label">€/Km</label>
                                    <input type="text" id="euroKM" name="euroKM" inputmode="decimal"
                                        pattern="^\d{1,6}([,.]\d{1,2})?$"
                                        class="form-control @error('euroKM') is-invalid @enderror"
                                        value="{{ old('euroKM', isset($record->euroKM) ? str_replace('.', ',', (string) $record->euroKM) : '') }}"
                                        placeholder="es. 0,36">
                                    <small class="text-muted">Puoi usare virgola o punto (max 2 decimali).</small>
                                    @error('euroKM')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-3 table-vertical-separators">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2">Servizio Giornaliero</th>
                                            <th rowspan="2">Servizio Speciale</th>
                                            <th rowspan="2">Tariffa</th>
                                            <th rowspan="2">Km</th>
                                            <th colspan="4" class="text-center">Spesa Documentata</th>
                                            <th colspan="3" class="text-center">Spesa NON Documentata</th>
                                        </tr>
                                        <tr>
                                            <th>Biglietto</th>
                                            <th>Vitto</th>
                                            <th>Alloggio</th>
                                            <th>Varie</th>
                                            <th>Vitto</th>
                                            <th>Diaria</th>
                                            <th>Diaria Speciale</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @foreach ($fields as $field)
                                                @php
                                                    $value = old($field, $record->{$field});
                                                    $isRate = $field === 'rate_documented';
                                                    $isKm = $field === 'km_documented';
                                                    $type = $isRate ? 'text' : 'number';
                                                    $step = 'any';
                                                @endphp

                                                {{-- km_documented modificabile SOLO dal DSC --}}
                                                @if ($isKm && !$isLeader)
                                                    <td>
                                                        <input type="number" class="form-control"
                                                            value="{{ $value }}" disabled aria-disabled="true">
                                                        <input type="hidden" name="{{ $field }}"
                                                            value="{{ $value }}">
                                                    </td>
                                                @else
                                                    <td>
                                                        <input type="{{ $type }}" step="{{ $step }}"
                                                            name="{{ $field }}" class="form-control"
                                                            value="{{ $value }}" />
                                                    </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Note</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $record->description) }}</textarea>
                            </div>

                            {{-- Allegati: puoi caricarne di nuovi in aggiunta a quelli esistenti --}}
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Nuovi allegati (facoltativi)</label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control"
                                    multiple>
                                @if ($record->attachments && $record->attachments->count())
                                    <small class="text-muted d-block mt-1">Allegati esistenti:</small>
                                    <ul class="mb-0">
                                        @foreach ($record->attachments as $att)
                                            <li>
                                                <a href="{{ route('attachments.show', $att) }}" target="_blank"
                                                    rel="noopener">
                                                    {{ $att->original_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                                <a href="{{ route('records.manage', $race) }}" class="btn btn-secondary">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </main>
</x-layout>
