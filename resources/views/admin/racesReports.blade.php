<x-layout documentTitle="Report Gara">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Report per la Gara del
            {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
            a {{ $race->place }}
        </h1>

        @if ($records->isEmpty())
            <p class="text-muted">Nessun record disponibile per questa gara.</p>
        @else
            @foreach ($records as $record)
                <div class="card shadow-sm mb-3 border">
                    <div class="card-body">
                        <p class="mb-2">{{ $record->description }}</p>
                        <footer class="blockquote-footer mb-0 mt-1">
                            {{ $record->user->name }} {{ $record->user->surname }}
                        </footer>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-layout>
