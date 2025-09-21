<x-layout documentTitle="Gestione Report Gara">
    <main class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                <h1 class="mb-4">
                    Gestione Report per la Gara {{ $race->name }} <br>
                    {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                    @if ($race->date_end)
                    / {{ ucwords(\Carbon\Carbon::parse($race->date_end)->translatedFormat('l d F')) }}
                    @endif
                    a {{ $race->place }}
                </h1>

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

                @if ($records->where('confirmed', false)->isNotEmpty() && auth()->user()->isLeaderOf($race))
                    <form method="POST" action="{{ route('records.confirm.all', $race) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check-double me-1"></i> Conferma Tutti i Record
                        </button>
                    </form>
                @endif

                {{-- CARD: Aggiungi record --}}
                @if ($records->isEmpty() || $records->where('confirmed', false)->isNotEmpty())
                    <div class="card tk-card mb-4">
                        <div class="card-header tk-card-header">
                            <i class="fas fa-plus-circle me-2"></i> Aggiungi record
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('records.store', $race) }}"
                                enctype="multipart/form-data" class="mb-0">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                <input type="hidden" name="race_id" value="{{ $race->id }}">

                                {{-- NUOVI CAMPI: Tipo + €/Km --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-3">
                                        <label for="type" class="form-label">Tipo *</label>
                                        <select id="type" name="type"
                                            class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>
                                                Seleziona…</option>
                                            <option value="FC" {{ old('type') === 'FC' ? 'selected' : '' }}>FC —
                                                Fuori città</option>
                                            <option value="CM" {{ old('type') === 'CM' ? 'selected' : '' }}>CM —
                                                Fisso</option>
                                            <option value="CP" {{ old('type') === 'CP' ? 'selected' : '' }}>CP —
                                                Fisso</option>
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
                                            value="{{ old('euroKM') }}" placeholder="es. 0,36">
                                        <small class="text-muted">Puoi usare virgola o punto (max 2 decimali).</small>
                                        @error('euroKM')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-vertical-separators mb-3">
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
                                                @php
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
                                                @foreach ($fields as $field)
                                                    @if ($field === 'km_documented' && !auth()->user()->isLeaderOf($race))
                                                        <td></td>
                                                    @else
                                                        <td>
                                                            <input
                                                                type="{{ $field === 'rate_documented' ? 'text' : 'number' }}"
                                                                step="any" name="{{ $field }}"
                                                                class="form-control" value="{{ old($field) }}" />
                                                        </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Note</label>
                                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Allegati (facoltativi)</label>
                                    <input type="file" name="attachments[]" id="attachments" class="form-control"
                                        multiple>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Aggiungi Record
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- CARD: tabella record --}}
                <div class="card tk-card p-3">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-list-ul me-2"></i> Record inseriti
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0 table-vertical-separators">
                                <thead class="table-light">
                                    <tr>
                                        <th>Crono</th>
                                        <th>Tipo</th>
                                        <th>€/Km</th>
                                        <th>Serv. Giorn.</th>
                                        <th>Serv. Spec.</th>
                                        <th>Tar.</th>
                                        <th>Km</th>
                                        <th>Imp. Km</th>
                                        <th>Bigl.</th>
                                        <th>Vitto (doc.)</th>
                                        <th>Alloggio (doc.)</th>
                                        <th>Varie (doc.)</th>
                                        <th>Vitto (ND)</th>
                                        <th>Diaria (ND)</th>
                                        <th>Diaria Spec. (ND)</th>
                                        <th>Totale</th>
                                        <th>Note</th>
                                        <th>Allegati</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $record)
                                        @php
                                            $user = auth()->user();
                                            $isLeader = $user->isLeaderOf($race);
                                            $isOwner = $user->id === $record->user_id;
                                            $canSee = $isOwner || $isLeader;

                                            // privacy: se non proprietario, usa i km del leader
                                            $leaderRecord =
                                                $isLeader && !$isOwner
                                                    ? $records->firstWhere('user_id', $user->id)
                                                    : null;
                                            $km = $isOwner
                                                ? $record->km_documented
                                                : $leaderRecord->km_documented ?? null;

                                            // €/Km del record (fallback 0.36)
                                            $ratePerKm = $record->euroKM !== null ? (float) $record->euroKM : 0.36;

                                            // Importo chilometrico e totale
                                            $amount = $km ? round(((float) $km) * $ratePerKm, 2) : 0.0;
                                            $total =
                                                $amount +
                                                (float) ($record->travel_ticket_documented ?? 0) +
                                                (float) ($record->food_documented ?? 0) +
                                                (float) ($record->accommodation_documented ?? 0) +
                                                (float) ($record->various_documented ?? 0) +
                                                (float) ($record->food_not_documented ?? 0) +
                                                (float) ($record->daily_allowances_not_documented ?? 0) +
                                                (float) ($record->special_daily_allowances_not_documented ?? 0);
                                        @endphp

                                        @if ($canSee)
                                            {{-- Riga dati --}}
                                            <tr>
                                                <td>{{ $record->user->name }} {{ $record->user->surname }}</td>
                                                <td>{{ $record->type ?? '—' }}</td>
                                                <td>{{ number_format($ratePerKm, 2) }}</td>
                                                <td>{{ $record->daily_service }}</td>
                                                <td>{{ $record->special_service }}</td>
                                                <td>{{ $record->rate_documented }}</td>
                                                <td>{{ $km }}</td>
                                                <td>{{ number_format($amount, 2) }}</td>
                                                <td>{{ $record->travel_ticket_documented }}</td>
                                                <td>{{ $record->food_documented }}</td>
                                                <td>{{ $record->accommodation_documented }}</td>
                                                <td>{{ $record->various_documented }}</td>
                                                <td>{{ $record->food_not_documented }}</td>
                                                <td>{{ $record->daily_allowances_not_documented }}</td>
                                                <td>{{ $record->special_daily_allowances_not_documented }}</td>
                                                <td><strong>{{ number_format($total, 2) }}</strong></td>
                                                <td>{{ $record->description }}</td>
                                                <td>
                                                    @if ($record->attachments && $record->attachments->count())
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach ($record->attachments as $attachment)
                                                                <li>
                                                                    <a href="{{ route('attachments.show', $attachment) }}"
                                                                        target="_blank">
                                                                        {{ $attachment->original_name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Riga azioni (sotto) --}}
                                            <tr class="bg-light">
                                                {{-- 18 colonne totali in testa, quindi colspan 18 --}}
                                                <td colspan="18">
                                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                                        @if (auth()->user()->isLeaderOf($race) && !$record->confirmed)
                                                            <form method="POST"
                                                                action="{{ route('records.confirm', $record) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-sm"
                                                                    onclick="return confirm('Confermare questo record?');">
                                                                    <i class="fas fa-check me-1"></i> Conferma
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @php
                                                            $canEdit =
                                                                auth()->id() === $record->user_id &&
                                                                !$record->confirmed;
                                                        @endphp

                                                        @if ($canEdit)
                                                            <a href="{{ route('records.edit', $record) }}"
                                                                class="btn btn-secondary btn-sm">
                                                                <i class="fas fa-pen me-1"></i> Modifica
                                                            </a>
                                                            <form action="{{ route('records.destroy', $record) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash me-1"></i> Elimina
                                                                </button>
                                                            </form>
                                                        @elseif ($record->confirmed)
                                                            <span class="badge bg-success align-self-center">
                                                                <i class="fas fa-check-circle me-1"></i> Confermato
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</x-layout>
