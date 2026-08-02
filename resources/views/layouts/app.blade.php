<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/png" href="{{ asset('images/partners_logo/DevThugs_Logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="w-full min-h-screen bg-dark-base text-white antialiased">
    @include('components.navbar')

    @yield('content')

    @include('components.footer')

    @include('components.chatbot-widget')

    @stack('scripts')
</body>
</html>
