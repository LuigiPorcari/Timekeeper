<x-layout documentTitle="Report Record Cronometrista">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Report di {{ $user->name }} {{ $user->surname }}</h1>

        @forelse ($races as $race)
            <div class="mb-4">
                <h5 class="fw-bold text-primary">
                    Gara del {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                    a {{ $race->place }}
                </h5>

                @if ($race->records->isEmpty())
                    <p class="text-muted">Nessun record inserito per questa gara.</p>
                @else
                    <div class="card card-body border shadow-sm">
                        <ul class="list-group list-group-flush">
                            @foreach ($race->records as $record)
                                <li class="list-group-item">{{ $record->description }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-muted">Il cronometrista non è assegnato a nessuna gara.</p>
        @endforelse
    </div>
</x-layout>
