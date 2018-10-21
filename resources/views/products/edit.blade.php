@extends('layouts.main')
@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.1.0/min/dropzone.min.css" rel="stylesheet">
    <style>
        .attachment {
            width: 50%;
            max-width: 150px;
        }

        .attachment img {
            width: 100%;
            height: auto;
        }

        .attachment .btn {
            position: absolute;
            top: 50%;
            left: 50%;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="inline_form">
                    {!! Form::model($product,['method'=>'PATCH','id'=>"product_form",'action'=>['ProductController@update',$product->id], 'files'=>true])!!}
                    <input type="hidden" name="categories_form" id="categories_form"
                           value="{{old('categories_form')?old('categories_form'):$strCategories}}">
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
                    {!! Form::submit('Update product',['class'=>'btn btn-warning']) !!}
                    {!! Form::close() !!}
                    {{ Form::open(['method' =>'DELETE' , 'action' => ['ProductController@destroy',$product->id]])}}

                    {!! Form::submit('Delete product',['class'=>'btn btn-danger']) !!}

                    {!! Form::close() !!}
                    <a href="{{route('index')}}" class="btn btn-info">Back to products</a>
                    <a href="{{route('products.show',['id'=>$product->id])}}" class="btn btn-success">Product view</a>
                    <div>

                    </div>
                </div>
                @include('partials.categories')

                <div class="w3-margin-top w3-margin-bottom">

                    @if(count($product->attachments)>0)
                        @foreach($product->attachments as $attachment)
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
                    <div class="container">
                        {!! Form::open(['method'=>'POST','action'=>['AttachmentController@store','userId'=>Auth::id()],'id'=>'uploadForm', 'class'=>'dropzone'])!!}

                        {{ Form::hidden('user_id', Auth::id() ) }}
                        {{ Form::hidden('product_id', $product->id ) }}
                        {!! Form::close() !!}
                    </div>
                    @include('includes.formErrors')
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
                var token = '{{\Illuminate\Support\Facades\Session::token()}}';
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

                //-- Check whether necessary to display any linked ctegory
                var selectedCategories = {!! json_encode($strCategories) !!};
                if (selectedCategories != '') {
                    var arrCategories = selectedCategories.trim().split(";");
                    showInitiallySelectedCategories(arrCategories);
                }

                function showInitiallySelectedCategories(arrCategories) {

                    for (var i = 0; i < arrCategories.length; i++) {
                        var strCategory = "<div class=\"w3-display-topright\">\n" +
                            "                    <button class=\"btn delete\" data-category='" + arrCategories[i] + "' onclick=\"deleteCategory(this)\" data-toggle=\"modal\"\n" +
                            "                                            data-target=\"#deleteModal_category\">X\n" +
                            "                    </button>\n" +
                            "                    </div>\n" +
                            "                    <div class=\"w3-display-middle category_text\">" + arrCategories[i] + "</div>";

                        $('<div class="w3-display-container w3-green col-sm-3 w3-margin-right w3-margin-bottom">').html(strCategory + "</div>").appendTo('#categories_list');
                    }
                }

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
            </script>
@endsection