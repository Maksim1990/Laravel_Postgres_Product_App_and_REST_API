<div id="divLoading"></div>
@if(!isset($loadMainJS))
<script src="{{asset('js/app.js')}}"></script>
@endif
@yield('scripts')


