<x-layout documentTitle="Modifica Record">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="edit-record-title">

        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                <h1 id="edit-record-title" class="mb-4">Modifica Record</h1>

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

                    $labels = [
                        'elaborazione_dati' => 'Elaborazione dati',
                        'elaborazione_dati_completa' => 'Elaborazione dati completa',
                        'elaborazione_dati_parziale_live' => 'Elab. dati parziale (live)',
                        'vasca' => 'Vasca',
                        'partenza' => 'Partenza',
                        'arrivo' => 'Arrivo',
                        'fotofinish' => 'Fotofinish',
                        'manuale' => 'Manuale',
                        'centro_classifica' => 'Centro classifica',
                        'tracking' => 'Tracking',
                        'start_ps' => 'Start PS',
                        'fine_ps' => 'Fine PS',
                        'controllo_orari_co' => 'Controllo orari (CO)',
                        'riordini' => 'Riordini',
                        'assistenza_partenza_arrivo' => 'Assistenza/Partenza/Arrivo',
                        'palco_premiazioni' => 'Palco premiazioni',
                        'transponder_pc' => 'Transponder – PC',
                        'solo_cronometraggio_start' => 'Solo cronometraggio: start',
                        'solo_cronometraggio_fine' => 'Solo cronometraggio: fine',
                        'co_con_pc' => 'CO con PC',
                        'co_solo_tablet' => 'CO solo tablet',
                        'partenza_prova' => 'Partenza prova',
                        'fine_prova' => 'Fine prova',
                        'arrivo_bandelle' => 'Arrivo – Bandelle',
                        'partenza_orologio_tablet' => 'Partenza con orologio/tablet',
                        'prog_spec_concorso_ippico' => 'Prog. spec. concorso ippico',
                        'utilizzo_spec_programma' => 'Utilizzo specifico programma',
                        'contagiri' => 'Contagiri',
                        'pressostati' => 'Pressostati',
                        'tablet' => 'Tablet',
                    ];
                    $nice = fn($slug) => $labels[$slug] ?? ucwords(str_replace(['_', '-'], ' ', $slug));
                    $specs = is_array($race->specialization_of_race) ? $race->specialization_of_race : [];
                    $selectedApps = old('apparecchiature', $record->apparecchiature ?? []);
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
                                Modulo per modificare un record di rendicontazione. Alcuni campi sono riservati al DSC.
                            </p>

                            {{-- Tipo + €/Km (€/Km solo DSC) --}}
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

                                @if ($isLeader)
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
                                @else
                                    <div class="col-12 col-md-3">
                                        <label class="form-label d-block">€/Km</label>
                                        <input type="text" class="form-control"
                                            value="{{ $record->euroKM !== null ? number_format($record->euroKM, 2, ',', '') : '—' }}"
                                            disabled aria-disabled="true">
                                    </div>
                                @endif

                                {{-- Trasporto (solo DSC) --}}
                                @if ($isLeader)
                                    @php $tm = old('transport_mode', $record->transport_mode ?? 'km'); @endphp
                                    <div class="col-12 col-md-6">
                                        <label class="form-label d-block">Trasporto *</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="transport_mode"
                                                    id="tm_trasp" value="trasportato"
                                                    {{ $tm === 'trasportato' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tm_trasp">Trasportato</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="transport_mode"
                                                    id="tm_km" value="km" {{ $tm === 'km' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tm_km">Numero km</label>
                                            </div>

                                            <div class="ms-3" id="kmBox">
                                                <label for="km_documented" class="form-label mb-0 me-2">Km</label>
                                                <input type="number" step="any" name="km_documented"
                                                    id="km_documented" class="form-control d-inline-block"
                                                    style="max-width: 140px"
                                                    value="{{ old('km_documented', $record->km_documented) }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- TABELLA con intestazioni raggruppate --}}
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-3 table-vertical-separators">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2">Servizio Giornaliero</th>
                                            <th rowspan="2">Servizio Speciale</th>
                                            <th rowspan="2">Tariffa</th>
                                            <th colspan="4" class="text-center">Spese Documentate</th>
                                            @if ($isLeader)
                                                <th colspan="3" class="text-center">Spese NON Documentate</th>
                                            @endif
                                        </tr>
                                        <tr>
                                            <th>Biglietto</th>
                                            <th>Vitto</th>
                                            <th>Alloggio</th>
                                            <th>Varie</th>
                                            @if ($isLeader)
                                                <th>Vitto</th>
                                                <th>Diaria</th>
                                                <th>Diaria Speciale</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="number" step="any" name="daily_service"
                                                    class="form-control"
                                                    value="{{ old('daily_service', $record->daily_service) }}"></td>
                                            <td><input type="number" step="any" name="special_service"
                                                    class="form-control"
                                                    value="{{ old('special_service', $record->special_service) }}">
                                            </td>
                                            <td><input type="text" name="rate_documented" class="form-control"
                                                    value="{{ old('rate_documented', $record->rate_documented) }}">
                                            </td>

                                            <td><input type="number" step="any" name="travel_ticket_documented"
                                                    class="form-control"
                                                    value="{{ old('travel_ticket_documented', $record->travel_ticket_documented) }}">
                                            </td>
                                            <td><input type="number" step="any" name="food_documented"
                                                    class="form-control"
                                                    value="{{ old('food_documented', $record->food_documented) }}">
                                            </td>
                                            <td><input type="number" step="any" name="accommodation_documented"
                                                    class="form-control"
                                                    value="{{ old('accommodation_documented', $record->accommodation_documented) }}">
                                            </td>
                                            <td><input type="number" step="any" name="various_documented"
                                                    class="form-control"
                                                    value="{{ old('various_documented', $record->various_documented) }}">
                                            </td>

                                            @if ($isLeader)
                                                <td><input type="number" step="any" name="food_not_documented"
                                                        class="form-control"
                                                        value="{{ old('food_not_documented', $record->food_not_documented) }}">
                                                </td>
                                                <td><input type="number" step="any"
                                                        name="daily_allowances_not_documented" class="form-control"
                                                        value="{{ old('daily_allowances_not_documented', $record->daily_allowances_not_documented) }}">
                                                </td>
                                                <td><input type="number" step="any"
                                                        name="special_daily_allowances_not_documented"
                                                        class="form-control"
                                                        value="{{ old('special_daily_allowances_not_documented', $record->special_daily_allowances_not_documented) }}">
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Apparecchiature (solo DSC) --}}
                            @if ($isLeader && !empty($specs))
                                <div class="mb-3">
                                    <label class="form-label">Apparecchiature usate in gara</label>
                                    <div class="row g-2">
                                        @foreach ($specs as $spec)
                                            <div class="col-12 col-sm-6 col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="apparecchiature[]" id="app_{{ $spec }}"
                                                        value="{{ $spec }}"
                                                        {{ in_array($spec, $selectedApps, true) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="app_{{ $spec }}">{{ $nice($spec) }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="description" class="form-label">Note</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $record->description) }}</textarea>
                            </div>

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

    @if ($isLeader)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tmKm = document.getElementById('tm_km');
                const tmTr = document.getElementById('tm_trasp');
                const box = document.getElementById('kmBox');

                function toggle() {
                    box.style.display = (tmKm && tmKm.checked) ? 'block' : 'none';
                }
                if (tmKm) tmKm.addEventListener('change', toggle);
                if (tmTr) tmTr.addEventListener('change', toggle);
                toggle();
            });
        </script>
    @endif
</x-layout>
