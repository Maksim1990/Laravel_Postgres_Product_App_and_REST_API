@extends('layouts.main')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.css" rel="stylesheet">


@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="inline_form w3-margin-bottom">
                {!! Form::open(['method'=>'POST', 'id'=>"email_form",'action'=>['ProductController@store','id'=>Auth::id()], 'files'=>true])!!}
                <div class="group-form">
                    {!! Form::label('name','Name:') !!}
                    {!! Form::text('name', null, ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('brand','Brand:') !!}
                    {!! Form::text('brand', null, ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('size','Size:') !!}
                    {!! Form::number('size', null, ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('case_count','Case count:') !!}
                    {!! Form::number('case_count', null, ['class'=>'form-control']) !!}
                </div>


                <div class="group-form">
                    {!! Form::label('description','Description:') !!}

                    {!! Form::textarea('description', null, ['class'=>'form-control','id'=>'code']) !!}
                    <br>
                </div>
                    <a href="{{route('index')}}" class="btn btn-success">Cancel</a>
                {!! Form::submit('Create product',['class'=>'btn btn-warning']) !!}
                {!! Form::close() !!}

            </div>
            <div class="w3-margin-bottom">
                <div class="col-sm-12">
                    <h3>Categories</h3><hr>
                    <div class="ui-widget">
                        <div class="col-sm-4">
                        <input id="category_item" type="text" class="form-control">
                        </div>
                        <div class="col-sm-4">
                        <input id="tags" type="text" class="form-control" placeholder="Start type category">
                        </div>
                        <div class="col-sm-2">
                        <a href="#" id="add_category" class="btn btn-success">Add</a>
                        </div>
                    </div>
                </div>
            </div>
                <div class="col-sm-12">
                <div class="container w3-margin-top">
                    {!! Form::open(['method'=>'POST','action'=>['AttachmentController@store','userId'=>Auth::id()],'id'=>'uploadForm', 'class'=>'dropzone'])!!}

                    {{ Form::hidden('user_id', Auth::id() ) }}
                    {{ Form::hidden('product_id', 0 ) }}
                    {!! Form::close() !!}
                </div>
                @include('includes.formErrors')
                </div>
            </div>

        </div>
    </div>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.js"></script>
    <script>
        var token = '{{\Illuminate\Support\Facades\Session::token()}}';
            function getCategoriesList() {
                var availableTags='';
                var url = '{{ route('get_categories_ajax') }}';
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: "json",
                    async: false,
                    data: {
                        _token: token
                    },
                    success: function (data) {
                        availableTags=data;
                    }
                });
                return availableTags
            }
        var availableTags=getCategoriesList();
            console.log(availableTags[0]);
            $( "#tags" ).autocomplete({
                source: availableTags[0]
            });
    </script>
    <script>
        Dropzone.options.uploadForm = {
            dataType: "json",
            success: function(file, response){
                if (response == "success") {
                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Attachments updated!'
                    }).show();
                }else{
                    new Noty({
                        type: 'error',
                        layout: 'bottomLeft',
                        text: 'There is error happened while uploading file!'
                    }).show();
                }
            }
        };
    </script>
@endsection