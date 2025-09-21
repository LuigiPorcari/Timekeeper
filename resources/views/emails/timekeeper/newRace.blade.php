<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Inserimento nuova gara</title>
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
            max-width: 600px;
            background-color: #ffffff;
            margin: 30px auto;
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            font-size: 24px;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        a.button {
            display: inline-block;
            margin-top: 20px;
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
        }
    </style>
</head>

<body>
    <div class="container" role="main">
        <h1>Inserimento nuova Gara</h1>
        <p>La gara <strong>{{ $raceName }}</strong> è stata aggiunta e si terrà
            @if ($raceStart != $raceEnd)
                dal {{ \Carbon\Carbon::parse($raceStart)->translatedFormat('l j F') }}
                al {{ \Carbon\Carbon::parse($raceEnd)->translatedFormat('l j F') }}
            @else
                il {{ \Carbon\Carbon::parse($raceStart)->translatedFormat('l j F') }}
            @endif
        </p>
        <p>Cordiali saluti</p>
        <a href="{{ route('homepage') }}" class="button">Visita il sito</a>
        <div class="footer">
            © {{ date('Y') }} TimeKeeper. Tutti i diritti riservati.
        </div>
    </div>
</body>

</html>
