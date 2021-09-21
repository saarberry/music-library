<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('/img/favicon.png') }}">

    {{-- Title --}}
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- JS configuration --}}
    <script>
        window._APP_ = {
            debug: {{ (config('app.debug') ? 'true' : 'false') }},
        };
    </script>

    {{-- Scripts --}}
    <script src="{{ mix('/js/manifest.js') }}" defer></script>
    <script src="{{ mix('/js/vendor.js') }}" defer></script>
    <script src="{{ mix('/js/app.js') }}" defer></script>

    {{-- Styles --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>

<body>
    @yield('content')
</body>

</html>