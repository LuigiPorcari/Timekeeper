<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $documentTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <x-nav />
    <div class="min-vh-100">
        {{ $slot }}
    </div>
    <x-footer />
    {{-- SCRIPT FONTAWESOME --}}
    <script src="https://kit.fontawesome.com/cfb9a37921.js" crossorigin="anonymous"></script>
</body>
</html>
