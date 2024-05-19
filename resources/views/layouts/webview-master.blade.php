<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
    <title>@yield('title')</title>
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/web-view-style.css') }}">
    <!-- JS -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.lazy.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
</head>
<body>
<div class="container">
    @yield('content')
</div>
