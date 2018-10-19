@extends('layouts.main')

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Upload</div>
                    <div class="container">
                        {!! Form::open(['method'=>'POST','action'=>['AttachmentController@store','userId'=>Auth::id()],'id'=>'uploadForm', 'class'=>'dropzone'])!!}

                        {{ Form::hidden('user_id', Auth::id() ) }}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.js"></script>
    <script>
        Dropzone.options.uploadForm = {
            dataType: "json",
            success: function(file, response){
                if (response == "success") {
                    window.location.href = route('index');
                }else{
                  alert('Error');
                }
            }
        };
    </script>
@endsection