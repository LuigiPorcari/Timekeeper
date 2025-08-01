<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Reimposta Password</title>
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
        <h1>Reimposta la tua Password</h1>
        <p>Hai ricevuto questa email perché è stata richiesta la reimpostazione della password per il tuo account.</p>
        <p>Per procedere, fai clic sul pulsante qui sotto:</p>
        <a href="{{ $url }}" class="button">Reimposta Password</a>
        <p>Se non hai richiesto questa modifica, puoi ignorare questa email. Nessuna azione sarà intrapresa.</p>
        <div class="footer">
            © {{ date('Y') }} TimeKeeper. Tutti i diritti riservati.
        </div>
    </div>
</body>

</html>
