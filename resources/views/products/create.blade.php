@extends('layouts.main')
@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.css" rel="stylesheet">
    <style>

    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="inline_form w3-margin-bottom">
                    {!! Form::open(['method'=>'POST', 'id'=>"product_form",'action'=>['ProductController@store','id'=>Auth::id()], 'files'=>true])!!}
                    <input type="hidden" name="categories" id="categories_form" value="{{old('categories_form')}}">
                    <div class="group-form">
                        {!! Form::label('name','Name:') !!}
                        <span class="w3-text-red tooltip_cust"><i class="fas fa-info-circle"></i><span class="tooltiptext">{{\App\Config\Config::ALLOWED_CHARACTERS_INFO}}</span></span>
                        {!! Form::text('name', null, ['class'=>'form-control','onkeypress'=>"return isNumberKey(event);"]) !!}
                    </div>

                    <div class="group-form">
                        {!! Form::label('brand','Brand:') !!}
                        <span class="w3-text-red tooltip_cust"><i class="fas fa-info-circle"></i><span class="tooltiptext">{{\App\Config\Config::ALLOWED_CHARACTERS_INFO}}</span></span>
                        {!! Form::text('brand', null, ['class'=>'form-control','onkeypress'=>"return isNumberKey(event);"]) !!}
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
                        <span class="w3-text-red tooltip_cust"><i class="fas fa-info-circle"></i><span class="tooltiptext">{{\App\Config\Config::ALLOWED_CHARACTERS_INFO}}</span></span>
                        {!! Form::textarea('description', null, ['class'=>'form-control','id'=>'code','onkeypress'=>"return isNumberKey(event);"]) !!}
                        <br>
                    </div>
                    <a href="{{route('index')}}" class="btn btn-success">Cancel</a>
                    {!! Form::submit('Create product',['class'=>'btn btn-warning']) !!}
                    {!! Form::close() !!}

                </div>
                @include('partials.categories')
                <div class="w3-margin-top w3-margin-bottom">

                    @if(count($attachments)>0)
                        @foreach($attachments as $attachment)
                            <div class="attachment" id="attachment_block_{{$attachment->id}}">
                                @if($attachment->import=='N')
                                    <img
                                        src="{{!empty($arrThumbnails[$attachment->id])?$arrThumbnails[$attachment->id]:asset('storage/upload/images/includes/video.png')}}"
                                        style="width:100%">
                                @else
                                    <img
                                        src="{{$attachment->type=='image'?$attachment->path:asset('storage/upload/images/includes/video.png')}}"
                                        style="width:100%">
                                @endif
                                <button class="btn delete" id="{{$attachment->id}}" data-toggle="modal"
                                        data-target="#deleteModal_{{$attachment->id}}">X
                                </button>
                            </div>
                            @include('partials.modal_attachment_delete',['attachment'=>$attachment->id,
                            'action'=>'delete',
                            'slug'=>'attachment'])
                        @endforeach
                    @else
                        <div class="w3-center">
                            No attachments found
                        </div>
                    @endif
                    <hr>
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
    <input type="hidden" id="category_delete" value="">
    <input type="hidden" id="product_id" value="0">
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
                        text: response
                    }).show();
                }
            }
        };
        //-- Delete attachment
        $("button[id^='delete_attachment_']").click(function () {
            var id = $(this).attr('id');
            var attachment_id = id.replace('delete_attachment_', '');
            if (id !== 'delete_attachment_category') {
                var url = '{{ route('delete_attachment_ajax') }}';
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: "json",
                    data: {
                        attachment_id: attachment_id,
                        _token: token
                    }, beforeSend: function () {
                        //-- Show loading image while execution of ajax request
                        $("div#divLoading").addClass('show');
                    },
                    success: function (data) {
                        if (data['result'] === "success") {
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Attachment was successfully deleted!'
                            }).show();

                            //-- Hide visually attachment
                            $('#attachment_block_' + attachment_id).hide();
                        } else {
                            new Noty({
                                type: 'error',
                                layout: 'bottomLeft',
                                text: data['error']
                            }).show();
                        }

                        //-- Hide loading image
                        $("div#divLoading").removeClass('show');
                    }
                });
            }

        });
        function isNumberKey(evt)
        {
            var charCode = (evt.which) ? evt.which : event.keyCode;

            var arrAllowedChars=[
               47,60,61,62
            ];
            if(arrAllowedChars.includes(charCode)){
                return false;
            }
            return true;
        }
    </script>
@endsection