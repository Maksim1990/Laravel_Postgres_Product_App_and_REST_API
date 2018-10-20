@extends('layouts.main')
@section('styles')
    <style>
        .attachment {
            position: relative;
            width: 80%;
            max-width: 300px;
            display: inline-block;
        }

        .attachment img {
            width: 100%;
            height: auto;
        }

        .attachment .btn {
            position: absolute;
            top: 20%;
            right: 5%;
            display: none;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            background-color: #ab1f25;
            color: white;
            font-size: 12px;
            padding: 5px 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
        }

        .attachment .btn:hover {
            background-color: black;
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
                        <div class="col-sm-12">
                            <p>{{$product->description}}</p>
                        </div>
                        <div class="col-sm-12">

                            <hr>
                            @if(count($product->attachments)>0)
                                @foreach($product->attachments as $attachment)
                                    <div class="col-sm-3">
                                        @if(in_array($attachment->extension,\App\Config\Config::IMAGES_EXTENSIONS))
                                            <div class="attachment" id="attachment_block_{{$attachment->id}}">
                                                <img src="{{asset('/storage/'.$attachment->path)}}" style="width:100%"
                                                     data-toggle="modal" data-target="#modal_{{$attachment->id}}">
                                                <button class="btn delete" id="{{$attachment->id}}" data-toggle="modal" data-target="#deleteModal_{{$attachment->id}}">X</button>
                                            </div>
                                    @endif
                                   @include('partials.modal_attachment_show',['attachment'=>$attachment])
                                   @include('partials.modal_attachment_delete',['attachment'=>$attachment])
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
        $('.attachment').on('mouseover',function () {
            $(this).find('.btn').show();
        })

        $('.attachment').on('mouseout',function () {
            $(this).find('.btn').hide();
        });


        //-- Delete attachment
        $("button[id^='delete_attachment_']").click(function () {
            var attachment_id = $(this).attr('id').replace('delete_attachment_','');

            $('#deleteModal_'+attachment_id).modal('toggle');
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