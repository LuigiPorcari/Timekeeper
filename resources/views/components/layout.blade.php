<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $documentTitle }}</title>

    {{-- Stili e JS compilati con Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" defer></script>

    {{-- Font Awesome --}}
    <script src="https://kit.fontawesome.com/cfb9a37921.js" crossorigin="anonymous" defer></script>
</head>

<body>
    {{-- Navbar --}}
    <x-nav />

    {{-- Contenuto principale --}}
    <main class="min-vh-100" role="main">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-footer />
</body>

</html>
