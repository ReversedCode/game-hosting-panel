<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Host de_nerdTV</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900&subset=latin" rel="stylesheet">

    <!-- Critical CSS -->
    @if(file_exists(public_path('css/landing-critical.min.css')))
        <style>
            {!! file_get_contents(public_path('css/landing-critical.min.css')) !!}
        </style>
    @else
        <link rel="stylesheet" href="{{ asset('css/landing-critical.min.css') }}">
    @endif

    <!-- Styles -->
    <link rel="preload" href="{{ mix('css/landing.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ mix('css/landing.css') }}"></noscript>
</head>
<body class="custom-gradient text-black font-sans">

@yield('content')

@stack('scripts')

@if(app()->environment('production'))
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-78711465-7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-78711465-7');
    </script>
@endif

</body>
</html>
