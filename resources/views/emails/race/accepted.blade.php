<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Accettazione Servizio Gara</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
        }

        .container {
            width: 100%;
            max-width: 700px;
            background-color: #ffffff;
            margin: 30px auto;
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            font-size: 22px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        h2 {
            color: #007bff;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
        }

        strong {
            color: #000;
        }

        a.button {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        a.button:hover {
            opacity: 0.9;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777777;
            text-align: center;
            border-top: 1px solid #eaeaea;
            padding-top: 10px;
        }

        .header-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .signature {
            margin-top: 40px;
            line-height: 1.6;
        }

        .signature strong {
            display: block;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container" role="main">
        <div class="header-info">
            <strong>FEDERAZIONE ITALIANA CRONOMETRISTI</strong><br>
            ASSOCIAZIONE CRONOMETRISTI SPORTIVI TORINO<br>
            Email: [inserire email ufficiale] |
            Telefono: [inserire numero di telefono]
        </div>

        <h1>Accettazione Servizio Gara</h1>

        <p><strong>Alla cortese attenzione del Comitato Organizzatore</strong></p>

        <p><strong>Oggetto:</strong> Accettazione servizio di cronometraggio –
            <strong>{{ $raceName }}</strong> /
            <strong>{{ \Carbon\Carbon::parse($raceStart)->format('d/m/Y') }}</strong>
        </p>

        <p>Gentili Organizzatori,</p>

        <p>
            Con la presente si conferma l’accettazione del servizio di cronometraggio da parte
            dell’<strong>Associazione Cronometristi Sportivi Torino</strong> per la manifestazione
            <strong>{{ $raceName }}</strong>, in programma il
            <strong>{{ \Carbon\Carbon::parse($raceStart)->format('d/m/Y') }}</strong>
            @if (!empty($raceEnd) && $raceEnd !== $raceStart)
                – <strong>{{ \Carbon\Carbon::parse($raceEnd)->format('d/m/Y') }}</strong>
            @endif
            presso <strong>{{ $racePlace ?? 'luogo da definire' }}</strong>.
        </p>

        <p>
            Il servizio sarà svolto in conformità alle normative e ai regolamenti tecnici della
            <strong>Federazione Italiana Cronometristi</strong> e secondo le modalità concordate con il
            Comitato Organizzatore.
        </p>

        <p>
            Restiamo a disposizione per eventuali dettagli organizzativi e comunicheremo a breve la
            composizione della squadra di servizio.
        </p>

        <div class="signature">
            <p>Cordiali saluti,</p>
            <p>
                Torino, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </p>
            <strong>Il Presidente</strong>
            <p>_____________________________<br>
                (Nome e Cognome)</p>
        </div>

        <a href="{{ route('homepage') }}" class="button">Visita il sito</a>

        <div class="footer">
            © {{ date('Y') }} Associazione Cronometristi Sportivi Torino – Tutti i diritti riservati.
        </div>
    </div>
</body>

</html>
