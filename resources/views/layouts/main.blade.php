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
@include('partials.footer')
</body>
</html>
