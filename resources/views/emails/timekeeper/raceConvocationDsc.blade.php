<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Convocazione Gara – DSC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 700px;
            background: #fff;
            margin: 30px auto;
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }

        .header-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        h1 {
            color: #007bff;
            font-size: 22px;
            text-transform: uppercase;
            margin: 0 0 10px;
        }

        h2 {
            color: #007bff;
            font-size: 16px;
            margin: 20px 0 6px;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }

        strong {
            color: #000;
        }

        ul {
            margin: 8px 0 0 18px;
            padding: 0;
        }

        li {
            margin: 6px 0;
        }

        .signature {
            margin-top: 34px;
            line-height: 1.6;
        }

        .signature strong {
            display: block;
            margin-top: 12px;
        }

        a.button {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 24px;
            background: #007bff;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        a.button:hover {
            opacity: .9;
        }

        .footer {
            margin-top: 26px;
            font-size: 12px;
            color: #777;
            text-align: center;
            border-top: 1px solid #eaeaea;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container" role="main">

        <div class="header-info">
            <strong>FEDERAZIONE ITALIANA CRONOMETRISTI</strong><br>
            ASSOCIAZIONE CRONOMETRISTI SPORTIVI TORINO<br>
            Email: {{ $contactEmail ?? '[inserire email]' }} | Telefono: {{ $contactPhone ?? '[inserire numero]' }}
        </div>

        <h1>Convocazione DSC – Servizio Gara</h1>

        <p><strong>Oggetto:</strong> Convocazione Direttore Servizi Cronometraggio –
            <strong>{{ $raceName }}</strong> /
            @if (!empty($raceStart) && !empty($raceEnd) && $raceStart !== $raceEnd)
                dal <strong>{{ \Carbon\Carbon::parse($raceStart)->format('d/m/Y') }}</strong>
                al <strong>{{ \Carbon\Carbon::parse($raceEnd)->format('d/m/Y') }}</strong>
            @else
                <strong>{{ \Carbon\Carbon::parse($raceStart ?? now())->format('d/m/Y') }}</strong>
            @endif
        </p>

        <p>Gentile Cronometrista,</p>

        <p>
            con la presente sei convocato in qualità di
            <strong>Direttore Servizi Cronometraggio (DSC)</strong> per la manifestazione
            <strong>{{ $raceName }}</strong>, che si terrà
            @if (!empty($raceStart) && !empty($raceEnd) && $raceStart !== $raceEnd)
                dal <strong>{{ \Carbon\Carbon::parse($raceStart)->translatedFormat('l j F') }}</strong>
                al <strong>{{ \Carbon\Carbon::parse($raceEnd)->translatedFormat('l j F') }}</strong>
            @else
                il <strong>{{ \Carbon\Carbon::parse($raceStart ?? now())->translatedFormat('l j F') }}</strong>
            @endif
            presso <strong>{{ $racePlace ?? '[luogo]' }}</strong>.
        </p>

        <h2>Dettagli organizzativi</h2>
        <ul>
            <li><strong>Ritrovo:</strong> {{ $meetInfo ?? '[ora e luogo di convocazione]' }}</li>
            <li><strong>Inizio gara:</strong> {{ $raceStartTime ?? '[ora]' }}</li>
            <li><strong>Squadra di servizio:</strong> {{ $teamInfo ?? '[elenco o da definire]' }}</li>
        </ul>

        <p>
            In qualità di DSC, sarai responsabile del corretto svolgimento del servizio di cronometraggio
            e della verifica dei report e dei dati di gara.
            Si raccomanda la massima puntualità e l’utilizzo dell’abbigliamento federale.
        </p>

        <p>
            Ti preghiamo di confermare la tua disponibilità entro il
            <strong>{{ $deadlineConfirm ?? '[termine]' }}</strong>
            @if (!empty($replyEmail))
                scrivendo a <a href="mailto:{{ $replyEmail }}">{{ $replyEmail }}</a>.
            @else
                rispondendo alla presente email.
            @endif
        </p>

        <div class="signature">
            <p>Cordiali saluti,</p>
            <p>{{ $placeToday ?? 'Torino' }}, {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
            <strong>Il Responsabile Servizi / Delegato</strong>
            <p>_____________________________<br>(Nome e Cognome)</p>
        </div>

        <a href="{{ route('homepage') }}" class="button">Visita il sito</a>

        <div class="footer">
            © {{ date('Y') }} Associazione Cronometristi Sportivi Torino – Tutti i diritti riservati.
        </div>
    </div>
</body>

</html>
