<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head-html')
</head>
<body>
<div id="app">
    @include('partials.header_bar')

    <main class="py-4">
        @yield('content')
    </main>
</div>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
@include('partials.footer')
</body>
</html>
