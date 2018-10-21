<div id="divLoading"></div>

@yield('scripts')
<script src="{{asset('js/app.js')}}"></script>
@if(isset($loadMainJS) && $loadMainJS)
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@endif

