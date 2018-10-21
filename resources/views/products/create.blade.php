@extends('layouts.main')
@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.css" rel="stylesheet">
    <style>
        #categories_list>.w3-display-container{
            height:50px;border-radius: 10px;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="inline_form w3-margin-bottom">
                    {!! Form::open(['method'=>'POST', 'id'=>"product_form",'action'=>['ProductController@store','id'=>Auth::id()], 'files'=>true])!!}
                    <input type="hidden" name="categories_form" id="categories_form" value="{{old('categories_form')}}">
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
                @include('partials.categories')
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
    <input type="hidden" id="category_delete" value="">
    @include('partials.modal_attachment_delete',['attachment'=>'category',
    'action'=>'unlink',
    'slug'=>'category'])
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.js"></script>
    @include('partials.categories_js')
    <script>
        Dropzone.options.uploadForm = {
            dataType: "json",
            success: function (file, response) {
                if (response == "success") {
                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Attachments updated!'
                    }).show();
                } else {
                    new Noty({
                        type: 'error',
                        layout: 'bottomLeft',
                        text: 'There is error happened while uploading file!'
                    }).show();
                }
            }
        };

        $('#product_form').on('submit', function(e) {
            e.preventDefault();

            var arrCetegories=[];
            $('.category_text').each(function( index ) {
              arrCetegories.push($( this ).text());
            });
            var strCategories=arrCetegories.join(";");
            $('#categories_form').val(strCategories);

            this.submit();
        });

    </script>
@endsection