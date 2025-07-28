<x-layout documentTitle="Gestione Report Gara">
    <main class="container mt-5 pt-5">
        <h1 class="mb-4">
            Gestione Report per la Gara {{$race->name}} del
            {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F Y')) }}
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
                <button type="submit" class="btn btn-warning">Conferma Tutti i Record</button>
            </form>
        @endif

        @if ($records->where('confirmed', true)->isEmpty())
            <form method="POST" action="{{ route('records.store', $race) }}" enctype="multipart/form-data"
                class="mb-5">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <input type="hidden" name="race_id" value="{{ $race->id }}">

                <table class="table table-bordered">
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
                            <th>Diaria Spec.</th>
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
                                        <input type="{{ $field === 'rate_documented' ? 'text' : 'number' }}"
                                            step="any" name="{{ $field }}" class="form-control"
                                            value="{{ old($field) }}" />
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                <div class="mb-3">
                    <label for="description" class="form-label">Note</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-9">
                        <label for="attachments" class="form-label">Allegati (PDF, immagini...)</label>
                        <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success w-100 mt-2">Salva</button>
                    </div>
                </div>
            </form>
        @endif

        <table class="table table-bordered mt-4">
            <thead class="table-light">
                <tr>
                    <th rowspan="2">Operatore</th>
                    <th rowspan="2">Servizio Giornaliero</th>
                    <th rowspan="2">Servizio Speciale</th>
                    <th rowspan="2">Tariffa</th>
                    <th rowspan="2">Km</th>
                    <th rowspan="2">€ Km (0.36)</th>
                    <th colspan="4" class="text-center">Spesa Documentata</th>
                    <th colspan="3" class="text-center">Spesa NON Documentata</th>
                    <th rowspan="2">Totale</th>
                    <th rowspan="2">Descrizione</th>
                    <th rowspan="2">Allegati</th>
                    <th rowspan="2">Azioni</th>
                </tr>
                <tr>
                    <th>Biglietto</th>
                    <th>Vitto</th>
                    <th>Alloggio</th>
                    <th>Varie</th>
                    <th>Vitto</th>
                    <th>Diaria</th>
                    <th>Diaria Spec.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    @php
                        $user = auth()->user();
                        $isLeader = $user->isLeaderOf($race);
                        $isOwner = $user->id === $record->user_id;
                        $canSee = $isOwner || $isLeader;
                        $canEdit = $isOwner && !$record->confirmed;
                        $leaderRecord = $isLeader && !$isOwner ? $records->firstWhere('user_id', $user->id) : null;
                        $km = $isOwner ? $record->km_documented : $leaderRecord->km_documented ?? null;
                        $amount = $km ? round($km * 0.36, 2) : 0;
                        $total =
                            $amount +
                            ($record->travel_ticket_documented ?? 0) +
                            ($record->food_documented ?? 0) +
                            ($record->accommodation_documented ?? 0) +
                            ($record->various_documented ?? 0) +
                            ($record->food_not_documented ?? 0) +
                            ($record->daily_allowances_not_documented ?? 0) +
                            ($record->special_daily_allowances_not_documented ?? 0);
                    @endphp
                    @if ($canSee)
                        <tr>
                            <td>{{ $record->user->name }} {{ $record->user->surname }}</td>
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
                                                <a href="{{ route('attachments.show', $attachment) }}" target="_blank">
                                                    {{ $attachment->original_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <em>Nessuno</em>
                                @endif
                            </td>
                            <td>
                                @if ($canEdit)
                                    <form action="{{ route('records.destroy', $record) }}" method="POST"
                                        onsubmit="return confirm('Sei sicuro di voler eliminare?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Elimina</button>
                                    </form>
                                @elseif ($record->confirmed)
                                    <span class="badge bg-success">Confermato</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </main>
</x-layout>
