@extends('layouts.main')
@section('styles')
    <style>
        .attachment {
            width: 80%;
            max-width: 300px;
        }

        .attachment .btn {
            position: absolute;
            top: 20%;
            right: 5%;
            display: none;
        }

        .modal-dialog {
            max-width: 70%;
        }

        .caption_input {
            display: none;
        }

        .caption_text {
            border-radius: 10px;
        }

        .caption_text:hover {
            box-shadow: 5px 10px 8px #888888;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-10">
                <div>
                    <a href="{{route('index')}}" class="btn btn-warning">Back to products</a>
                    <a href="{{route('edit_product',['id'=>$product->id])}}" class="btn btn-info">Edit product</a>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h2>{{$product->name}}</h2>
                        <span>{{$product->product_code}}</span>
                    </div>

                    <div class="w3-container">
                        <div class="col-sm-5">
                            <table class="w3-table w3-bordered">
                                <tr>
                                    <td><b>Size:</b></td>
                                    <td>{{$product->size?$product->size:''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Brand:</b></td>
                                    <td>{{$product->brand?$product->brand:''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Case count:</b></td>
                                    <td>{{$product->case_count?$product->case_count:''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Created at:</b></td>
                                    <td>{{$product->created_at?$product->created_at->diffForHumans():''}}</td>
                                </tr>
                                <tr>
                                    <td><b>Last modified:</b></td>
                                    <td>{{$product->updated_at?$product->updated_at->diffForHumans():''}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-6 w3-margin-top w3-center">
                            {!! \DNS1D::getBarcodeSVG($product->barcode, "EAN13",3,103,"black", true) !!}<br>
                            {{$product->barcode}}
                        </div>
                        <div class="col-sm-12 w3-center w3-margin-top">
                            <p>{{$product->description}}</p>
                        </div>
                        <div class="col-sm-12 w3-center w3-margin-top" id="categories_list">
                            <h3>Categories</h3>
                            <hr>
                            @if(!empty($arrCategories))
                            @foreach($arrCategories as $category)
                                <div class="w3-display-container w3-green col-sm-3 w3-margin-right w3-margin-bottom">
                                    <div class="w3-display-middle category_text">{{$category}}</div>
                                </div>
                            @endforeach
                                @else
                                <p>No linked categories</p>
                            @endif
                        </div>
                        <div class="col-sm-12 w3-margin-bottom">

                            <hr>
                            @if(count($product->attachments)>0)
                                @foreach($product->attachments as $attachment)
                                    <div class="col-sm-3">
                                        <div class="attachment" id="attachment_block_{{$attachment->id}}">
                                            @if($attachment->import=='N')
                                                <img
                                                    src="{{!empty($arrThumbnails[$attachment->id])?$arrThumbnails[$attachment->id]:asset('storage/upload/images/includes/video.png')}}"
                                                    style="width:100%" data-toggle="modal"
                                                    data-target="#modal_{{$attachment->id}}">
                                            @else
                                                <img
                                                    src="{{$attachment->type=='image'?$attachment->path:asset('storage/upload/images/includes/video.png')}}"
                                                    style="width:100%" data-toggle="modal"
                                                    data-target="#modal_{{$attachment->id}}">
                                            @endif
                                            <button class="btn delete" id="{{$attachment->id}}" data-toggle="modal"
                                                    data-target="#deleteModal_{{$attachment->id}}">X
                                            </button>
                                        </div>
                                        @include('partials.modal_attachment_show',['attachment'=>$attachment])
                                        @include('partials.modal_attachment_delete',['attachment'=>$attachment->id,
                                        'slug'=>'attachment',
                                        'action'=>'delete',
                                        ])
                                        @endforeach
                                        @else
                                            No attachments found
                                        @endif
                                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endsection

        @section('scripts')
            <script>
                var token = '{{\Illuminate\Support\Facades\Session::token()}}';
                $('.attachment').on('mouseover', function () {
                    $(this).find('.btn').show();
                })

                $('.attachment').on('mouseout', function () {
                    $(this).find('.btn').hide();
                });


                $("p[id^='caption_text_']").click(function () {
                    var attachment_id = $(this).attr('id').replace('caption_text_', '');
                    var strCaption = $(this).text();
                    $(this).hide();
                    if (strCaption.trim() === 'Still no caption') {
                        $('#caption_input_' + attachment_id).attr('placeholder', strCaption.trim());
                    } else {
                        $('#caption_input_' + attachment_id).val(strCaption.trim());
                    }

                    $('#caption_input_box_' + attachment_id).show();
                    $('#caption_input_' + attachment_id).focus();
                });


                $('input[id^="caption_input_"]').keydown(function (e) {

                    if (e.keyCode == 13) {
                        var id = $(this).attr('id').replace('caption_input_', '');
                        var newCaption = $(this).val();

                        if (newCaption.trim() !== '') {
                            var url = '{{ route('update_caption_ajax') }}';
                            $.ajax({
                                method: 'POST',
                                url: url,
                                dataType: "json",
                                data: {
                                    attachment_id: id,
                                    new_caption: newCaption,
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
                                            text: 'Caption successfully updated!'
                                        }).show();

                                        //-- Hide visually attachment

                                        $('#caption_input_box_' + id).hide();
                                        $('#caption_text_' + id).text(newCaption.trim()).show();
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
                    }
                });


                //-- Delete attachment
                $("button[id^='delete_attachment_']").click(function () {
                    var attachment_id = $(this).attr('id').replace('delete_attachment_', '');

                    $('#deleteModal_' + attachment_id).modal('toggle');
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
                });

            </script>
@endsection