<!DOCTYPE html>
<html>
<head>
    <title>Reimposta Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
        }
        a {
            color: #ffffff !important; /* Colore del testo */
            background-color: #007bff; /* Colore di sfondo */
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            font-weight: bold;
        }
        a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reimposta Password</h1>
        <p>Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reimpostazione della password per il tuo account.</p>
        <p>Fai clic sul link qui sotto per reimpostare la tua password:</p>
        <a href="{{ $url }}">Reimposta la Password</a>
        <p>Se non hai richiesto la reimpostazione della password, ignora questa email.</p>
    </div>
</body>
</html>
{{-- <!DOCTYPE html>
<html>
<head>
    <title>Reimposta Password</title>
</head>
<body>
    <h1>Reimposta Password</h1>
    <p>Hai ricevuto questa email perché abbiamo ricevuto una richiesta di reimpostazione della password per il tuo account.</p>
    <p>Fai clic sul link qui sotto per reimpostare la tua password:</p>
    <a href="{{ $url }}">{{ $url }}</a>
    <p>Se non hai richiesto la reimpostazione della password, ignora questa email.</p>
</body>
</html> --}}
