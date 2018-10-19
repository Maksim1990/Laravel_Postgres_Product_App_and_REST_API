@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
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
                    {!! Form::text('size', null, ['class'=>'form-control']) !!}
                </div>

                <div class="group-form">
                    {!! Form::label('case_count','Case count:') !!}
                    {!! Form::text('case_count', null, ['class'=>'form-control']) !!}
                </div>


                <div class="group-form">
                    {!! Form::label('description','Description:') !!}

                    {!! Form::textarea('description', null, ['class'=>'form-control','id'=>'code']) !!}
                    <br>
                </div>
                {!! Form::submit('Create product',['class'=>'btn btn-warning']) !!}
                {!! Form::close() !!}
                @include('includes.formErrors')
            </div>

        </div>
    </div>
@endsection